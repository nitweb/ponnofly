@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Returns & refunds | '.$setting('store_name'))
@section('frontend_description', $page->hero_subtitle)
@section('page', 'returns')

@section('frontend_contents')
    @php($fill = fn (?string $text) => \App\Models\ReturnsPage::replacePlaceholders($text))

    <div class="pg">
        <section class="ph">
            <div class="wrap ph__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">Returns &amp; refunds</li>
                        </ol>
                    </nav>
                    <h1>{{ $page->hero_title }}</h1>
                    <p class="ph__sub">{{ $page->hero_subtitle }}</p>
                </div>
                <div class="ph__badge" aria-hidden="true"><span class="ic ic-return" aria-hidden="true"></span></div>
            </div>
        </section>

        <section class="sec">
            <div class="wrap">
                <div class="win">
                    <div class="win__big"><span class="win__num">{{ $setting('return_days') }}</span><span class="win__unit">day returns</span>
                        <p class="win__cap">{{ $page->win_caption }}</p>
                    </div>
                    <div class="win__body">
                        <h2>{{ $fill($page->win_title) }}</h2>
                        <p>{{ $fill($page->win_text) }}</p>
                        @if (! empty($page->win_needs))
                            <ul class="need">
                                @foreach ($page->win_needs as $need)
                                    <li><span class="ic ic-check" aria-hidden="true"></span> {{ $need }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @if (! empty($page->steps))
            <section class="sec sec--tint">
                <div class="wrap">
                    <div class="sec__head">
                        <h2>{{ $page->steps_title }}</h2>
                        <p>{{ $page->steps_text }}</p>
                    </div>
                    <ol class="steps3">
                        @foreach ($page->steps as $step)
                            <li>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $fill($step['text']) }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        <section class="sec">
            <div class="wrap">
                <div class="ret2">
                    <div class="card nope">
                        <h3>{{ $page->nope_title }}</h3>
                        <p>{{ $page->nope_text }}</p>
                        @if (! empty($page->nope_items))
                            <ul>
                                @foreach ($page->nope_items as $item)
                                    <li><span class="ic ic-x" aria-hidden="true"></span> {{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="card helpbox"><span class="ic ic-chat" aria-hidden="true"></span>
                        <h3>{{ $page->help_title }}</h3>
                        <p>{{ $page->help_text }}</p><a href="{{ route('contact.show') }}" class="btn btn--primary">Contact us</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
