<ul class="links list-unstyled ml-3">
@foreach ($links as $link)
    <li class="media">
        @if ($link->imgurl)
            <img class="link-img align-self-start mr-3" src="{{ $link->imgurl }}" alt=" ">
        @endif
        <div class="media-body">
            <h5 class="mt-0 mb-0"><a href="{{ $link->linkurl }}">{{ $link->linknm }}</a></h5>
            <p class="ml-3">{{ $link->linkdesc }}</p>
        </div>
    </li>
@endforeach
</ul>
