<?php

namespace App\Service\Extractor;

class ClassName
{
    public function extractClassName(object $object): string
    {
        return $this->extractClassNameFromFqdn(get_class($object));
    }

    public function extractClassNameFromFqdn(string $fqdn): string
    {
        if (preg_match('/([^\\\]+)$/i', $fqdn, $matches)) {
            return $matches[1];
        }

        return $fqdn;
    }
}
