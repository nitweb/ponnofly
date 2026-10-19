@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'FAQ | '.$setting('store_name'))
@section('frontend_description', 'Quick answers to the questions we hear most about delivery, payments, returns and orders.')
@section('page', 'faq')

@section('frontend_contents')
    <div class="pg">
        <section class="ph">
            <div class="wrap ph__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">FAQ</li>
                        </ol>
                    </nav>
                    <h1>FAQ</h1>
                    <p class="ph__sub">Quick answers to the questions we hear most.</p>
                </div>
                <div class="ph__badge" aria-hidden="true"><span class="ic ic-help" aria-hidden="true"></span></div>
            </div>
        </section>

        <section class="sec">
            <div class="wrap">
                <div class="faq-tools">
                    <div class="fld"><label class="lbl" for="faq-q">Search questions</label>
                        <div class="fld__box"><span class="ic ic-search" aria-hidden="true"></span><input id="faq-q" type="search" placeholder="Type a word, like refund or delivery" autocomplete="off"></div>
                    </div>
                    <div class="faq-chips" role="group" aria-label="Filter by topic">
                        <button type="button" class="chip" data-cat="all" aria-pressed="true">All</button>
                        @foreach ($categories as $key => $label)
                            <button type="button" class="chip" data-cat="{{ $key }}" aria-pressed="false">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="faq-layout">
                    <div>
                        <div class="faq-meta"><span id="faq-count" aria-live="polite"></span><button type="button" id="faq-toggle">Expand all</button></div>
                        <div class="faq-list">
                            @foreach ($faqs as $faq)
                                <details class="qa" data-faq="{{ $faq->category }}">
                                    <summary>{{ $faq->question }}<span class="ic ic-chev" aria-hidden="true"></span></summary>
                                    <div class="qa__a">{!! nl2br(e($faq->answer)) !!}</div>
                                </details>
                            @endforeach
                        </div>
                        <div class="card faq-empty" id="faq-empty" hidden>
                            <h3>No questions match your search</h3>
                            <p>Try a different word, or ask us directly.</p>
                            <button type="button" class="btn btn--ghost" id="faq-reset">Clear search</button>
                            <a href="{{ route('contact.show') }}" class="btn btn--primary">Contact us</a>
                        </div>
                    </div>

                    <aside class="faq-aside">
                        <div class="card dark"><span class="ic ic-chat" aria-hidden="true"></span>
                            <h3>Still stuck?</h3>
                            <p>Our team replies to every message within one working day.</p>
                            <a href="{{ route('contact.show') }}" class="btn btn--accent">Contact us</a>
                        </div>
                        <div class="card quick">
                            <h3>Quick links</h3>
                            <a href="{{ route('track.show') }}"><span class="ic ic-package" aria-hidden="true"></span> Track order</a>
                            <a href="{{ route('shipping.show') }}"><span class="ic ic-truck" aria-hidden="true"></span> Shipping info</a>
                            <a href="{{ route('returns.show') }}"><span class="ic ic-return" aria-hidden="true"></span> Returns &amp; refunds</a>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>
@endsection
