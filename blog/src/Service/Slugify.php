<?php


namespace App\Service;


class Slugify
{
    public function generate(string $input): string
    {
        $specialCharacters = [ 'à', 'â', 'é', 'è', ' ê', 'ë', 'ç', 'ù', 'ö', 'û', '*', '?', '!', '.', ',', ] ;
        $newCharacters = [ 'a', 'a', 'e', 'e', 'e', 'e', 'c', 'u', 'o', 'u', '', '', '', '', '', '' ];

        $input = trim( (strtolower((str_replace($specialCharacters, $newCharacters, $input)))));
        return $input;
    }

}