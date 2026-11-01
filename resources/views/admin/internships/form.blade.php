@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ $record ? trans('global.edit') : trans('global.create') }} {{ trans('cruds.internship.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ $record ? route('admin.internships.update', [$record->id]) : route('admin.internships.store') }}" method="POST">
            @csrf
            @if($record)
                @method('PUT')
            @endif

            @if(!auth()->user()->isCollegeAdmin())
            <div class="form-group {{ $errors->has('college_id') ? 'has-error' : '' }}">
                <label for="college_id">College</label>
                <select id="college_id" name="college_id" class="form-control select2">
                    <option value="">-- Global (All Colleges) --</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" {{ (string) old('college_id', $record ? $record->college_id : '') === (string) $college->id ? 'selected' : '' }}>
                            {{ $college->name }} ({{ $college->university->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @if($errors->has('college_id'))
                    <em class="invalid-feedback" style="display: block;">
                        {{ $errors->first('college_id') }}
                    </em>
                @endif
                <small class="form-text text-muted">
                    Leave blank to create a global internship available to all colleges
                </small>
            </div>
            @endif

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $record ? $record->name : '') }}" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback" style="display: block;">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                <label for="price">Price (₹) *</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ old('price', $record ? $record->price : '') }}" required>
                @if($errors->has('price'))
                    <em class="invalid-feedback" style="display: block;">
                        {{ $errors->first('price') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('tax_percent') ? 'has-error' : '' }}">
                <label for="tax_percent">Tax Percent (%) *</label>
                <input type="number" step="0.01" id="tax_percent" name="tax_percent" class="form-control" value="{{ old('tax_percent', $record ? $record->tax_percent : '0') }}" required>
                @if($errors->has('tax_percent'))
                    <em class="invalid-feedback" style="display: block;">
                        {{ $errors->first('tax_percent') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('duration_weeks') ? 'has-error' : '' }}">
                <label for="duration_weeks">Duration (Weeks)</label>
                <input type="number" step="1" id="duration_weeks" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', $record ? $record->duration_weeks : '') }}">
                @if($errors->has('duration_weeks'))
                    <em class="invalid-feedback" style="display: block;">
                        {{ $errors->first('duration_weeks') }}
                    </em>
                @endif
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $record ? $record->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback" style="display: block;">
                        {{ $errors->first('description') }}
                    </em>
                @endif
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $record ? $record->is_active : false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
                <a href="{{ route('admin.internships.index') }}" class="btn btn-secondary">{{ trans('global.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
