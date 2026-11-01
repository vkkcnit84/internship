@php
    $isAdminRole = isset($role) && (int) $role->id === 1;
    $chosen      = array_map('intval', old('assignable_roles', isset($role) ? $role->assignableRoles->pluck('id')->all() : []));
@endphp
<div class="form-group {{ $errors->has('assignable_roles.*') ? 'has-error' : '' }}">
    <label>Roles this role can assign to users</label>
    @if($isAdminRole)
        <p class="helper-block mb-0">Admin can always see and assign every role.</p>
    @else
        <div>
            @foreach($allRoles as $roleId => $roleTitle)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="assignable_{{ $roleId }}" name="assignable_roles[]" value="{{ $roleId }}" {{ in_array((int) $roleId, $chosen, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="assignable_{{ $roleId }}">{{ $roleTitle }}</label>
                </div>
            @endforeach
        </div>
        <p class="helper-block">
            Only these roles appear in the Role list when a user with this role creates or edits users, and they are the only ones the server accepts.
            They can also only list, edit and delete users whose roles are all in this list. Leave all unticked to allow none.
        </p>
    @endif
    @if($errors->has('assignable_roles.*'))
        <em class="invalid-feedback" style="display: block;">{{ $errors->first('assignable_roles.*') }}</em>
    @endif
</div>
