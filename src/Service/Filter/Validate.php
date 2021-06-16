<?php

namespace App\Service\Filter;

class Validate
{
    public function filter($string): ?string
    {
        return preg_replace('/&[^amp;]/Ui', '&amp;', $string);
    }
}
