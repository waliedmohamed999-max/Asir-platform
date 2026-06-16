<?php

namespace App\Support;

class Code39Barcode
{
    private const MAP = [
        '0' => 'nnnwwnwnn',
        '1' => 'wnnwnnnnw',
        '2' => 'nnwwnnnnw',
        '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw',
        '5' => 'wnnwwnnnn',
        '6' => 'nnwwwnnnn',
        '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn',
        '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw',
        'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn',
        'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn',
        'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw',
        'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn',
        'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww',
        'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn',
        'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn',
        'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww',
        'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn',
        'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw',
        'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn',
        'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn',
        'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw',
        '.' => 'wwnnnnwnn',
        ' ' => 'nwwnnnwnn',
        '$' => 'nwnwnwnnn',
        '/' => 'nwnwnnnwn',
        '+' => 'nwnnnwnwn',
        '%' => 'nnnwnwnwn',
        '*' => 'nwnnwnwnn',
    ];

    public static function svg(?string $value, int $height = 64): string
    {
        $code = strtoupper(trim((string) $value));

        if ($code === '') {
            return '';
        }

        $code = '*'.$code.'*';
        $narrow = 2;
        $wide = 5;
        $gap = 2;
        $x = 0;
        $bars = [];

        foreach (str_split($code) as $character) {
            $pattern = self::MAP[$character] ?? null;

            if (! $pattern) {
                continue;
            }

            foreach (str_split($pattern) as $index => $symbol) {
                $width = $symbol === 'w' ? $wide : $narrow;

                if ($index % 2 === 0) {
                    $bars[] = sprintf(
                        '<rect x="%d" y="0" width="%d" height="%d" rx="1" fill="#0f172a"></rect>',
                        $x,
                        $width,
                        $height
                    );
                }

                $x += $width;
            }

            $x += $gap;
        }

        $svgWidth = max($x, 120);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %2$d" width="%1$d" height="%2$d" role="img" aria-label="Barcode">%3$s</svg>',
            $svgWidth,
            $height,
            implode('', $bars)
        );
    }
}
