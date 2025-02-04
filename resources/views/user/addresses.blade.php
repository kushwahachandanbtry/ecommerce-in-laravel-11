<!-- @extends('layouts.app')
@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
      <h2 class="page-title">Addresses</h2>
      <div class="row">
        <div class="col-lg-3">
          @include('user.account-nav')
        </div>
        <div class="col-lg-9">
          <div class="page-content my-account__address">
            <div class="row">
              <div class="col-6">
                <p class="notice">The following addresses will be used on the checkout page by default.</p>
              </div>
              <div class="col-6 text-right">
                <a href="{{route('user.address.add')}}" class="btn btn-sm btn-info">Add New</a>
              </div>
            </div>
            <div class="my-account__address-list row">
              <h5>Shipping Address</h5>

              <div class="my-account__address-item col-md-6">
                <div class="my-account__address-item__title">
                  <h5>Sudhir Kumar <i class="fa fa-check-circle text-success"></i></h5>
                  <a href="#">Edit</a>
                </div>
                <div class="my-account__address-item__detail">
                  <p>Flat No - 13, R. K. Wing - B</p>
                  <p>ABC, DEF</p>
                  <p>GHJ, </p>
                  <p>Near Sun Temple</p>
                  <p>000000</p>
                  <br>
                  <p>Mobile : 1234567891</p>
                </div>
              </div>
              <hr>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection -->
