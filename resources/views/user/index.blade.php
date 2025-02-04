@extends('layouts.app')

@section('content')

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
      <h2 class="page-title">My Account</h2>
      <div class="row">
        <div class="col-lg-3">
          @include('user.account-nav')
        </div>
        <div class="col-lg-9">
          <div class="page-content my-account__dashboard">
            <p>Hello <strong>User</strong></p>
            <p>Thank you for choosing us for your shopping needs. Enjoy a seamless shopping experience with amazing deals, top-quality products, and fast delivery! You can see your <a class="unerline-link" href="{{route('user.orders')}}">recent
                orders</a>, manage your <a class="unerline-link" href="{{route('wishlist.index')}}">wishlist
                </a></p>
          </div>
        </div>
      </div>
    </section>
  </main>

@endsection
