<nav class="navbar navbar-light shadow-sm px-3">

    <span class="navbar-brand">Dashboard</span>

    <div class="dropdown ms-auto">
        @if (auth()->user()->hasPermission('sales', 'create'))
        <a href="{{ route('sales.pos') }}" class="btn btn-primary m-1">
            <i class="bi bi-cart3 me-1"></i>
            POS Sale
        </a>
        @endif
        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            {{ auth()->user()->name }}
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
            @if (auth()->user()->isAdministrator())
                <li><a class="dropdown-item" href="{{ route('users.index') }}">Users &amp; access</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">Log Out</button>
                </form>
            </li>
        </ul>

    </div>

</nav>