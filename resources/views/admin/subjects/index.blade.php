@extends('layouts.admin')
@section('content')
@can('subject_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.subjects.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.subject.title_singular') }}
            </a>
        </div>
    </div>
@endcan

@if(!auth()->user()->isCollegeAdmin())
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filter Subjects
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="college_id" class="font-weight-bold">College</label>
                    <select name="college_id" id="college_id" class="form-control select2" onchange="filterStreamsByCollege()">
                        <option value="">-- All Colleges --</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->name }} ({{ $college->university->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="stream_id" class="font-weight-bold">Stream</label>
                    <select name="stream_id" id="stream_id" class="form-control select2">
                        <option value="">-- All Streams --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" data-college="{{ $stream->college_id }}" {{ request('stream_id') == $stream->id ? 'selected' : '' }}>
                                {{ $stream->name }} ({{ $stream->college->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary btn-block ml-2">
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
        {{ trans('cruds.subject.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-subject">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>{{ trans('cruds.subject.fields.id') }}</th>
                        <th>{{ trans('cruds.subject.fields.name') }}</th>
                        @if(!auth()->user()->isCollegeAdmin())
                            <th>Stream</th>
                            <th>College</th>
                        @endif
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
                                <td>{{ $row->stream->name ?? '' }}</td>
                                <td>{{ $row->stream->college->name ?? '' }} @if($row->stream->college)({{ $row->stream->college->university->name ?? 'N/A' }})@endif</td>
                            @endif
                            <td>
                                @can('subject_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.subjects.show', $row->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('subject_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.subjects.edit', $row->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('subject_delete')
                                    <form action="{{ route('admin.subjects.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
function filterStreamsByCollege() {
    var collegeId = document.getElementById('college_id').value;
    var streamSelect = document.getElementById('stream_id');

    // Get all options and update them
    $(streamSelect).find('option[data-college]').each(function() {
        var optionCollegeId = $(this).attr('data-college');
        if (collegeId === '' || optionCollegeId === collegeId) {
            $(this).prop('disabled', false);
        } else {
            $(this).prop('disabled', true);
        }
    });

    // Clear stream selection when college changes
    if (collegeId !== '') {
        streamSelect.value = '';
    }

    // Reinitialize select2 to apply changes
    if ($.fn.select2) {
        try {
            $(streamSelect).select2('destroy');
        } catch(e) {}
        $(streamSelect).select2();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    filterStreamsByCollege();
});
</script>
@endsection
