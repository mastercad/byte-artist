<?php

namespace App\Service\Handler;

use App\Entity\User;
use App\Service\Generator\Path;
use Symfony\Component\HttpFoundation\File\File;

class UploadedFiles
{
  /** string absolute path to project root */
  private Path $pathGenerator;

  private User $user;

  public function __construct(User $user, Path $pathGenerator)
  {
    $this->user = $user;
    $this->pathGenerator = $pathGenerator;
  }

  public function handle($entity)
  {
    $publicUploadPath = $this->pathGenerator->generatePublicUploadPath($this->user);
    $absoluteUploadPath = $this->pathGenerator->generateAbsolutePublicUploadPath($this->user);
    $targetAbsolutePublicPath = $this->pathGenerator->generateAbsolutePublicImagesPath($entity);
    $targetPublicPath = $this->pathGenerator->generatePublicImagesPath($entity);

    // Match images in description
    if (!empty($entity->getDescription())
        && preg_match_all('#\<img.*?src="(?:http[s]*:\/\/[0-9\.a-z:]+)*('.preg_quote($publicUploadPath).'\/([^"]+\.[a-z]+))".*?\/>#i', $entity->getDescription(), $matches)
    ) {
        foreach ($matches[2] as $fileName) {
            $absoluteFilePath = $absoluteUploadPath.'/'.$fileName;

            if (!file_exists($absoluteFilePath)) {
                continue;
            }

            $file = new File($absoluteFilePath);
            if ($file->isReadable()) {
                $file->move($targetAbsolutePublicPath, $file->getFilename());
                $replacedDescription = str_replace($publicUploadPath.'/'.$fileName, $targetPublicPath.'/'.$fileName, $entity->getDescription());
                $entity->setDescription($replacedDescription);
            }
        }
    }

    // @TODO ADJUST THIS BEHAVIOUR, ALSO THE PREV PIC SHOULD HANDLED LIKE THE OTHER IMAGES IN UPLOAD!
    $previewFilePath = $this->pathGenerator->getProjectRootPath().'/public'.$entity->getPreviewPicture();
    if (file_exists($previewFilePath)
      && is_file($previewFilePath)
    ) {
        $file = new File($previewFilePath);
        $targetAbsolutePublicPath = $this->pathGenerator->generateAbsolutePublicImagesPath($entity);
        $file->move($targetAbsolutePublicPath, $file->getFilename());
        $entity->setPreviewPicture($targetPublicPath.'/'. $file->getFilename());
    }

    return $this;
  }
}
