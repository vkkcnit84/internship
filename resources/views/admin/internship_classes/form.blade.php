@extends('layouts.admin')
@section('content')
@php
    $isEdit      = (bool) $class;
    $collegeVal  = old('college_id', $class && $class->internship ? $class->internship->college_id : '');
    $recurrence  = old('recurrence', $class ? $class->recurrence : 'none');
    $mode        = old('mode', $class ? $class->mode : 'online');
    $days        = old('days_of_week', $class ? $class->days : []);
    $fmtDate     = function ($d) { return $d ? $d->format('Y-m-d') : ''; };
    $fmtTime     = function ($t) { return $t ? substr($t, 0, 5) : ''; };
@endphp

<div class="card">
    <div class="card-header">
        {{ $isEdit ? trans('global.edit') : trans('global.create') }} {{ trans('cruds.internshipClass.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ $isEdit ? route('admin.internship-classes.update', $class->id) : route('admin.internship-classes.store') }}" method="POST">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="form-row">
                @if(!auth()->user()->isCollegeAdmin())
                    <div class="form-group col-md-6">
                        <label for="college_id">College</label>
                        <select id="college_id" name="college_id" class="form-control">
                            <option value="">-- All Colleges --</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ (string) $collegeVal === (string) $college->id ? 'selected' : '' }}>{{ $college->name }} ({{ $college->university->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Choose a college to create the class on its behalf.</small>
                    </div>
                @endif
                <div class="form-group {{ auth()->user()->isCollegeAdmin() ? 'col-md-12' : 'col-md-6' }}">
                    <label for="internship_id">Internship Domain *</label>
                    <select id="internship_id" name="internship_id" class="form-control" required></select>
                    @if($errors->has('internship_id'))
                        <em class="invalid-feedback" style="display: block;">{{ $errors->first('internship_id') }}</em>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $class->title ?? '') }}" required>
                @if($errors->has('title'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('title') }}</em>@endif
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $class->description ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="mode">Mode *</label>
                    <select id="mode" name="mode" class="form-control">
                        @foreach(\App\InternshipClass::MODE_SELECT as $key => $label)
                            <option value="{{ $key }}" {{ $mode === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-8" id="link-group">
                    <label for="meeting_link">Meeting link</label>
                    <input type="url" id="meeting_link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..." value="{{ old('meeting_link', $class->meeting_link ?? '') }}">
                    @if($errors->has('meeting_link'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('meeting_link') }}</em>@endif
                </div>
                <div class="form-group col-md-8" id="venue-group">
                    <label for="venue">Venue</label>
                    <input type="text" id="venue" name="venue" class="form-control" placeholder="Room / hall / address" value="{{ old('venue', $class->venue ?? '') }}">
                </div>
            </div>

            <hr>
            <h6>Schedule</h6>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="recurrence">Repeats *</label>
                    <select id="recurrence" name="recurrence" class="form-control">
                        @foreach(\App\InternshipClass::RECURRENCE_SELECT as $key => $label)
                            <option value="{{ $key }}" {{ $recurrence === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="start_date"><span id="start-date-label">Date</span> *</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $fmtDate($class->start_date ?? null)) }}" required>
                    @if($errors->has('start_date'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('start_date') }}</em>@endif
                </div>
                <div class="form-group col-md-4" id="end-date-group">
                    <label for="end_date">Repeat until *</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', $class && $class->recurrence !== 'none' ? $fmtDate($class->end_date) : '') }}">
                    @if($errors->has('end_date'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('end_date') }}</em>@endif
                </div>
            </div>

            <div class="form-group" id="days-group">
                <label>Days of the week *</label><br>
                @foreach(\App\InternshipClass::DAYS as $num => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="day{{ $num }}" name="days_of_week[]" value="{{ $num }}" {{ in_array($num, array_map('intval', (array) $days), true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="day{{ $num }}">{{ $label }}</label>
                    </div>
                @endforeach
                @if($errors->has('days_of_week'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('days_of_week') }}</em>@endif
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="start_time">Start time *</label>
                    <input type="time" id="start_time" name="start_time" class="form-control" value="{{ old('start_time', $fmtTime($class->start_time ?? null)) }}" required>
                    @if($errors->has('start_time'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('start_time') }}</em>@endif
                </div>
                <div class="form-group col-md-3">
                    <label for="end_time">End time *</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" value="{{ old('end_time', $fmtTime($class->end_time ?? null)) }}" required>
                    @if($errors->has('end_time'))<em class="invalid-feedback" style="display: block;">{{ $errors->first('end_time') }}</em>@endif
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $class ? $class->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ trans('global.active') }}</label>
                </div>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
                <a class="btn btn-default" href="{{ route('admin.internship-classes.index') }}">{{ trans('global.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
$(function () {
    var internships = @json($internships->map(function ($i) {
        return ['id' => $i->id, 'college_id' => $i->college_id, 'label' => $i->name . ($i->college ? ' - ' . $i->college->name : ' (all colleges)')];
    })->values());
    var selectedInternship = '{{ old('internship_id', $class->internship_id ?? '') }}';

    function fillInternships() {
        var college = $('#college_id').length ? $('#college_id').val() : '';
        var $sel = $('#internship_id').empty().append(new Option('{{ trans('global.pleaseSelect') }}', ''));
        internships.forEach(function (i) {
            if (!college || i.college_id === null || String(i.college_id) === String(college)) {
                $sel.append(new Option(i.label, i.id, false, String(i.id) === String(selectedInternship)));
            }
        });
    }

    function syncMode() {
        var online = $('#mode').val() === 'online';
        $('#link-group').toggle(online);
        $('#venue-group').toggle(!online);
    }

    function syncRecurrence() {
        var r = $('#recurrence').val();
        $('#days-group').toggle(r === 'weekly');
        $('#end-date-group').toggle(r !== 'none');
        $('#end_date').prop('required', r !== 'none');
        $('#start-date-label').text(r === 'none' ? 'Date' : 'Starts on');
    }

    $('#college_id').on('change', function () { selectedInternship = ''; fillInternships(); });
    $('#internship_id').on('change', function () { selectedInternship = $(this).val(); });
    $('#mode').on('change', syncMode);
    $('#recurrence').on('change', syncRecurrence);

    fillInternships();
    syncMode();
    syncRecurrence();
});
</script>
@endsection
