<?php

namespace App\Service\Generator;

use App\Entity\Projects;
use App\Entity\User;
use App\Service\Extractor\ClassName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
  public function testGeneratePublicUploadPath()
  {
    /** @var ClassName */
    $classNameExtractorMock = $this->createMock(ClassName::class);

    /** @var User|MockObject */
    $userMock = $this->createMock(User::class);
    $userMock->method('getId')->willReturn(12);

    $pathGenerator = new Path('/test/folder/for/test', $classNameExtractorMock);
    $this->assertSame('/upload/12', $pathGenerator->generatePublicUploadPath($userMock));
  }

  public function testGenerateAbsolutePublicUploadPath()
  {
    /** @var ClassName */
    $classNameExtractorMock = $this->createMock(ClassName::class);

    /** @var User|MockObject */
    $userMock = $this->createMock(User::class);
    $userMock->method('getId')->willReturn(12);

    $pathGenerator = new Path('/test/folder/for/test', $classNameExtractorMock);
    $this->assertSame('/test/folder/for/test/public/upload/12', $pathGenerator->generateAbsolutePublicUploadPath($userMock));
  }

  public function testGeneratePublicImagesPath()
  {
    /** @var ClassName */
    $classNameExtractorMock = $this->createMock(ClassName::class);
    $classNameExtractorMock->method('extractClassName')->willReturn('Projects');

    /** @var User|MockObject */
    $projectsMock = $this->createMock(Projects::class);
    $projectsMock->method('getId')->willReturn(5);

    $pathGenerator = new Path('/test/folder/for/test', $classNameExtractorMock);
    $this->assertSame('/images/content/dynamisch/projects/5', $pathGenerator->generatePublicImagesPath($projectsMock));
  }

  public function testGenerateAbsolutePublicImagesPath()
  {
    $entity = new Projects();
    $entity->setId(131);

    /** @var ClassName|MockObject */
    $classNameExtractorMock = $this->createMock(ClassName::class);
    $classNameExtractorMock->method('extractClassName')->willReturn('Projects');

    $pathGenerator = new Path('/test/new/folder', $classNameExtractorMock);
    $this->assertSame('/test/new/folder/public/images/content/dynamisch/projects/131', $pathGenerator->generateAbsolutePublicImagesPath($entity));
  }

  public function testGenerateAbsolutePublicImagesPathWithoutMock()
  {
    $entity = new Projects();
    $entity->setId(131);

    /** @var ClassName */
    $classNameExtractor = new ClassName();

    $pathGenerator = new Path('/test/new/folder', $classNameExtractor);
    $this->assertSame('/test/new/folder/public/images/content/dynamisch/projects/131', $pathGenerator->generateAbsolutePublicImagesPath($entity));
  }
}
