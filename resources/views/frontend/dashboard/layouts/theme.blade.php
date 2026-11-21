@php
    $rgb = function (string $hex): array {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    };
    $mix = fn(array $from, array $to, float $amount): string => implode(' ', array_map(fn(int $a, int $b) => (int) round($a + ($b - $a) * $amount), $from, $to));
    $white = [255, 255, 255];
    $black = [0, 0, 0];

    $primary = $rgb($setting('color_primary'));
    $accent = $rgb($setting('color_accent'));

    // Dark mode palette (used when the visitor's device is set to dark)
    $darkPaper = [14, 20, 35];
    $darkSurface = [23, 32, 52];
    $darkPrimary = array_map('intval', explode(' ', $mix($primary, $white, 0.35)));
    $fontStacks = [
        'Plus Jakarta Sans' => "'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif",
        'Inter' => "'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif",
    ];
@endphp
<style>
    :root {
        --color-primary: {{ implode(' ', $primary) }};
        --color-primary-dark: {{ $mix($primary, $black, 0.25) }};
        --color-primary-soft: {{ $mix($primary, $white, 0.94) }};
        --color-primary-100: {{ $mix($primary, $white, 0.86) }};
        --color-primary-200: {{ $mix($primary, $white, 0.72) }};
        --color-primary-300: {{ $mix($primary, $white, 0.49) }};
        --color-accent: {{ implode(' ', $accent) }};
        --color-accent-dark: {{ $mix($accent, $black, 0.115) }};
        --color-accent-soft: {{ $mix($accent, $white, 0.87) }};
        --color-star: {{ implode(' ', $accent) }};
        --color-ink: {{ implode(' ', $rgb($setting('color_text'))) }};
        --color-paper: {{ implode(' ', $rgb($setting('color_background'))) }};
        --font-display: {!! $fontStacks[$setting('font_heading')] ?? $fontStacks['Plus Jakarta Sans'] !!};
        --font-body: {!! $fontStacks[$setting('font_body')] ?? $fontStacks['Inter'] !!};
    }

    @media (prefers-color-scheme: dark) {
        :root:not([data-theme="light"]) {
            --color-primary: {{ implode(' ', $darkPrimary) }};
            --color-primary-dark: {{ $mix($primary, $white, 0.2) }};
            --color-primary-soft: {{ $mix($darkPaper, $darkPrimary, 0.16) }};
            --color-primary-100: {{ $mix($darkSurface, $darkPrimary, 0.24) }};
            --color-primary-200: {{ $mix($darkSurface, $darkPrimary, 0.4) }};
            --color-primary-300: {{ $mix($darkSurface, $darkPrimary, 0.58) }};
            --color-accent-dark: {{ $mix($accent, $white, 0.12) }};
            --color-accent-soft: {{ $mix($darkSurface, $accent, 0.16) }};
            --color-ink: 235 240 248;
            --color-paper: {{ implode(' ', $darkPaper) }};
            --color-line: 48 61 90;
            --color-surface: {{ implode(' ', $darkSurface) }};
            --color-surface-2: 33 44 70;
            --color-ink-deep: 9 14 26;
        }
    }

    :root[data-theme="dark"] {
        --color-primary: {{ implode(' ', $darkPrimary) }};
        --color-primary-dark: {{ $mix($primary, $white, 0.2) }};
        --color-primary-soft: {{ $mix($darkPaper, $darkPrimary, 0.16) }};
        --color-primary-100: {{ $mix($darkSurface, $darkPrimary, 0.24) }};
        --color-primary-200: {{ $mix($darkSurface, $darkPrimary, 0.4) }};
        --color-primary-300: {{ $mix($darkSurface, $darkPrimary, 0.58) }};
        --color-accent-dark: {{ $mix($accent, $white, 0.12) }};
        --color-accent-soft: {{ $mix($darkSurface, $accent, 0.16) }};
        --color-ink: 235 240 248;
        --color-paper: {{ implode(' ', $darkPaper) }};
        --color-line: 48 61 90;
        --color-surface: {{ implode(' ', $darkSurface) }};
        --color-surface-2: 33 44 70;
        --color-ink-deep: 9 14 26;
    }
</style>
