@extends('layouts.admin')
@section('content')
@can('internship_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.internships.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.internship.title_singular') }}
            </a>
        </div>
    </div>
@endcan

@if(!auth()->user()->isCollegeAdmin())
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filter Internships
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="university_id" class="font-weight-bold">University</label>
                    <select name="university_id" id="university_id" class="form-control select2" onchange="filterCollegesByUniversityInternship()">
                        <option value="">-- All Universities --</option>
                        @foreach($universities as $university)
                            <option value="{{ $university->id }}" {{ request('university_id') == $university->id ? 'selected' : '' }}>
                                {{ $university->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="college_id" class="font-weight-bold">College</label>
                    <select name="college_id" id="college_id" class="form-control select2">
                        <option value="">-- All Colleges --</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" data-university="{{ $college->university_id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->name }} ({{ $college->university->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.internships.index') }}" class="btn btn-secondary btn-block ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        {{ trans('cruds.internship.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-internship">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>{{ trans('cruds.internship.fields.id') }}</th>
                        <th>{{ trans('cruds.internship.fields.name') }}</th>
                        @if(!auth()->user()->isCollegeAdmin())
                            <th>{{ trans('cruds.college.title_singular') }}</th>
                        @endif
                        <th>{{ trans('cruds.internship.fields.price') }}</th>
                        <th>{{ trans('cruds.internship.fields.is_active') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr data-entry-id="{{ $row->id }}">
                            <td></td>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->name }}</td>
                            @if(!auth()->user()->isCollegeAdmin())
                                <td>
                                    @if($row->college)
                                        {{ $row->college->name }} ({{ $row->college->university->name ?? 'N/A' }})
                                    @else
                                        <span class="badge badge-secondary">Global</span>
                                    @endif
                                </td>
                            @endif
                            <td>₹{{ number_format($row->price, 2) }}</td>
                            <td>
                                <span class="badge {{ $row->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $row->is_active ? trans('global.active') : trans('global.inactive') }}
                                </span>
                            </td>
                            <td>
                                @can('internship_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.internships.show', $row->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('internship_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.internships.edit', $row->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('internship_delete')
                                    <form action="{{ route('admin.internships.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function filterCollegesByUniversityInternship() {
    var universityId = document.getElementById('university_id').value;
    var collegeSelect = document.getElementById('college_id');
    var collegeOptions = collegeSelect.querySelectorAll('option[data-university]');

    collegeOptions.forEach(function(option) {
        var optionUniversityId = option.getAttribute('data-university');
        if (universityId === '' || optionUniversityId === universityId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    if (universityId !== '') {
        collegeSelect.value = '';
    }

    if ($.fn.select2) {
        $(collegeSelect).trigger('change');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    filterCollegesByUniversityInternship();
});
</script>
@endsection
