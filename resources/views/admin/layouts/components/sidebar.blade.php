<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <i class="fa fa-user fa-3x mt-3"></i>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">

        </div>

        <ul class="sidebar-menu">
            <li class=""><a class="nav-link " href="{{ route('users') }}"><i
                        class="fas fa-users"></i> <span>Users</span></a></li>
            <li class=""><a class="nav-link " href="{{ route('products') }}"><i class="fas fa-store"></i>
                    <span>Products</span></a></li>
            <li class=""><a class="nav-link " href="{{ route('price_rules') }}"><i class="fas fa-dollar-sign"></i>
                    <span>Price Rules</span></a></li>
            {{-- <li class="nav-item dropdown"><a class="nav-link has-dropdown" href="#"><i class="fas fa-cogs"></i>
                    <span>Setting</span></a>
                <ul class="dropdown-menu" style="display: none;">
                    <li class=""><a class="nav-link " href="#"><i class="fas fa-cog"></i> <span>Shein
                                Setting</span></a></li>
                </ul>
            </li> --}}
        </ul>
    </aside>
</div>
