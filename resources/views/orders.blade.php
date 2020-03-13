@extends("layouts.app")

@section("title", "Battalions")

@section("content")
<h2>Knights</h2>
<p>
    Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.<br><br>

    Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis,
</p>
@if(Auth::user()->getRankVal() <= 8)
    <h2>Officers</h2>
    <p>
        Li Europan lingues es membres del sam familie. Lor separat existentie es un myth.<br><br>

        Por scientie, musica, sport etc, litot Europa usa li sam vocabular.<br><br>

        Li lingues differe solmen in li grammatica, li pronunciation e li plu commun vocabules. Omnicos
    </p>
@endif

@if(Auth::user()->getRankVal() <= 5)
    <h2>Commanders</h2>
    <p>
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;<br><br>

        Praesent sem urna, mollis vel venenatis vel, rhoncus nec ligula. Curabitur sit amet lacus vitae tellus commodo.
    </p>
@endif
@endsection
