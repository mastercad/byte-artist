<?php

namespace App\Service\Util;

class Strings
{
    public static function makeStringLinkSave(string $content): ?string
    {
        $content = strtolower($content);

        $content = preg_replace('/[^A-Za-z0-9]/i', '-', $content);
        $content = preg_replace('/\-{2,}+/', '-', $content);
        $content = preg_replace('/^\-/', '', $content);
        $content = preg_replace('/\-$/', '', $content);

        return $content;
    }

    public static function replaceSpecialCharacters(string $content): ?string
    {
        $search = [
        '/ä/',
        '/ü/',
        '/ö/',
        '/ß/',
        '/Ü/',
        '/Ä/',
        '/Ö/',
        ];

        $replaces = [
        'ae',
        'ue',
        'oe',
        'ss',
        'ue',
        'ae',
        'oe',
        ];

        return preg_replace($search, $replaces, $content);
    }

    /**
     * Convert all separator founds with followed single character with uppercase single character.
     *
     * @return string
     */
    public static function convertToCamelCase(string $content, string $separator = '_')
    {
        return preg_replace_callback(
            '/('.preg_quote($separator).')+([a-z])+?/i',
            function ($match) {
                return ucfirst($match[2]);
            },
            $content
        );
    }

    public static function convertFromCamelCase(string $content, string $separator = '_'): ?string
    {
        return preg_replace_callback(
            '/([A-Z])/',
            function ($match) use ($separator) {
                return $separator.lcfirst($match[1]);
            },
            lcfirst($content)
        );
    }
}
