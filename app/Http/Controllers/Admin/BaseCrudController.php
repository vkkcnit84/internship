<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic admin CRUD for simple "name + optional parent" entities
 * (University, College, Stream, Subject, Internship).
 *
 * A subclass only describes the entity in config(); the views live in
 * resources/views/admin/crud and the permissions follow the usual
 * {key}_access / _create / _edit / _show / _delete convention.
 *
 * config() keys:
 *   key          permission prefix and lang key (e.g. 'university')
 *   model        Eloquent class
 *   route        route name prefix (e.g. 'admin.universities')
 *   with         relations to eager load on the index
 *   children     relations that block deletion while they have rows
 *   unique_scope column that scopes the name's uniqueness (e.g. 'university_id')
 *   fields       list of ['name', 'type' => text|textarea|number|select|checkbox|image,
 *                'required', 'rules', 'unique', 'model' (selects), 'relation' (selects),
 *                'with' (selects), 'dir' (images: folder on the public disk),
 *                'index' => false to hide from the index]
 */
abstract class BaseCrudController extends Controller
{
    abstract protected function config(): array;

    public function index()
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'access');

        $rows = $cfg['model']::with($cfg['with'] ?? [])->get();

        return view('admin.crud.index', compact('cfg', 'rows'));
    }

    public function create()
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'create');

        return view('admin.crud.form', [
            'cfg'     => $cfg,
            'record'  => null,
            'options' => $this->selectOptions($cfg),
        ]);
    }

    public function store(Request $request)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'create');

        $cfg['model']::create($this->validated($request, $cfg));

        return redirect()->route($cfg['route'] . '.index')->with('message', trans('global.saved'));
    }

    public function show($id)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'show');

        $record = $cfg['model']::with($cfg['with'] ?? [])->findOrFail($id);

        return view('admin.crud.show', compact('cfg', 'record'));
    }

    public function edit($id)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'edit');

        $record = $cfg['model']::findOrFail($id);

        return view('admin.crud.form', [
            'cfg'     => $cfg,
            'record'  => $record,
            'options' => $this->selectOptions($cfg),
        ]);
    }

    public function update(Request $request, $id)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'edit');

        $record = $cfg['model']::findOrFail($id);
        $record->update($this->validated($request, $cfg, $record));

        return redirect()->route($cfg['route'] . '.index')->with('message', trans('global.saved'));
    }

    public function destroy($id)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'delete');

        $record = $cfg['model']::findOrFail($id);

        if ($this->hasChildren($record, $cfg)) {
            return back()->withErrors(['delete' => trans('global.cannotDeleteInUse', ['name' => $record->name])]);
        }

        $record->delete();

        return back()->with('message', trans('global.deleted'));
    }

    public function massDestroy(Request $request)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        $records = $cfg['model']::whereIn('id', $request->input('ids'))->get();
        $blocked = $records->filter(function ($record) use ($cfg) {
            return $this->hasChildren($record, $cfg);
        });

        // All-or-nothing: nothing is deleted if any selected row is still in use.
        if ($blocked->isNotEmpty()) {
            return response()->json([
                'message' => trans('global.cannotDeleteInUse', ['name' => $blocked->pluck('name')->implode(', ')]),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $records->each->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    protected function authorizeAction(array $cfg, string $action): void
    {
        abort_if(Gate::denies($cfg['key'] . '_' . $action), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    protected function hasChildren(Model $record, array $cfg): bool
    {
        foreach ($cfg['children'] ?? [] as $relation) {
            if ($record->{$relation}()->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function validated(Request $request, array $cfg, ?Model $record = null): array
    {
        $rules = [];
        $table = (new $cfg['model'])->getTable();

        foreach ($cfg['fields'] as $field) {
            $name     = $field['name'];
            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];

            switch ($field['type']) {
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'checkbox':
                    $fieldRules   = ['boolean'];
                    break;
                case 'image':
                    // SVG is deliberately not allowed (it can carry scripts).
                    $fieldRules[] = 'image';
                    $fieldRules[] = 'mimes:jpg,jpeg,png,webp';
                    $fieldRules[] = 'max:2048';
                    break;
                case 'select':
                    $fieldRules[] = 'integer';
                    $fieldRules[] = Rule::exists((new $field['model'])->getTable(), 'id')->whereNull('deleted_at');
                    break;
                default:
                    $fieldRules[] = 'string';
            }

            if ($field['type'] === 'text') {
                $fieldRules[] = 'max:255';
            }

            if ($field['unique'] ?? false) {
                $unique = Rule::unique($table, $name)->whereNull('deleted_at');

                if (!empty($cfg['unique_scope'])) {
                    $unique->where($cfg['unique_scope'], $request->input($cfg['unique_scope']));
                }

                $fieldRules[] = $unique->ignore($record ? $record->id : null);
            }

            $rules[$name] = array_merge($fieldRules, $field['rules'] ?? []);
        }

        $request->validate($rules);

        $data = [];
        foreach ($cfg['fields'] as $field) {
            if ($field['type'] === 'image') {
                if ($request->hasFile($field['name'])) {
                    if ($record && $record->{$field['name']}) {
                        Storage::disk('public')->delete($record->{$field['name']});
                    }
                    $data[$field['name']] = $request->file($field['name'])->store($field['dir'] ?? $cfg['key'], 'public');
                }

                continue;
            }

            $data[$field['name']] = $field['type'] === 'checkbox'
                ? $request->boolean($field['name'])
                : $request->input($field['name']);
        }

        return $data;
    }

    protected function selectOptions(array $cfg): array
    {
        $options = [];

        foreach ($cfg['fields'] as $field) {
            if ($field['type'] !== 'select') {
                continue;
            }

            $options[$field['name']] = $field['model']::with($field['with'] ?? [])
                ->orderBy('name')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [$row->id => $row->full_name ?? $row->name];
                })
                ->prepend(trans('global.pleaseSelect'), '');
        }

        return $options;
    }
}
