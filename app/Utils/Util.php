<?php

namespace App\Utils;

class Util
{
    public static function getAbjad(Int $index): string
    {
        $abjad = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        ];

        return $abjad[$index];
    }
    public static function countDuplicate($array, $key): array
    {
        $hasil = [];

        foreach ($array as $item) {
            if (isset($item[$key])) {
                $hasil[$item[$key]] = ($hasil[$item[$key]] ?? 0) + 1;
            }
        }

        $formattedResult = [];
        foreach ($hasil as $answer_text => $count) {
            $formattedResult[] = [
                'answer_text' => $answer_text,
                'count' => $count
            ];
        }

        return $formattedResult;
    }
}
