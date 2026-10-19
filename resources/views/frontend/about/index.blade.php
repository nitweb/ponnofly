@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'About us | '.$setting('store_name'))
@section('frontend_description', $about->hero_subtitle)
@section('page', 'about')

@section('frontend_contents')
    @php
        $paragraphs = preg_split('/\R{2,}/', trim($about->story_text));
    @endphp

    <div class="pg">
        <section class="abt-hero">
            <div class="wrap abt-hero__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">About us</li>
                        </ol>
                    </nav>
                    <h1>{{ $about->hero_title }}</h1>
                    <p class="abt-hero__sub">{{ $about->hero_subtitle }}</p>
                    <div class="abt-hero__act"><a href="{{ route('shop') }}" class="btn btn--accent">Start shopping <span class="ic ic-arrow" aria-hidden="true"></span></a></div>
                </div>
                <figure class="abt-photo"><img src="{{ $about->imageUrl($about->hero_image) }}" alt="{{ $about->hero_caption ?: $about->hero_title }}" width="1000" height="800" fetchpriority="high">
                    @if ($about->hero_caption)
                        <figcaption><span class="ic ic-pin" aria-hidden="true"></span> {{ $about->hero_caption }}</figcaption>
                    @endif
                </figure>
            </div>
        </section>

        @if (! empty($about->stats))
            <div class="wrap">
                <div class="stats">
                    @foreach ($about->stats as $stat)
                        <div class="stat"><b data-to="{{ $stat['value'] }}" data-dec="0" data-suf="{{ $stat['suffix'] }}">{{ number_format($stat['value']) }}{{ $stat['suffix'] }}</b><span>{{ $stat['label'] }}</span></div>
                    @endforeach
                </div>
            </div>
        @endif

        <section class="sec">
            <div class="wrap story">
                <div class="story__img"><img src="{{ $about->imageUrl($about->story_image) }}" alt="{{ $about->story_title }}" width="900" height="1100" loading="lazy">
                    @if ($about->story_tag_number || $about->story_tag_text)
                        <div class="tag"><b>{{ $about->story_tag_number }}</b>{{ $about->story_tag_text }}</div>
                    @endif
                </div>
                <div>
                    <h2>{{ $about->story_title }}</h2>
                    @foreach ($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    @if (! empty($about->story_points))
                        <ul>
                            @foreach ($about->story_points as $point)
                                <li><span class="ic ic-check" aria-hidden="true"></span> {{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        <section class="sec sec--tint">
            <div class="wrap">
                <div class="mv">
                    <article class="mv__card mv__card--m"><span class="ic ic-target" aria-hidden="true"></span>
                        <h3>Our mission</h3>
                        <p>{{ $about->mission_text }}</p>
                    </article>
                    <article class="mv__card mv__card--v"><span class="ic ic-eye" aria-hidden="true"></span>
                        <h3>Our vision</h3>
                        <p>{{ $about->vision_text }}</p>
                    </article>
                </div>
            </div>
        </section>

        @if (! empty($about->values))
            <section class="sec">
                <div class="wrap">
                    <div class="sec__head">
                        <h2>{{ $about->values_title }}</h2>
                        @if ($about->values_subtitle)<p>{{ $about->values_subtitle }}</p>@endif
                    </div>
                    <div class="vals">
                        @foreach ($about->values as $value)
                            <article class="card val"><span class="ic ic-{{ $value['icon'] }}" aria-hidden="true"></span>
                                <h3>{{ $value['title'] }}</h3>
                                <p>{{ $value['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if (! empty($about->steps))
            <section class="sec sec--dark">
                <div class="wrap">
                    <div class="sec__head">
                        <h2>{{ $about->process_title }}</h2>
                        @if ($about->process_subtitle)<p>{{ $about->process_subtitle }}</p>@endif
                    </div>
                    <ol class="how">
                        @foreach ($about->steps as $step)
                            <li>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        @if (collect($about->gallery)->contains(fn ($slot) => ! empty($slot['image'])))
            <section class="sec">
                <div class="wrap">
                    <div class="sec__head">
                        <h2>{{ $about->gallery_title }}</h2>
                    </div>
                    <div class="gal">
                        @foreach ($about->gallery as $slot)
                            @if (! empty($slot['image']))
                                <figure><img src="{{ $about->imageUrl($slot['image']) }}" alt="{{ $slot['alt'] ?? '' }}" width="{{ $loop->first ? 900 : 600 }}" height="600" loading="lazy"></figure>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="sec">
            <div class="wrap">
                <div class="cta">
                    <div>
                        <h2>{{ $about->cta_title }}</h2>
                        <p>{{ $about->cta_text }}</p>
                    </div>
                    <div class="cta__act"><a href="{{ route('shop') }}" class="btn btn--accent">Shop now <span class="ic ic-arrow" aria-hidden="true"></span></a></div>
                </div>
            </div>
        </section>
    </div>
@endsection
