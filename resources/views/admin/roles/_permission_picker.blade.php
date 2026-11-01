@php
    // Group "course_create", "course_edit", ... under "Course"; unknown endings stay under "Other".
    $pickerActions = ['mark_paid', 'access', 'create', 'edit', 'show', 'delete', 'approve', 'reject'];
    $pickerGroups  = [];

    foreach ($permissions as $permId => $permTitle) {
        $group  = $permTitle;
        $action = 'other';

        foreach ($pickerActions as $candidate) {
            $suffix = '_' . $candidate;
            if (substr($permTitle, -strlen($suffix)) === $suffix && strlen($permTitle) > strlen($suffix)) {
                $group  = substr($permTitle, 0, -strlen($suffix));
                $action = $candidate;
                break;
            }
        }

        $pickerGroups[$group][] = ['id' => $permId, 'action' => $action, 'title' => $permTitle];
    }

    ksort($pickerGroups);

    $actionOrder = array_flip(['access', 'create', 'edit', 'show', 'delete', 'approve', 'reject', 'mark_paid', 'other']);
    foreach ($pickerGroups as $group => $items) {
        usort($items, function ($a, $b) use ($actionOrder) {
            return $actionOrder[$a['action']] <=> $actionOrder[$b['action']];
        });
        $pickerGroups[$group] = $items;
    }
@endphp

<div id="permission-picker" class="card mb-2" style="background: #f8fafc;">
    <div class="card-body py-2">
        <div class="mb-2">
            <strong>Quick select</strong>
            <button type="button" class="btn btn-sm btn-secondary" data-pick="all">Everything</button>
            <button type="button" class="btn btn-sm btn-secondary" data-pick="none">Nothing</button>
            <button type="button" class="btn btn-sm btn-secondary" data-pick="read">Read-only (access + show)</button>
        </div>
        <table class="table table-sm mb-0" style="background: #fff;">
            <tbody>
                @foreach($pickerGroups as $group => $items)
                    <tr data-group>
                        <td style="width: 24%;">
                            <label class="mb-0 font-weight-bold" style="display: inline-block;">
                                <input type="checkbox" class="group-toggle"> {{ ucfirst(str_replace('_', ' ', $group)) }}
                            </label>
                        </td>
                        <td>
                            @foreach($items as $item)
                                <label class="mr-3 mb-0" style="display: inline-block; white-space: nowrap;" title="{{ $item['title'] }}">
                                    <input type="checkbox" class="perm-box" data-id="{{ $item['id'] }}" data-action="{{ $item['action'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $item['action'] === 'other' ? $item['title'] : $item['action'])) }}
                                </label>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
