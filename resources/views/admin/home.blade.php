@extends('layouts.admin')
@section('content')
<style>
    .stat-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .stat-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .stat-card .card-body {
        padding: 24px;
    }

    .stat-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        margin: 8px 0;
        color: #333;
    }

    .stat-label {
        font-size: 14px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success { background-color: #28a745; }
    .badge-warning { background-color: #ffc107; color: #333; }
    .badge-danger { background-color: #dc3545; }
    .badge-info { background-color: #17a2b8; }
    .badge-primary { background-color: #007bff; }

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .recent-table {
        margin-top: 24px;
    }

    .badge-lg {
        padding: 8px 12px;
        font-size: 12px;
    }
</style>

<div class="content">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 style="color: #333; font-weight: 600; margin-bottom: 8px;">
                {{ auth()->user()->isAdmin() ? 'Admin Dashboard' : 'Institution Dashboard' }}
            </h2>
            <p style="color: #999; margin: 0;">Welcome back, {{ auth()->user()->first_name }}!</p>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Admin Dashboard -->
    @if(auth()->user()->college_id)
        <!-- College Admin Dashboard -->
        <div class="row">
            <div class="col-lg-12">
                <h3 style="color: #333; margin-bottom: 24px;">
                    <i class="fas fa-graduation-cap" style="color: #6f42c1; margin-right: 12px;"></i>
                    College Admin - {{ $stats['college_name'] ?? 'College' }}
                    <span style="color: #999; font-size: 16px;">({{ $stats['university_name'] ?? 'University' }})</span>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #007bff;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_students'] ?? 0 }}</div>
                        <div class="stat-label">Students in College</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #e83e8c;">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_registrations'] ?? 0 }}</div>
                        <div class="stat-label">Total Registrations</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">{{ $stats['registrations_paid'] ?? 0 }}</div>
                        <div class="stat-label">Paid</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number">{{ $stats['registrations_pending'] ?? 0 }}</div>
                        <div class="stat-label">Pending Payment</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Registrations from this College -->
        @if(!empty($recent_registrations) && count($recent_registrations) > 0)
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card" style="border: none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                        <div class="card-header bg-white" style="border-bottom: 1px solid #e9ecef; padding: 20px; border-radius: 8px 8px 0 0;">
                            <h5 style="margin: 0; font-weight: 600;">Students from Your College</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 recent-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="border: none; padding: 16px;">Student Name</th>
                                            <th style="border: none; padding: 16px;">Course (Stream) / Major Subject</th>
                                            <th style="border: none; padding: 16px;">Program</th>
                                            <th style="border: none; padding: 16px;">Status</th>
                                            <th style="border: none; padding: 16px;">Amount</th>
                                            <th style="border: none; padding: 16px;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_registrations as $reg)
                                            <tr>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->user->first_name ?? 'N/A' }} {{ $reg->user->last_name ?? '' }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">
                                                    {{ optional($reg->studentProfile->stream)->name ?? 'N/A' }} / {{ optional($reg->studentProfile->subject)->name ?? 'N/A' }}
                                                </td>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->internship->name ?? 'N/A' }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">
                                                    @if($reg->isPaid())
                                                        <span class="badge badge-success badge-lg">Paid</span>
                                                    @else
                                                        <span class="badge badge-warning badge-lg">Pending</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 16px; vertical-align: middle;">₹{{ number_format($reg->total_amount, 2) }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @elseif(auth()->user()->isAdmin())
        <!-- System Overview -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #007bff;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_users'] ?? 0 }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_institutions'] ?? 0 }}</div>
                        <div class="stat-label">Institutions</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_courses'] ?? 0 }}</div>
                        <div class="stat-label">Courses</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #17a2b8;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_enrollments'] ?? 0 }}</div>
                        <div class="stat-label">Enrollments</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Internship Section -->
        <div class="row mt-4">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #6f42c1;">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_internships'] ?? 0 }}</div>
                        <div class="stat-label">Internship Domains</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #e83e8c;">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_registrations'] ?? 0 }}</div>
                        <div class="stat-label">Registrations</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_paid'] ?? 0 }}</div>
                        <div class="stat-label">Paid Registrations</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_pending'] ?? 0 }}</div>
                        <div class="stat-label">Pending Payments</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Section -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #20c997;">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-number">₹{{ number_format($stats['revenue'] ?? 0, 0) }}</div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="stat-number">{{ $payment_summary['total_captured'] ?? 0 }}</div>
                        <div class="stat-label">Payments Captured</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #dc3545;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="stat-number">{{ $payment_summary['total_failed'] ?? 0 }}</div>
                        <div class="stat-label">Failed Payments</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Registrations -->
        @if(!empty($recent_registrations) && count($recent_registrations) > 0)
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card" style="border: none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                        <div class="card-header bg-white" style="border-bottom: 1px solid #e9ecef; padding: 20px; border-radius: 8px 8px 0 0;">
                            <h5 style="margin: 0; font-weight: 600;">Recent Internship Registrations</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 recent-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="border: none; padding: 16px;">Student Name</th>
                                            <th style="border: none; padding: 16px;">Email</th>
                                            <th style="border: none; padding: 16px;">Program</th>
                                            <th style="border: none; padding: 16px;">Status</th>
                                            <th style="border: none; padding: 16px;">Amount</th>
                                            <th style="border: none; padding: 16px;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_registrations as $reg)
                                            <tr>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->user->first_name ?? 'N/A' }} {{ $reg->user->last_name ?? '' }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->user->email ?? 'N/A' }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->internship->name ?? 'N/A' }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">
                                                    @if($reg->isPaid())
                                                        <span class="badge badge-success badge-lg">Paid</span>
                                                    @else
                                                        <span class="badge badge-warning badge-lg">Pending</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 16px; vertical-align: middle;">₹{{ number_format($reg->total_amount, 2) }}</td>
                                                <td style="padding: 16px; vertical-align: middle;">{{ $reg->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @else
        <!-- Institution Dashboard -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #007bff;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_users'] ?? 0 }}</div>
                        <div class="stat-label">Users</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number">{{ $stats['total_courses'] ?? 0 }}</div>
                        <div class="stat-label">Courses</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">{{ $stats['enrollment_accepted'] ?? 0 }}</div>
                        <div class="stat-label">Accepted</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stat-card bg-white">
                    <div class="card-body">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number">{{ $stats['enrollment_pending'] ?? 0 }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card" style="border: none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                    <div class="card-body" style="padding: 40px; text-align: center;">
                        <i class="fas fa-info-circle" style="font-size: 48px; color: #17a2b8; margin-bottom: 20px;"></i>
                        <h4 style="color: #333;">Quick Stats Overview</h4>
                        <p style="color: #999;">Manage your institution's courses and student enrollments from the menu above.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
@section('scripts')
@parent
@endsection