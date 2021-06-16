<?php

namespace App\Twig\Extension;

use App\Service\Filter\Ubb\Replace;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UppReplaceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('ubb', [$this, 'replace'], ['is_safe' => ['html']]),
        ];
    }

    public function replace($content, $id): string
    {
        $ubbReplacer = new Replace(true);
        $ubbReplacer->setBilderPfad('/images/content/dynamisch/blogs/'.$id.'/');

        return $ubbReplacer->filter($content);
    }
}
