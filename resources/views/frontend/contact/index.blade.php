@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Contact us | '.$setting('store_name'))
@section('frontend_description', 'Call, email or message us. We reply to every message within one working day.')
@section('page', 'contact')

@section('frontend_contents')
    @php
        $phoneDigits = preg_replace('/\D+/', '', (string) $setting('phone'));
        $phoneLink = $phoneDigits === '' ? '' : (str_starts_with($phoneDigits, '0') ? '+88'.$phoneDigits : '+'.$phoneDigits);
        $address = trim((string) $setting('address'));
        $directions = 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($address);
        $embed = 'https://www.google.com/maps?q='.urlencode($address).'&output=embed';
    @endphp

    <div class="pg">
        <section class="ph">
            <div class="wrap ph__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">Contact us</li>
                        </ol>
                    </nav>
                    <h1>Contact us</h1>
                    <p class="ph__sub">We reply to every message within one working day.</p>
                </div>
                <div class="ph__badge" aria-hidden="true"><span class="ic ic-chat" aria-hidden="true"></span></div>
            </div>
        </section>

        <section class="sec">
            <div class="wrap">
                <div class="cinfo">
                    @if ($phoneLink)
                        <a class="card cinfo__item" href="tel:{{ $phoneLink }}"><span class="ic ic-phone" aria-hidden="true"></span>
                            <div>
                                <h3>Call us</h3>
                                <p>{{ $setting('phone') }}</p>
                            </div>
                        </a>
                    @endif
                    @if ($setting('support_email'))
                        <a class="card cinfo__item" href="mailto:{{ $setting('support_email') }}"><span class="ic ic-mail" aria-hidden="true"></span>
                            <div>
                                <h3>Email us</h3>
                                <p>{{ $setting('support_email') }}</p><small>We reply within one working day</small>
                            </div>
                        </a>
                    @endif
                    @if ($setting('opening_hours'))
                        <div class="card cinfo__item"><span class="ic ic-clock" aria-hidden="true"></span>
                            <div>
                                <h3>Opening hours</h3>
                                <p>{{ $setting('opening_hours') }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($address !== '')
                        <a class="card cinfo__item" href="{{ $directions }}" target="_blank" rel="noopener"><span class="ic ic-pin" aria-hidden="true"></span>
                            <div>
                                <h3>Visit our office</h3>
                                <p>{{ $address }}</p><small>Tap for directions</small>
                            </div>
                        </a>
                    @endif
                </div>

                <div class="cgrid">
                    <div class="card cform">
                        <h2>Send us a message</h2>
                        <p>Tell us how we can help and we will get back to you within one working day.</p>
                        <form id="contact-form" method="POST" action="{{ route('contact.store') }}" novalidate>
                            @csrf
                            <div class="two">
                                <div class="fld"><label class="lbl" for="c-name">Full name <i>*</i></label>
                                    <div class="fld__box"><span class="ic ic-user" aria-hidden="true"></span><input id="c-name" name="name" type="text" required maxlength="120" value="{{ old('name', auth()->user()?->name) }}" placeholder="Your full name" autocomplete="name"></div>
                                    @error('name')<small class="text-danger text-sm font-bold" role="alert">{{ $message }}</small>@enderror
                                </div>
                                <div class="fld"><label class="lbl" for="c-phone">Mobile number <i>*</i></label>
                                    <div class="fld__box"><span class="ic ic-phone" aria-hidden="true"></span><input id="c-phone" name="phone" type="tel" required value="{{ old('phone', auth()->user()?->phone) }}" placeholder="01XXXXXXXXX" autocomplete="tel"></div>
                                    @error('phone')<small class="text-danger text-sm font-bold" role="alert">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="fld"><label class="lbl" for="c-email">Email address <i>*</i></label>
                                <div class="fld__box"><span class="ic ic-mail" aria-hidden="true"></span><input id="c-email" name="email" type="email" required maxlength="150" value="{{ old('email', auth()->user()?->email) }}" placeholder="you@example.com" autocomplete="email"></div>
                                @error('email')<small class="text-danger text-sm font-bold" role="alert">{{ $message }}</small>@enderror
                            </div>
                            <div class="fld"><label class="lbl" for="c-topic">Topic</label>
                                <div class="fld__box"><span class="ic ic-tag" aria-hidden="true"></span><select id="c-topic" name="topic">
                                        @foreach ($topics as $topic)
                                            <option value="{{ $topic }}" @selected(old('topic') === $topic)>{{ $topic }}</option>
                                        @endforeach
                                    </select><span class="ic ic-chev sel-arrow" aria-hidden="true"></span></div>
                                @error('topic')<small class="text-danger text-sm font-bold" role="alert">{{ $message }}</small>@enderror
                            </div>
                            <div class="fld"><label class="lbl" for="c-msgtxt">Message <i>*</i></label>
                                <div class="fld__box fld__box--ta"><span class="ic ic-chat" aria-hidden="true"></span><textarea id="c-msgtxt" name="message" required minlength="10" maxlength="500" placeholder="How can we help?">{{ old('message') }}</textarea></div>
                                @error('message')<small class="text-danger text-sm font-bold" role="alert">{{ $message }}</small>@enderror
                            </div>
                            <span class="count" id="c-count">{{ mb_strlen(old('message', '')) }} / 500</span>
                            <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <button type="submit" class="btn btn--primary btn--block">Send message <span class="ic ic-send" aria-hidden="true"></span></button>
                        </form>
                    </div>

                    @if ($address !== '')
                        <div class="card cmap"><iframe title="{{ $setting('store_name') }} office location on Google Maps" src="{{ $embed }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                            <div class="cmap__bar">
                                <div><strong>{{ $setting('store_name') }} Office</strong><span>{{ $address }}</span></div>
                                <a class="btn btn--primary" href="{{ $directions }}" target="_blank" rel="noopener"><span class="ic ic-nav" aria-hidden="true"></span> Get directions</a>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="cquick"><a class="card" href="{{ route('track.show') }}"><span class="ic ic-package" aria-hidden="true"></span> Track my order <span class="ic ic-arrow" aria-hidden="true"></span></a><a class="card" href="{{ route('returns.show') }}"><span class="ic ic-return" aria-hidden="true"></span> Start a return <span class="ic ic-arrow" aria-hidden="true"></span></a><a class="card" href="{{ route('faq.index') }}"><span class="ic ic-help" aria-hidden="true"></span> Read the FAQ <span class="ic ic-arrow" aria-hidden="true"></span></a></div>
            </div>
        </section>
    </div>

    @if (session('contact_success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') return;

                Swal.fire({
                    icon: 'success',
                    title: 'Message sent!',
                    text: @json(session('contact_success')),
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#0B3A8C',
                    customClass: { popup: 'swal-contact' },
                });
            });
        </script>
    @endif
@endsection
