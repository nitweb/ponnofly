@if ($shopCategories->isNotEmpty())
@php
    $sliderActive = $shopCategories->count() > 6;
@endphp
<section class="max-w-7xl mx-auto px-4 lg:px-6 mt-12" aria-labelledby="h-cat" aria-roledescription="carousel">

    <div class="flex items-end justify-between mb-6 gap-4">

        <h2 id="h-cat" class="font-display font-extrabold text-3xl">Shop by category</h2>

        @if ($sliderActive)
        <div class="flex gap-2">

            <button type="button" data-cat-prev class="w-10 h-10 grid place-items-center rounded-full bg-white border border-line hover:bg-accent" aria-label="Previous categories">
                <svg class="w-5 h-5 rotate-90">
                    <use href="#i-chev" />
                </svg>
            </button>

            <button type="button" data-cat-next class="w-10 h-10 grid place-items-center rounded-full bg-white border border-line hover:bg-accent" aria-label="Next categories">
                <svg class="w-5 h-5 -rotate-90">
                    <use href="#i-chev" />
                </svg>
            </button>

        </div>
        @endif

    </div>

    <ul id="cat-track" class="cat-track" @if ($sliderActive) data-loop="3500" data-loop-prev="[data-cat-prev]" data-loop-next="[data-cat-next]" @endif>

        @foreach ($shopCategories as $category)
            <li class="cat-slide">
                <a href="{{ $category->url }}" class="flex flex-col items-center gap-3 bg-white border border-line rounded-2xl p-4 hover:shadow-md hover:-translate-y-0.5 transition-all">
                    <span class="w-16 h-16 rounded-2xl grid place-items-center text-3xl font-extrabold text-primary" style="overflow: hidden" aria-hidden="true">
                        @if ($category->image)
                            <img src="{{ $category->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @else
                            {{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}
                        @endif
                    </span>
                    <span class="font-bold text-sm text-center">{{ $category->name }}</span>
                </a>
            </li>
        @endforeach

    </ul>

</section>

<style>
    .cat-track {
        display: flex;
        gap: .75rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        scrollbar-width: none;
        padding: 2px 2px 6px
    }

    .cat-track::-webkit-scrollbar {
        display: none
    }

    .cat-slide {
        flex: 0 0 calc((100% - 1.5rem)/3);
        scroll-snap-align: start
    }

    @media(min-width:640px) {
        .cat-track {
            gap: 1rem
        }

        .cat-slide {
            flex-basis: calc((100% - 3rem)/4)
        }
    }

    @media(min-width:768px) {
        .cat-slide {
            flex-basis: calc((100% - 5rem)/6)
        }
    }
</style>
@endif
