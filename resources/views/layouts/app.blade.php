<!doctype html>
<html lang="en">

<head>
    <title>Squire - @yield('title')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link type="text/css" href="{{ asset('static/css/app.css') }}" rel="stylesheet">
    @stack('styles')
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
                <li class="nav-item {{ Request::is('profile/' . Auth::user()->rname) ? 'active' : '' }}">
                    <a class="nav-link" href="/profile/{{ Auth::user()->rname }}">My Profile</a>
                </li>
                <li class="nav-item {{ Request::is('battalion') ? 'active' : '' }}">
                    <a class="nav-link" href="/battalion">Battalions</a>
                </li>
                <li class="nav-item {{ Request::is('division') ? 'active' : '' }}">
                    <a class="nav-link" href="/division">Divisions</a>
                </li>
                <li class="nav-item {{ Request::is('orders') ? 'active' : '' }}">
                    <a class="nav-link" href="/orders">Orders</a>
                </li>
                <li class="nav-item {{ Request::is('links') ? 'active' : '' }}">
                    <a class="nav-link" href="/links">Links</a>
                </li>
                @if(Auth::user()->security === 1)
                <li class="nav-item {{ Request::is('admin*') ? 'active' : '' }}">
                    <a class="nav-link" href="/admin">Admin</a>
                </li>
                @endif
                @php
                    $navElection = \App\Model\Election::active();
                    $isNavEA = $navElection && \App\Model\ElectionAdministrator::where('fkeyelection', $navElection->pkey)
                        ->where('fkeyknight', Auth::user()->pkey)
                        ->exists();
                @endphp
                @if($isNavEA)
                <li class="nav-item {{ Request::is('election*') ? 'active' : '' }}">
                    <a class="nav-link" href="/election/dashboard">EA Dashboard</a>
                </li>
                @endif
            </ul>
            <form class="form-inline my-2 my-lg-0">
                <div class="username">{{ Auth::user()->getRankName() . ' ' . Auth::user()->rname }}</div>

                {{-- Notification bell --}}
                <div class="nav-notifications dropdown mr-2" id="notification-bell">
                    <button class="btn btn-outline-light position-relative"
                            id="notif-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notif-badge badge badge-danger badge-pill d-none" id="notif-count"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right notif-dropdown" aria-labelledby="notif-toggle">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <strong>Notifications</strong>
                            <a href="#" class="small" id="notif-mark-all">Mark all read</a>
                        </div>
                        <div id="notif-list">
                            <div class="px-3 py-2 text-muted small">Loading…</div>
                        </div>
                    </div>
                </div>

                <a class="btn btn-outline-light" href="/logout" type="submit">Logout <i class="fas fa-sign-out-alt"></i></a>
            </form>
            </div>
        @endif
      </nav>

    @if (Auth::check())
<div class="container-xl">
            <div class="row">
                @hasSection('full_width')
                <div class="content col-12">
                @else
                <div class="content col-lg-9">
                @endif
                @if(session()->has('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session()->get('success') }}
                    </div>
                    @endif
                    @if(session()->has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session()->get('error') }}
                    </div>
                    @endif
                    @if(session()->has('warning'))
                    <div class="alert alert-warning" role="alert">
                        {!! session()->get('warning') !!}
                    </div>
                    @endif
                    @if(session()->has('info'))
                    <div class="alert alert-info" role="alert">
                        {{ session()->get('info') }}
                    </div>
                    @endif
                    @yield('content')
                </div>
                @unless(View::hasSection('full_width'))
                <div class="discord col-lg-3 d-none d-lg-block">
                    <iframe src="https://discordapp.com/widget?id=295643919553921035&theme=dark" width="250" height="500px" align="right" allowtransparency="true" frameborder="0"></iframe>
                </div>
                @endunless
            </div>
        </div>
    @else
        <div class="container">
            @if(session()->has('success'))
            <div class="alert alert-sucess" role="alert">
                {{ session()->get('success') }}
            </div>
            @endif
            @yield('content')
        </div>
    @endif

    <script type="text/javascript" src="{{ asset('static/js/app.js') }}"></script>
<script>
(function () {
    if (!document.getElementById('notification-bell')) return;

    var pollInterval = 60000;
    var loaded       = false;

    function timeAgo(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)    return 'just now';
        if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function updateBadge(count) {
        var $badge = $('#notif-count');
        if (count > 0) {
            $badge.text(count > 99 ? '99+' : count).removeClass('d-none');
        } else {
            $badge.addClass('d-none');
        }
    }

    function renderNotifications(data) {
        var $list = $('#notif-list');
        $list.empty();

        if (!data.notifications || data.notifications.length === 0) {
            $list.append('<div class="notif-empty">You\'re all caught up!</div>');
            return;
        }

        $.each(data.notifications, function (i, n) {
            var $row = $('<a>')
                .addClass('notif-item unread')
                .attr('href', '#')
                .on('click', function (e) {
                    e.preventDefault();
                    $.post('/notifications/' + n.pkey + '/read', {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).always(function () {
                        window.location.href = n.url || '/';
                    });
                });

            $row.append($('<span>').text(n.message));
            $row.append($('<span>').addClass('notif-time').text(timeAgo(n.crtsetdt)));
            $list.append($row);
        });
    }

    function fetchNotifications(renderDropdown) {
        $.getJSON('/notifications', function (data) {
            updateBadge(data.count);
            if (renderDropdown) {
                renderNotifications(data);
                loaded = true;
            }
        });
    }

    // Load dropdown on first open only
    $('#notification-bell').on('show.bs.dropdown', function () {
        if (!loaded) {
            fetchNotifications(true);
        }
    });

    // Mark all read
    $('#notif-mark-all').on('click', function (e) {
        e.preventDefault();
        $.post('/notifications/read-all', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function () {
            updateBadge(0);
            loaded = false;
            $('#notif-list').html('<div class="notif-empty">You\'re all caught up!</div>');
        });
    });

    // Poll badge count periodically
    fetchNotifications(false);
    setInterval(function () { fetchNotifications(false); }, pollInterval);
})();
</script>
</body>

</html>
