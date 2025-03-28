<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg" id="sidebar-toggle-button"><i class="fas fa-bars"></i></a></li>
        </ul>
    </div>

    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <i class="fa fa-user"></i>
                <div class="d-sm-none d-lg-inline-block">{{ __('Hi') }}, {{ auth()->user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-divider"></div>
                <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="display-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>
