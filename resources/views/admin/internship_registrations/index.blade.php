@extends('layouts.admin')
@section('content')

@if(!auth()->user()->isCollegeAdmin())
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filter Registrations
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="university_id" class="font-weight-bold">University</label>
                    <select name="university_id" id="university_id" class="form-control select2" onchange="filterCollegesByUniversity()">
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
                    <a href="{{ route('admin.internship-registrations.index') }}" class="btn btn-secondary btn-block ml-2">
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
        {{ trans('cruds.internshipRegistration.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-registrations">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>{{ trans('cruds.internshipRegistration.fields.id') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.student') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.email') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.mobile') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.internship') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.university') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.college') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.stream') }}</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.subject') }}</th>
                        <th>Session</th>
                        @can('internship_registration_amount_view')
                            <th>{{ trans('cruds.internshipRegistration.fields.total_amount') }}</th>
                        @endcan
                        <th>Payment Method</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.status') }}</th>
                        <th>Approval Status</th>
                        <th>{{ trans('cruds.internshipRegistration.fields.paid_at') }}</th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $registration)
                        @php($profile = $registration->user->studentProfile)
                        <tr data-entry-id="{{ $registration->id }}">
                            <td>

                            </td>
                            <td>{{ $registration->id }}</td>
                            <td>{{ $registration->user->name }}</td>
                            <td>{{ $registration->user->email }}</td>
                            <td>{{ $profile->mobile ?? '' }}</td>
                            <td>{{ $registration->internship->name ?? '' }}</td>
                            <td>{{ $profile->university->name ?? '' }}</td>
                            <td>{{ $profile->college->name ?? '' }}</td>
                            <td>{{ $profile->stream->name ?? '' }}</td>
                            <td>{{ $profile->subject->name ?? '' }}</td>
                            <td>{{ $profile->academic_session ?? '' }}</td>
                            @can('internship_registration_amount_view')
                                <td>&#8377;{{ number_format($registration->total_amount, 2) }}</td>
                            @endcan
                            <td>{{ \App\InternshipRegistration::PAYMENT_METHODS[$registration->payment_method] ?? $registration->payment_method }}</td>
                            <td>{{ \App\InternshipRegistration::STATUS_SELECT[$registration->status] ?? $registration->status }}</td>
                            <td>
                                @if($registration->payment_method === 'offline')
                                    @if($registration->approval_status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($registration->approval_status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>
                            <td>{{ optional($registration->paid_at)->format(config('panel.date_format') . ' ' . config('panel.time_format')) }}</td>
                            <td style="min-width: 250px;">
                                <a class="btn btn-xs btn-outline-primary" href="{{ route('admin.internship-registrations.consent-letter', $registration) }}" target="_blank" title="View / download consent letter">
                                    <i class="fas fa-file-alt"></i> Consent Letter
                                </a>
                                <a class="btn btn-xs btn-outline-primary" href="{{ route('admin.internship-registrations.acceptance-letter', $registration) }}" target="_blank" title="View / download internship acceptance letter">
                                    <i class="fas fa-file-signature"></i> Acceptance Letter
                                </a>
                                @if($registration->payment_method === 'offline')
                                    @if($registration->approval_status === 'pending')
                                        @can('internship_registration_approve')
                                            <form action="{{ route('admin.internship-registrations.approve', $registration) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success" title="Approve this offline payment">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                        @endcan
                                        @can('internship_registration_reject')
                                            <form action="{{ route('admin.internship-registrations.reject', $registration) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this payment?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger" title="Reject this offline payment">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        @endcan
                                    @elseif($registration->approval_status === 'approved')
                                        @if($registration->status !== 'paid')
                                            @can('internship_registration_mark_paid')
                                                <form action="{{ route('admin.internship-registrations.mark-paid', $registration) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-info" title="Mark payment as received">
                                                        <i class="fas fa-money-bill"></i> Mark Paid
                                                    </button>
                                                </form>
                                            @endcan
                                        @else
                                            @can('internship_registration_mark_paid')
                                                <form action="{{ route('admin.internship-registrations.mark-unpaid', $registration) }}" method="POST" style="display: inline;" title="Click to change status back to unpaid if needed">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-warning">
                                                        <i class="fas fa-undo"></i> Mark Unpaid
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    @endif
                                @endif
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
function filterCollegesByUniversity() {
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

    // Clear college selection when university changes
    if (universityId !== '') {
        collegeSelect.value = '';
    }

    // Reinitialize select2 if it was already initialized
    if ($.fn.select2) {
        $(collegeSelect).trigger('change');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    filterCollegesByUniversity();

    $.extend(true, $.fn.dataTable.defaults, {
        order: [[ 1, 'desc' ]],
        pageLength: 100,
    });
    $('.datatable-registrations:not(.ajaxTable)').DataTable({ buttons: $.extend(true, [], $.fn.dataTable.defaults.buttons) })
});
</script>
@endsection
