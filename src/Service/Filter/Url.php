<?php

namespace App\Service\Filter;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Url
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function filter($string)
    {
        if ('/' == substr($string, 0, 1)) {
            if ('/' != substr($string, -1)) {
                $string .= '/';
            }

            return $string;
        } elseif ('#' == trim($string)) {
            return '#';
        } else {
            /*
            return $this->urlGenerator->generate(
                [
                    'module' => 'default',
                    'controller' => 'index',
                    'action' => 'show',
                    'name' => urlencode($string),
                ],
                null,
                true
            ).'/';
            */
            return '/show/'.urlencode($string);
        }
    }
}
