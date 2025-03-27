<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard.index') }}"><img class="dashboard-w" src="" alt=""
                    style="max-height: max-content"></a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">

        </div>

        <ul class="sidebar-menu">
            <li class=""><a class="nav-link " href="http://127.0.0.1:8000/admin/dashboard"><i
                        class="fas fa-users"></i> <span>Users</span></a></li>
            <li class=""><a class="nav-link " href="{{ route('products') }}"><i
                        class="fas fa-store"></i> <span>Shein Store</span></a></li>
            <li class="nav-item dropdown "><a class="nav-link has-dropdown" href="http://127.0.0.1:8000/admin/#"><i
                        class="fa fa-cogs" aria-hidden="true"></i> <span>Setting</span></a>
                <ul class="dropdown-menu">
                    <li class=""><a class="nav-link " href="http://127.0.0.1:8000/admin/category"> <span>Shein Setting</span></a></li>
                </ul>
            </li>
        </ul>
    </aside>
</div>
