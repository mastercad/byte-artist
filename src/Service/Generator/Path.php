<?php

namespace App\Service\Generator;

use App\Entity\User;
use App\Service\Extractor\ClassName;

class Path
{
    private string $projectRootPath;

    private ClassName $classNameExtractor;

    public function __construct(string $projectRootPath, ClassName $classNameExtractor)
    {
        $this->projectRootPath = $projectRootPath;
        $this->classNameExtractor = $classNameExtractor;
    }

    public function generatePublicUploadPath(User $user): string
    {
        return '/upload/'.$user->getId();
    }

    public function generateAbsolutePublicUploadPath(User $user): string
    {
        return $this->getProjectRootPath().'/public'.$this->generatePublicUploadPath($user);
    }

    public function generatePublicImagesPath($entity): string
    {
        $entityType = $this->classNameExtractor->extractClassName($entity);

        return '/images/content/dynamisch/'.strtolower($entityType).'/'.$entity->getId();
    }

    public function generateAbsolutePublicImagesPath($entity): string
    {
        return $this->getProjectRootPath().'/public'.$this->generatePublicImagesPath($entity);
    }

    public function getProjectRootPath(): string
    {
        return $this->projectRootPath;
    }
}
