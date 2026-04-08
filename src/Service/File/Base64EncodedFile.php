<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Replacement for hshn/base64-encoded-file (incompatible with Symfony 7).
 * Accepts a base64-encoded data string and exposes it as a Symfony File.
 */
class Base64EncodedFile extends File
{
    public function __construct(string $base64Data)
    {
        // Strip data URI prefix (e.g. "data:image/png;base64,") if present
        if (str_contains($base64Data, ';base64,')) {
            $base64Data = substr($base64Data, strpos($base64Data, ';base64,') + 8);
        }

        $decoded = base64_decode($base64Data, true);

        if (false === $decoded) {
            throw new \InvalidArgumentException('Invalid base64 data provided.');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'b64file_');

        if (false === $tempPath) {
            throw new \RuntimeException('Could not create temporary file.');
        }

        file_put_contents($tempPath, $decoded);

        parent::__construct($tempPath, true);
    }
}
