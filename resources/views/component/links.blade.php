<ul class="links list-unstyled ml-3">
@php /** @var \App\Model\Link $link */ @endphp
@foreach ($links as $link)
    <li class="media">
        @if ($link->imgurl)
            <div class="link-img align-self-start mr-3">
                <img class="img-fluid" src="{{ $link->imgurl }}" alt=" ">
            </div>
        @endif
        <div class="media-body">
            <h5 class="mt-0 mb-0"><a href="{{ $link->linkurl }}" target="_blank" rel="noopener">{{ $link->linknm }}</a></h5>
            <p class="ml-3">{{ $link->linkdesc }}</p>
        </div>
    </li>
@endforeach
</ul>
