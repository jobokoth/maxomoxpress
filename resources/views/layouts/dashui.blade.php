<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="author" content="Masomo School ERP" />
    <title>{{ $title ?? 'Masomo School ERP' }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('dashui/assets/images/favicon/favicon.ico') }}" />
    <script src="{{ asset('dashui/assets/js/vendors/color-modes.js') }}"></script>

    <link href="{{ asset('dashui/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('dashui/assets/libs/@mdi/font/css/materialdesignicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('dashui/assets/libs/simplebar/dist/simplebar.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('dashui/assets/css/theme.min.css') }}">
</head>

<body>
    <main id="main-wrapper" class="main-wrapper">
        <nav class="navbar-vertical navbar">
            <div class="nav-scroller">
                <a class="navbar-brand" href="{{ route('tenant.dashboard', ['school_slug' => $school->slug]) }}">
                    <img src="{{ asset('dashui/assets/images/brand/logo/logo-2.svg') }}" alt="logo" />
                </a>

                <ul class="navbar-nav flex-column" id="sideNavbar">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}" href="{{ route('tenant.dashboard', ['school_slug' => $school->slug]) }}">
                            <i data-feather="home" class="nav-icon icon-xs me-2"></i> Dashboard
                        </a>
                    </li>

                    @can('students.view')
                        <li class="nav-item">
                            <span class="navbar-heading">Students</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.students.*') && ! request()->routeIs('tenant.students.alumni') ? 'active' : '' }}" href="{{ route('tenant.students.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="user-check" class="nav-icon icon-xs me-2"></i> Admissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.students.alumni') ? 'active' : '' }}" href="{{ route('tenant.students.alumni', ['school_slug' => $school->slug]) }}">
                                <i data-feather="award" class="nav-icon icon-xs me-2"></i> Alumni
                            </a>
                        </li>
                    @endcan

                    @if (
                        auth()->user()->can('academic-years.manage')
                        || auth()->user()->can('terms.manage')
                        || auth()->user()->can('departments.manage')
                        || auth()->user()->can('courses.manage')
                        || auth()->user()->can('batches.manage')
                        || auth()->user()->can('subjects.view')
                    )
                        <li class="nav-item">
                            <span class="navbar-heading">Academic</span>
                        </li>
                    @endif

                    @can('academic-years.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.academic-years.*') ? 'active' : '' }}" href="{{ route('tenant.academic-years.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="calendar" class="nav-icon icon-xs me-2"></i> Academic Years
                            </a>
                        </li>
                    @endcan
                    @can('terms.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.terms.*') ? 'active' : '' }}" href="{{ route('tenant.terms.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="layers" class="nav-icon icon-xs me-2"></i> Terms
                            </a>
                        </li>
                    @endcan
                    @can('departments.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.departments.*') ? 'active' : '' }}" href="{{ route('tenant.departments.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="grid" class="nav-icon icon-xs me-2"></i> Departments
                            </a>
                        </li>
                    @endcan
                    @can('courses.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.courses.*') ? 'active' : '' }}" href="{{ route('tenant.courses.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="book-open" class="nav-icon icon-xs me-2"></i> Courses
                            </a>
                        </li>
                    @endcan
                    @can('batches.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.batches.*') ? 'active' : '' }}" href="{{ route('tenant.batches.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="users" class="nav-icon icon-xs me-2"></i> Batches
                            </a>
                        </li>
                    @endcan

                    @can('subjects.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.subjects.*') ? 'active' : '' }}" href="{{ route('tenant.subjects.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="bookmark" class="nav-icon icon-xs me-2"></i> Subjects
                            </a>
                        </li>
                    @endcan

                    @if (
                        auth()->user()->can('attendance.manage')
                        || auth()->user()->can('timetable.manage')
                        || auth()->user()->can('announcements.view')
                    )
                        <li class="nav-item">
                            <span class="navbar-heading">Daily Operations</span>
                        </li>
                    @endif

                    @can('attendance.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.operations.attendance.*') ? 'active' : '' }}" href="{{ route('tenant.operations.attendance.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="check-square" class="nav-icon icon-xs me-2"></i> Attendance
                            </a>
                        </li>
                    @endcan
                    @can('timetable.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.operations.timetable.*') ? 'active' : '' }}" href="{{ route('tenant.operations.timetable.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="clock" class="nav-icon icon-xs me-2"></i> Timetable
                            </a>
                        </li>
                    @endcan
                    @can('announcements.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.operations.announcements.*') ? 'active' : '' }}" href="{{ route('tenant.operations.announcements.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="bell" class="nav-icon icon-xs me-2"></i> Announcements
                            </a>
                        </li>
                    @endcan

                    @if (
                        auth()->user()->can('assessments.manage')
                        || auth()->user()->can('marks.manage')
                        || auth()->user()->can('reports.view')
                    )
                        <li class="nav-item">
                            <span class="navbar-heading">Assessment</span>
                        </li>
                    @endif
                    @can('assessments.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.assessment.exams.*') ? 'active' : '' }}" href="{{ route('tenant.assessment.exams.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="file-text" class="nav-icon icon-xs me-2"></i> Exams
                            </a>
                        </li>
                    @endcan
                    @can('marks.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.assessment.marks.*') ? 'active' : '' }}" href="{{ route('tenant.assessment.marks.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="edit-3" class="nav-icon icon-xs me-2"></i> Marks Entry
                            </a>
                        </li>
                    @endcan
                    @can('reports.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.assessment.reports.*') ? 'active' : '' }}" href="{{ route('tenant.assessment.reports.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="bar-chart-2" class="nav-icon icon-xs me-2"></i> Report Cards
                            </a>
                        </li>
                    @endcan
                    @can('reports.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.compliance.reports.*') ? 'active' : '' }}" href="{{ route('tenant.compliance.reports.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="shield" class="nav-icon icon-xs me-2"></i> Compliance Reports
                            </a>
                        </li>
                    @endcan

                    @can('fees.manage')
                        <li class="nav-item">
                            <span class="navbar-heading">Finance</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.finance.*') ? 'active' : '' }}" href="{{ route('tenant.finance.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="dollar-sign" class="nav-icon icon-xs me-2"></i> Fees & Billing
                            </a>
                        </li>
                    @endcan

                    @can('student-services.manage')
                        <li class="nav-item">
                            <span class="navbar-heading">Student Services</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.services.*') ? 'active' : '' }}" href="{{ route('tenant.services.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="life-buoy" class="nav-icon icon-xs me-2"></i> Services Hub
                            </a>
                        </li>
                    @endcan

                    @can('communications.manage')
                        <li class="nav-item">
                            <span class="navbar-heading">Communication</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.communication.*') ? 'active' : '' }}" href="{{ route('tenant.communication.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="message-square" class="nav-icon icon-xs me-2"></i> Communication Center
                            </a>
                        </li>
                    @endcan

                    @can('parent-portal.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.parent-portal.*') ? 'active' : '' }}" href="{{ route('tenant.parent-portal.index', ['school_slug' => $school->slug]) }}">
                                <i data-feather="heart" class="nav-icon icon-xs me-2"></i> Parent Portal
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </nav>

        <div id="app-content">
            <div class="header">
                <div class="navbar-custom navbar navbar-expand-lg">
                    <div class="container-fluid px-0">
                        <a id="nav-toggle" href="#" class="ms-auto ms-md-0 me-0 me-lg-3">
                            <i class="bi bi-text-indent-left text-muted fs-2"></i>
                        </a>
                        <div class="d-flex align-items-center ms-auto gap-3">
                            <span class="badge bg-light-primary text-primary">{{ $school->name }}</span>
                            <span class="text-muted small">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content-area">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('dashui/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dashui/assets/libs/feather-icons/dist/feather.min.js') }}"></script>
    <script src="{{ asset('dashui/assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('dashui/assets/js/theme.min.js') }}"></script>
    <script>
        feather.replace();
    </script>
    @stack('scripts')
</body>

</html>
