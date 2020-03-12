@extends("layouts.app")

@section("title")
    Login
@endsection

@section("content")
@if(session()->has('error'))
    <div class="alert alert-error">
        {{ session()->get('error') }}
    </div>
@endif
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">Login</div>
                <div class="panel-heading">Login with Reddit</div>
                <div class="panel-body">
                    <a class="btn btn-primary" href="/login/reddit">
                        Reddit Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
