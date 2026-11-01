<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Institution;
use App\College;
use App\Role;
use App\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $actor = auth()->user();
        $users = User::with('roles')->get()->filter(function ($user) use ($actor) {
            return $actor->canManage($user);
        })->values();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::whereIn('id', auth()->user()->assignableRoleIds())->pluck('title', 'id');

        $institutions = Institution::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $colleges = College::with('university')->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'institutions', 'colleges'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeManage($user);

        $roles = Role::whereIn('id', auth()->user()->assignableRoleIds())->pluck('title', 'id');

        $institutions = Institution::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $colleges = College::with('university')->orderBy('name')->get();

        $user->load('roles', 'institution');

        return view('admin.users.edit', compact('roles', 'institutions', 'colleges', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeManage($user);

        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeManage($user);

        $user->load('roles', 'institution');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeManage($user);

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $users = User::with('roles')->whereIn('id', request('ids'))->get();
        $users->each(function ($user) {
            $this->authorizeManage($user);
        });

        User::whereIn('id', $users->pluck('id'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function authorizeManage(User $user): void
    {
        abort_unless(auth()->user()->canManage($user), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
