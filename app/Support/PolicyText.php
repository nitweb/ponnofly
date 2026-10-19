<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class PolicyText
{
    public const ICONS = [
        'lock' => 'Lock',
        'shield' => 'Shield',
        'wallet' => 'Wallet',
        'user' => 'Person',
        'check' => 'Check',
        'return' => 'Return',
        'handshake' => 'Handshake',
        'truck' => 'Truck',
        'star' => 'Star',
        'clock' => 'Clock',
        'tag' => 'Tag',
    ];

    /**
     * Turn plain text into safe HTML: blank line = new paragraph,
     * lines starting with "- " = bullet list, [text](url) = link.
     */
    public static function render(?string $text): HtmlString
    {
        $html = '';

        foreach (preg_split('/\R{2,}/', trim((string) $text)) as $block) {
            $lines = preg_split('/\R/', trim($block));

            if (collect($lines)->every(fn ($line) => str_starts_with(ltrim($line), '- '))) {
                $html .= '<ul>'.collect($lines)
                    ->map(fn ($line) => '<li>'.self::inline(substr(ltrim($line), 2)).'</li>')
                    ->implode('').'</ul>';
            } else {
                $html .= '<p>'.nl2br(self::inline(implode("\n", $lines))).'</p>';
            }
        }

        return new HtmlString($html);
    }

    private static function inline(string $line): string
    {
        return (string) preg_replace_callback(
            '/\[([^\]]+)\]\(((?:https?:\/\/|mailto:|\/)[^\s)]*)\)/',
            fn ($match) => '<a href="'.$match[2].'">'.$match[1].'</a>',
            e($line),
        );
    }
}
