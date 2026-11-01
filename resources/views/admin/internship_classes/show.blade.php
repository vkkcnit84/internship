@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ trans('global.show') }} {{ trans('cruds.internshipClass.title_singular') }}</span>
        <a class="btn btn-success btn-sm" href="#whatsapp"><i class="fab fa-whatsapp"></i> Send on WhatsApp</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th style="width: 25%">Title</th><td>{{ $class->title }}</td></tr>
                <tr><th>Internship</th><td>{{ $class->internship->name ?? '' }}</td></tr>
                <tr><th>College</th><td>{{ $class->internship->college->full_name ?? '' }}</td></tr>
                @if($class->description)
                    <tr><th>Description</th><td>{!! nl2br(e($class->description)) !!}</td></tr>
                @endif
                <tr><th>Mode</th><td>{{ \App\InternshipClass::MODE_SELECT[$class->mode] ?? $class->mode }}</td></tr>
                @if($class->mode === 'online' && $class->meeting_link)
                    <tr><th>Meeting link</th><td><a href="{{ $class->meeting_link }}" target="_blank" rel="noopener noreferrer">{{ $class->meeting_link }}</a></td></tr>
                @endif
                @if($class->mode === 'offline' && $class->venue)
                    <tr><th>Venue</th><td>{{ $class->venue }}</td></tr>
                @endif
                <tr><th>Repeats</th><td>{{ \App\InternshipClass::RECURRENCE_SELECT[$class->recurrence] ?? $class->recurrence }}@if($class->recurrence === 'weekly') &middot; {{ $class->schedule_summary }}@endif</td></tr>
                <tr><th>Dates</th><td>{{ $class->start_date->format('d M Y') }}@if($class->recurrence !== 'none') &ndash; {{ $class->end_date->format('d M Y') }}@endif</td></tr>
                <tr><th>Timing</th><td>{{ $class->time_range }}</td></tr>
                <tr><th>Total sessions</th><td>{{ $class->totalSessions() }}</td></tr>
                <tr><th>Active</th><td>{{ $class->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                <tr><th>Created by</th><td>{{ $class->creator->name ?? '' }}</td></tr>
            </tbody>
        </table>

        <h6 class="mt-4">Upcoming sessions</h6>
        @forelse($upcoming as $date)
            <span class="badge badge-info" style="font-size: 13px; margin: 2px;">{{ $date->format('D, d M Y') }}</span>
        @empty
            <p class="text-muted">No upcoming sessions.</p>
        @endforelse

        <div class="mt-4">
            <a class="btn btn-default" href="{{ route('admin.internship-classes.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>

<div class="card" id="whatsapp">
    <div class="card-header"><i class="fab fa-whatsapp" style="color: #25d366;"></i> Send class details on WhatsApp</div>
    <div class="card-body">
        <p class="text-muted">
            Students with a paid registration for <strong>{{ $class->internship->name ?? '' }}</strong>.
            Each button opens WhatsApp with the message filled in; press send there.
        </p>

        <div class="form-group">
            <label for="group-message"><strong>Message for a group or broadcast</strong></label>
            <textarea id="group-message" class="form-control" rows="12" readonly>{{ $groupMessage }}</textarea>
            <div class="mt-2">
                <button type="button" class="btn btn-info btn-sm" id="copy-message"><i class="fas fa-copy"></i> Copy message</button>
                <a class="btn btn-success btn-sm" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($groupMessage) }}"><i class="fab fa-whatsapp"></i> Open WhatsApp</a>
                <span id="copy-note" class="text-success ml-2" style="display: none;">Copied</span>
            </div>
        </div>

        <h6 class="mt-4">Enrolled students ({{ count($recipients) }})</h6>
        @if(count($recipients))
            <table class="table table-bordered table-striped">
                <thead><tr><th>Student</th><th>WhatsApp number</th><th style="width: 200px;"></th></tr></thead>
                <tbody>
                    @foreach($recipients as $r)
                        <tr>
                            <td>{{ $r['name'] }}</td>
                            <td>{{ $r['phone'] ? '+' . substr($r['phone'], 0, 2) . ' ' . substr($r['phone'], 2) : '-' }}</td>
                            <td>
                                @if($r['url'])
                                    <a class="btn btn-success btn-sm wa-send" target="_blank" rel="noopener" href="{{ $r['url'] }}" data-key="wa-{{ $class->id }}-{{ $r['key'] }}">
                                        <i class="fab fa-whatsapp"></i> <span>Send on WhatsApp</span>
                                    </a>
                                @else
                                    <span class="text-danger">No valid number</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No paid registrations yet for this internship.</p>
        @endif
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
$(function () {
    $('#copy-message').on('click', function () {
        var done = function () { $('#copy-note').show().delay(1500).fadeOut(); };
        if (navigator.clipboard) {
            navigator.clipboard.writeText($('#group-message').val()).then(done);
        } else {
            $('#group-message').select(); document.execCommand('copy'); done();
        }
    });

    // Remember (in this browser only) which students were already messaged.
    $('.wa-send').each(function () {
        var $a = $(this), key = $a.data('key');
        var mark = function () { $a.removeClass('btn-success').addClass('btn-secondary').find('span').text('Sent - send again'); };
        if (localStorage.getItem(key)) { mark(); }
        $a.on('click', function () { localStorage.setItem(key, '1'); mark(); });
    });
});
</script>
@endsection
