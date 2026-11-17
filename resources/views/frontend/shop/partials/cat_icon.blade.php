@php
    $n = mb_strtolower($name ?? '');
    $icons = [
        'medical|health|pharma|medicine' => '<path d="M12 5v14M5 12h14"/><rect x="3" y="3" width="18" height="18" rx="5"/>',
        'auto|\bcars?\b|bike|vehicle|motor' => '<path d="M5 16V11l2-5h10l2 5v5"/><path d="M3 16h18v3H3z"/><circle cx="7.5" cy="19" r="1"/><circle cx="16.5" cy="19" r="1"/>',
        'camera|photo|video|lens' => '<path d="M4 8h3l1.5-2h7L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="3.5"/>',
        'clean|wash|laundry|detergent|household' => '<path d="M9 3h6v4H9z"/><path d="M8 7h8l1 4v9a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-9Z"/><path d="M10 14h4"/>',
        'brush|tooth|dental|oral|hygiene|shav' => '<path d="m15 4 5 5-9.5 9.5a2.8 2.8 0 0 1-4-4Z"/><path d="M13 6 18 11M9 15l-2 2"/>',
        'fashion|cloth|wear|apparel|shirt|dress' => '<path d="M8 3 3 6l2 4 3-1v12h8V9l3 1 2-4-5-3a4 4 0 0 1-8 0Z"/>',
        'electron|gadget|phone|mobile|computer|laptop|tech' => '<rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/>',
        'home|living|furniture|kitchen|decor|house' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
        'beauty|cosmetic|skin|makeup|care' => '<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8Z"/>',
        'grocery|food|fruit|vegetable|snack|drink' => '<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/><circle cx="10" cy="20" r="1.2"/><circle cx="17" cy="20" r="1.2"/>',
        'sport|fitness|gym|outdoor' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'baby|kid|child|toy' => '<circle cx="12" cy="10" r="6"/><path d="M9.5 9.5h.01M14.5 9.5h.01M10 12.5c1.2 1 2.8 1 4 0"/><path d="M8 18.5c1.2 1 2.4 1.5 4 1.5s2.8-.5 4-1.5"/>',
        'book|stationery|office|education' => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2Z"/><path d="M4 19V5"/>',
        'jewel|watch|accessor|bag' => '<circle cx="12" cy="13" r="6"/><path d="M9 3h6l-1 4h-4Z"/>',
        'pet|animal' => '<circle cx="6" cy="10" r="2"/><circle cx="18" cy="10" r="2"/><circle cx="10" cy="5.5" r="2"/><circle cx="14" cy="5.5" r="2"/><path d="M12 11c-3.5 0-6 3-6 5.5 0 2 1.7 2.5 3 2.5 1 0 1.8-.5 3-.5s2 .5 3 .5c1.3 0 3-.5 3-2.5 0-2.5-2.5-5.5-6-5.5Z"/>',
    ];
    $path = '<path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9Z"/><circle cx="7.5" cy="7.5" r="1.2"/>';
    foreach ($icons as $keys => $d) {
        if (preg_match('/'.$keys.'/u', $n)) { $path = $d; break; }
    }
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $path !!}</svg>
