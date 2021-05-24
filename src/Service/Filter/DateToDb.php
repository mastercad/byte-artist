<?php

namespace App\Service\Filter;

class DateToDb
{

    public function filter($datum)
    {
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/', $datum, $matches)) {
            return date("Y-m-d", strtotime($matches[3] . "-" . $matches[2] . "-" . $matches[1]));
        } elseif (preg_match('/^(\d{1,2})\.(\d{2,4})$/', $datum, $matches)) {
            return date("Y-m-d", strtotime($matches[2] . "-" . $matches[1] . "-01"));
        } elseif (preg_match('/^(\d{2,4})\-(\d{1,2})\-(\d{1,2})$/', $datum, $matches)) {
            return date("Y-m-d", strtotime($matches[1] . "-" . $matches[2] . "-" . $matches[3]));
        }

        return false;
    }
}
