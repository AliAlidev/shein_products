<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <i class="fa fa-user fa-3x mt-3"></i>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">

        </div>

        <ul class="sidebar-menu">
            <li class=""><a class="nav-link " href="http://127.0.0.1:8000/admin/dashboard"><i
                        class="fas fa-users"></i> <span>Users</span></a></li>
            <li class=""><a class="nav-link " href="{{ route('products') }}"><i class="fas fa-store"></i>
                    <span>Shein Store</span></a></li>
            <li class="nav-item dropdown"><a class="nav-link has-dropdown" href="#"><i class="fas fa-cogs"></i>
                    <span>Setting</span></a>
                <ul class="dropdown-menu" style="display: none;">
                    <li class=""><a class="nav-link " href="#"><i class="fas fa-cog"></i> <span>Shein
                                Setting</span></a></li>
                </ul>
            </li>
        </ul>
    </aside>
</div>
