<!-- @extends('layouts.app')
@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">Account Details</h2>
        <div class="row">
            <div class="col-lg-3">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__edit">
                    <div class="my-account__edit-form">
                        <form name="account_edit_form" action="#" method="POST" class="needs-validation" novalidate="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" placeholder="Full Name" name="name"
                                            value="{{$user->name}}" required="">
                                        <label for="name">Name</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating my-3">
                                        <input type="text" class="form-control" placeholder="Mobile Number"
                                            name="mobile" value="{{$user->mobile}}" required="">
                                        <label for="mobile">Mobile Number</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating my-3">
                                        <input type="email" class="form-control" placeholder="Email Address"
                                            name="email" value="{{$user->email}}" required="">
                                        <label for="account_email">Email Address</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="my-3">
                                        <h5 class="text-uppercase mb-0">Password Change</h5>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating my-3">
                                        <input type="password" class="form-control" id="old_password"
                                            name="old_password" placeholder="Old password" required="">
                                        <label for="old_password">Old password</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating my-3">
                                        <input type="password" class="form-control" id="new_password"
                                            name="new_password" placeholder="New password" required="">
                                        <label for="account_new_password">New password</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating my-3">
                                        <input type="password" class="form-control" cfpwd="" data-cf-pwd="#new_password"
                                            id="new_password_confirmation" name="new_password_confirmation"
                                            placeholder="Confirm new password" required="">
                                        <label for="new_password_confirmation">Confirm new password</label>
                                        <div class="invalid-feedback">Passwords did not match!</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="my-3">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection -->
