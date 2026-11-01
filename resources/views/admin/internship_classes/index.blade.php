@extends('layouts.admin')
@section('content')

@if(!auth()->user()->isCollegeAdmin())
<div class="card">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filter Classes
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="university_id" class="font-weight-bold">University</label>
                    <select name="university_id" id="university_id" class="form-control">
                        <option value="">-- All Universities --</option>
                        @foreach($universities as $university)
                            <option value="{{ $university->id }}" {{ request('university_id') == $university->id ? 'selected' : '' }}>{{ $university->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="college_id" class="font-weight-bold">College</label>
                    <select name="college_id" id="college_id" class="form-control">
                        <option value="">-- All Colleges --</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" data-university="{{ $college->university_id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>{{ $college->name }} ({{ $college->university->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('admin.internship-classes.index') }}" class="btn btn-secondary btn-block ml-2"><i class="fas fa-times"></i> Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@can('internship_class_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.internship-classes.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.internshipClass.title_singular') }}
            </a>
        </div>
    </div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('cruds.internshipClass.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-classes">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>{{ trans('cruds.internshipClass.fields.id') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.title') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.internship') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.college') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.mode') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.schedule') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.timing') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.next_class') }}</th>
                        <th>{{ trans('cruds.internshipClass.fields.is_active') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                        @php($next = $class->is_active ? ($class->occurrences(null, 1)[0] ?? null) : null)
                        <tr data-entry-id="{{ $class->id }}">
                            <td></td>
                            <td>{{ $class->id }}</td>
                            <td>{{ $class->title }}</td>
                            <td>{{ $class->internship->name ?? '' }}</td>
                            <td>{{ $class->internship->college->name ?? '' }}</td>
                            <td>{{ \App\InternshipClass::MODE_SELECT[$class->mode] ?? $class->mode }}</td>
                            <td>
                                {{ $class->schedule_summary }}
                                @if($class->recurrence !== 'none')
                                    <br><small class="text-muted">{{ $class->start_date->format('d M Y') }} &ndash; {{ $class->end_date->format('d M Y') }}</small>
                                @else
                                    <br><small class="text-muted">{{ $class->start_date->format('d M Y') }}</small>
                                @endif
                            </td>
                            <td>{{ $class->time_range }}</td>
                            <td>{{ $next ? $next->format('d M Y') : '-' }}</td>
                            <td>{{ $class->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            <td>
                                @can('internship_class_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.internship-classes.show', $class->id) }}">{{ trans('global.view') }}</a>
                                @endcan
                                @can('internship_class_show')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.internship-classes.show', $class->id) }}#whatsapp" title="Send class details to enrolled students on WhatsApp">
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                    </a>
                                @endcan
                                @can('internship_class_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.internship-classes.edit', $class->id) }}">{{ trans('global.edit') }}</a>
                                @endcan
                                @can('internship_class_delete')
                                    <form action="{{ route('admin.internship-classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
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
@parent
<script>
$(function () {
    $.extend(true, $.fn.dataTable.defaults, { order: [[1, 'desc']], pageLength: 100 });
    $('.datatable-classes').DataTable({ buttons: $.extend(true, [], $.fn.dataTable.defaults.buttons) });
});
</script>
@endsection
