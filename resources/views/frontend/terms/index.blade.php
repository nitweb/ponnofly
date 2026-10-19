@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Terms of service | '.$setting('store_name'))
@section('frontend_description', $page->hero_subtitle)
@section('page', 'info')

@section('frontend_contents')
    @php
        $clauses = collect($page->clauses)->map(fn ($clause, $index) => $clause + ['id' => (\Illuminate\Support\Str::slug($clause['title']) ?: 'section').'-'.($index + 1)]);
    @endphp

    <div class="pg">
        <section class="ph ph--tight">
            <div class="wrap ph__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">Terms of service</li>
                        </ol>
                    </nav>
                    <h1>{{ $page->hero_title }}</h1>
                    <p class="ph__sub">{{ $page->hero_subtitle }}</p>
                    <div class="meta-row">
                        @if ($page->last_updated)
                            <span><span class="ic ic-clock" aria-hidden="true"></span>Last updated: {{ $page->last_updated->format('j F Y') }}</span>
                        @endif
                        <span><span class="ic ic-check" aria-hidden="true"></span>Plain language</span>
                    </div>
                </div>
                <div class="ph__badge" aria-hidden="true"><span class="ic ic-handshake" aria-hidden="true"></span></div>
            </div>
        </section>

        <section class="sec" style="padding-top:0">
            <div class="wrap">
                @if (! empty($page->keys))
                    <div class="keys">
                        @foreach ($page->keys as $key)
                            <div class="key"><span class="key__ic"><span class="ic ic-{{ $key['icon'] }}" aria-hidden="true"></span></span>
                                <div>
                                    <h3>{{ $key['title'] }}</h3>
                                    <p>{{ $key['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="lg" style="margin-top:2.5rem">
                    <aside class="toc" aria-label="On this page">
                        <h2>On this page</h2>
                        <ol>
                            @foreach ($clauses as $clause)
                                <li><a href="#{{ $clause['id'] }}">{{ $clause['title'] }}</a></li>
                            @endforeach
                        </ol>
                        <div class="toc__bar" aria-hidden="true"><i></i></div>
                    </aside>
                    <div class="doc">
                        @foreach ($clauses as $clause)
                            <article class="clause" id="{{ $clause['id'] }}">
                                <h2>{{ $clause['title'] }}</h2>
                                {{ \App\Support\PolicyText::render($clause['body']) }}
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="sec" style="padding-top:0">
            <div class="wrap">
                <div class="cta">
                    <div>
                        <h2>{{ $page->cta_title }}</h2>
                        <p>{{ $page->cta_text }}</p>
                    </div>
                    <div class="cta__act"><a href="{{ route('contact.show') }}" class="btn btn--accent">Contact us <span class="ic ic-arrow" aria-hidden="true"></span></a><a href="{{ route('faq.index') }}" class="btn btn--line">Read FAQ</a></div>
                </div>
            </div>
        </section>
    </div>
@endsection
