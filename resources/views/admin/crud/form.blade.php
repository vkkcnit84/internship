@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ $record ? trans('global.edit') : trans('global.create') }} {{ trans('cruds.' . $cfg['key'] . '.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ $record ? route($cfg['route'] . '.update', [$record->id]) : route($cfg['route'] . '.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($record)
                @method('PUT')
            @endif
            @foreach($cfg['fields'] as $field)
                @php
                    $name  = $field['name'];
                    $label = trans('cruds.' . $cfg['key'] . '.fields.' . $name);
                    $value = old($name, $record ? $record->{$name} : ($field['type'] === 'checkbox' ? true : ''));
                    $required = $field['required'] ?? false;
                @endphp
                <div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
                    @if($field['type'] === 'checkbox')
                        <div class="form-check">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input class="form-check-input" type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" {{ $value ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
                        </div>
                    @else
                        <label for="{{ $name }}">{{ $label }}{{ $required ? '*' : '' }}</label>
                        @if($field['type'] === 'select')
                            <select id="{{ $name }}" name="{{ $name }}" class="form-control select2" {{ $required ? 'required' : '' }}>
                                @foreach($options[$name] as $id => $optionLabel)
                                    <option value="{{ $id }}" {{ (string) $value === (string) $id ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'image')
                            @if($record && $record->{$name})
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $record->{$name}) }}" alt="" style="max-height: 80px; max-width: 200px;">
                                </div>
                            @endif
                            <input type="file" id="{{ $name }}" name="{{ $name }}" class="form-control-file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small class="form-text text-muted">JPG, PNG or WEBP, up to 2 MB.</small>
                        @elseif($field['type'] === 'textarea')
                            <textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="4">{{ $value }}</textarea>
                        @elseif($field['type'] === 'number')
                            <input type="number" step="0.01" id="{{ $name }}" name="{{ $name }}" class="form-control" value="{{ $value }}" {{ $required ? 'required' : '' }}>
                        @else
                            <input type="text" id="{{ $name }}" name="{{ $name }}" class="form-control" value="{{ $value }}" {{ $required ? 'required' : '' }}>
                        @endif
                    @endif
                    @if($errors->has($name))
                        <em class="invalid-feedback" style="display: block;">
                            {{ $errors->first($name) }}
                        </em>
                    @endif
                </div>
            @endforeach
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>
@endsection
