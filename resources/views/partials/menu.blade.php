<div class="sidebar">
    <nav class="sidebar-nav">

        <ul class="nav">
            @if(auth()->user()->isStudentOnly())
                <li class="nav-item">
                    <a href="{{ route('admin.student.profile') }}" class="nav-link {{ request()->routeIs('admin.student.profile') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-fw fa-user-circle"></i>
                        My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.student.welcome-letter') }}" class="nav-link {{ request()->routeIs('admin.student.welcome-letter') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-fw fa-file-alt"></i>
                        Welcome Letter
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route("admin.home") }}" class="nav-link">
                        <i class="nav-icon fas fa-fw fa-tachometer-alt"></i>
                        {{ trans('global.dashboard') }}
                    </a>
                </li>
            @endif
            @can('user_management_access')
                <li class="nav-item nav-dropdown">
                    <a class="nav-link  nav-dropdown-toggle" href="#">
                        <i class="fa-fw fas fa-users nav-icon">

                        </i>
                        {{ trans('cruds.userManagement.title') }}
                    </a>
                    <ul class="nav-dropdown-items">
                        @can('permission_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.permissions.index") }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-unlock-alt nav-icon">

                                    </i>
                                    {{ trans('cruds.permission.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('role_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-briefcase nav-icon">

                                    </i>
                                    {{ trans('cruds.role.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('user_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.users.index") }}" class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-user nav-icon">

                                    </i>
                                    {{ trans('cruds.user.title') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan
            @can('discipline_access')
                <li class="nav-item">
                    <a href="{{ route("admin.disciplines.index") }}" class="nav-link {{ request()->is('admin/disciplines') || request()->is('admin/disciplines/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa-book-open nav-icon">

                        </i>
                        {{ trans('cruds.discipline.title') }}
                    </a>
                </li>
            @endcan
            @can('institution_access')
                <li class="nav-item">
                    <a href="{{ route("admin.institutions.index") }}" class="nav-link {{ request()->is('admin/institutions') || request()->is('admin/institutions/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa-university nav-icon">

                        </i>
                        {{ trans('cruds.institution.title') }}
                    </a>
                </li>
            @endcan
            @can('course_access')
                <li class="nav-item">
                    <a href="{{ route("admin.courses.index") }}" class="nav-link {{ request()->is('admin/courses') || request()->is('admin/courses/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa-certificate nav-icon">

                        </i>
                        {{ trans('cruds.course.title') }}
                    </a>
                </li>
            @endcan
            @can('enrollment_access')
                <li class="nav-item">
                    <a href="{{ route("admin.enrollments.index") }}" class="nav-link {{ request()->is('admin/enrollments') || request()->is('admin/enrollments/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa-highlighter nav-icon">

                        </i>
                        {{ trans('cruds.enrollment.title') }}
                    </a>
                </li>
            @endcan
            @can('internship_management_access')
                <li class="nav-item nav-dropdown">
                    <a class="nav-link  nav-dropdown-toggle" href="#">
                        <i class="fa-fw fas fa-graduation-cap nav-icon">

                        </i>
                        {{ trans('cruds.internshipManagement.title') }}
                    </a>
                    <ul class="nav-dropdown-items">
                        @if(!auth()->user()->isCollegeAdmin())
                            @can('university_access')
                                <li class="nav-item">
                                    <a href="{{ route("admin.universities.index") }}" class="nav-link {{ request()->is('admin/universities') || request()->is('admin/universities/*') ? 'active' : '' }}">
                                        <i class="fa-fw fas fa-university nav-icon">

                                        </i>
                                        {{ trans('cruds.university.title') }}
                                    </a>
                                </li>
                            @endcan
                            @can('college_access')
                                <li class="nav-item">
                                    <a href="{{ route("admin.colleges.index") }}" class="nav-link {{ request()->is('admin/colleges') || request()->is('admin/colleges/*') ? 'active' : '' }}">
                                        <i class="fa-fw fas fa-school nav-icon">

                                        </i>
                                        {{ trans('cruds.college.title') }}
                                    </a>
                                </li>
                            @endcan
                        @endif
                        @can('stream_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.streams.index") }}" class="nav-link {{ request()->is('admin/streams') || request()->is('admin/streams/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-stream nav-icon">

                                    </i>
                                    {{ trans('cruds.stream.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('subject_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.subjects.index") }}" class="nav-link {{ request()->is('admin/subjects') || request()->is('admin/subjects/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-book nav-icon">

                                    </i>
                                    {{ trans('cruds.subject.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('internship_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.internships.index") }}" class="nav-link {{ request()->is('admin/internships') || request()->is('admin/internships/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-id-badge nav-icon">

                                    </i>
                                    {{ trans('cruds.internship.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('internship_class_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.internship-classes.index") }}" class="nav-link {{ request()->is('admin/internship-classes') || request()->is('admin/internship-classes/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-chalkboard-teacher nav-icon">

                                    </i>
                                    {{ trans('cruds.internshipClass.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('internship_registration_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.internship-registrations.index") }}" class="nav-link {{ request()->is('admin/internship-registrations') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-clipboard-list nav-icon">

                                    </i>
                                    {{ trans('cruds.internshipRegistration.title') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <i class="nav-icon fas fa-fw fa-sign-out-alt">

                    </i>
                    {{ trans('global.logout') }}
                </a>
            </li>
        </ul>

    </nav>
    <button class="sidebar-minimizer brand-minimizer" type="button"></button>
</div>