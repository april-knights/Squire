<!doctype html>
<html lang="en">

<head>
    <title>Squire - @yield('title')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link type="text/css" href="{{ asset('static/css/app.css') }}" rel="stylesheet">
</head>

<body>
    <div id="background-color">
        <div id="background-img">
        </div>
    </div>

    <nav class="navbar navbar-expand-xl navbar-dark">
        <a class="navbar-brand" href="/">
            <img src="/static/img/BackgroundLogo.png" class="d-inline-block align-top" alt="">
            Squire
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        @if (Auth::check())
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                <a class="nav-link" href="/profile/{{ Auth::user()->rname }}">My Profile</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/battalion">Battalions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/division">Divisions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/orders">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/links">Links</a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0">
                <div class="username">{{ Auth::user()->rname }}</div>
                <!--<input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Search</button>-->
                <a class="btn btn-outline-light" href="/logout" type="submit">Logout <i class="fas fa-sign-out-alt"></i></a>
            </form>
            </div>
        @endif
      </nav>

    @if (Auth::check())
        <div class="container-xl">
            <div class="row">
                <div class="content col-lg-9">
                    @yield('content')
                </div>
                <div class="discord col-lg-3 d-none d-lg-block">
                    <iframe src="https://discordapp.com/widget?id=295643919553921035&theme=dark" width="250" height="500px" align="right" allowtransparency="true" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    @else
        <div class="container">
            @yield('content')
        </div>
    @endif

    <script type="text/javascript" src="{{ asset('static/js/app.js') }}"></script>
</body>

</html>
