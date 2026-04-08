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
