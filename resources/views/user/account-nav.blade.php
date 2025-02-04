<ul class="account-nav">
    <li><a href="{{route('user.index')}}" class="menu-link menu-link_us-s">Dashboard</a></li>
    <li><a href="{{route('user.orders')}}" class="menu-link menu-link_us-s">Orders</a></li>
    <!-- <li><a href="" class="menu-link menu-link_us-s">Addresses</a></li>
    <li><a href="" class="menu-link menu-link_us-s">Account Details</a></li> -->
    <li><a href="{{route('wishlist.index')}}" class="menu-link menu-link_us-s">Wishlist</a></li>

    <li>
        <form action="{{route('logout')}}" method="POST" id="logout-form">
            @csrf
            <a href="{{route('logout')}}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="menu-link menu-link_us-s">Logout</a>
        </form>
    </li>
</ul>
