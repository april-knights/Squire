<!doctype html>
<html lang="en">

<head>
    <title>Squire - @yield("title")</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/style.css">
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
              <a class="nav-link" href="/profile">My Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/battalions">Battalions</a>
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
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Search</button>
          </form>
        </div>
        @endif
      </nav>

    <div class="content container-xl">
        @yield("content")
    </div>

    @yield("script")
</body>

</html>
