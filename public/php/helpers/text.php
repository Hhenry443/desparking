<?php

// Escapes $text for HTML output, then turns any http(s):// or www. URLs into clickable links.
function autolink(string $text): string
{
    $pattern = '/((?:https?:\/\/[^\s<>"]+)|(?:www\.[^\s<>"]+))/i';
    $parts   = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out     = '';

    foreach ($parts as $i => $part) {
        if ($i % 2 === 0) {
            $out .= htmlspecialchars($part, ENT_QUOTES);
            continue;
        }

        $trailing = '';
        if (preg_match('/^(.*?)([.,:;!?\)\]]+)$/', $part, $m)) {
            $part     = $m[1];
            $trailing = $m[2];
        }

        $href = stripos($part, 'http') === 0 ? $part : 'http://' . $part;

        $out .= '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" target="_blank" rel="noopener noreferrer nofollow" class="underline text-[#060745] hover:no-underline">'
              . htmlspecialchars($part, ENT_QUOTES) . '</a>' . htmlspecialchars($trailing, ENT_QUOTES);
    }

    return $out;
}
