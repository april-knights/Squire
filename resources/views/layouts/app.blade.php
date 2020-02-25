<!doctype html>
<html lang="en">

<head>
    <title>Squire - @yield("title")</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/static/bootstrap.min.css">
</head>

<body>
    <div class="container">
        @yield("content")
    </div>

    @yield("script")
</body>

</html>
