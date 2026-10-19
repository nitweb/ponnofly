@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Shipping info | '.$setting('store_name'))
@section('frontend_description', $page->hero_subtitle)
@section('page', 'shipping')

@section('frontend_contents')
    <div class="pg">
        <section class="ph">
            <div class="wrap ph__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">Shipping info</li>
                        </ol>
                    </nav>
                    <h1>{{ $page->hero_title }}</h1>
                    <p class="ph__sub">{{ $page->hero_subtitle }}</p>
                </div>
                <div class="ph__badge" aria-hidden="true"><span class="ic ic-truck" aria-hidden="true"></span></div>
            </div>
        </section>

        <section class="sec sec--tint">
            <div class="wrap">
                <div class="sec__head">
                    <h2>{{ $page->zones_title }}</h2>
                    <p>{{ $page->zones_text }}</p>
                </div>
                <ol class="zones">
                    @foreach ($zones as $zone)
                        <li class="zone"><span class="zone__node"><span class="ic ic-{{ $zone['icon'] }}" aria-hidden="true"></span></span>
                            <div class="card zone__card">
                                <h3>{{ $zone['label'] }}</h3>
                                @if (filled($zone['areas']))
                                    <p class="zone__areas">{{ $zone['areas'] }}</p>
                                @endif
                                <p class="zone__fee"><small>৳</small>{{ number_format($zone['fee']) }}</p>
                                @if (filled($zone['time']))
                                    <p class="zone__eta"><span class="ic ic-clock" aria-hidden="true"></span>{{ $zone['time'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        @if (! empty($page->know_items))
            <section class="sec">
                <div class="wrap">
                    <div class="sec__head">
                        <h2>{{ $page->know_title }}</h2>
                    </div>
                    <div class="know">
                        @foreach ($page->know_items as $item)
                            <article class="card know__item"><span class="ic ic-{{ $item['icon'] }}" aria-hidden="true"></span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if (! empty($page->flow_steps))
            <section class="sec sec--dark">
                <div class="wrap">
                    <div class="sec__head">
                        <h2>{{ $page->flow_title }}</h2>
                        <p>{{ $page->flow_text }}</p>
                    </div>
                    <ol class="flow">
                        @foreach ($page->flow_steps as $step)
                            <li>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        <section class="sec">
            <div class="wrap">
                <div class="cta">
                    <div>
                        <h2>{{ $page->cta_title }}</h2>
                        <p>{{ $page->cta_text }}</p>
                    </div>
                    <div class="cta__act"><a href="{{ route('track.show') }}" class="btn btn--accent">Track order <span class="ic ic-arrow" aria-hidden="true"></span></a><a href="{{ route('contact.show') }}" class="btn btn--line">Contact us</a></div>
                </div>
            </div>
        </section>
    </div>
@endsection
