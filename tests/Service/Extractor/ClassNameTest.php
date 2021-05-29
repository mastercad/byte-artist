<?php

namespace App\Tests\Service\Extractor;

use App\Service\Extractor\ClassName;
use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase
{
  /**
   * @dataProvider extractClassNameFromObjectDataProvider
   */
  public function testExtractClassNameFromObject($className, $expectation)
  {
    $extractor = new ClassName();
    $this->assertSame($expectation, $extractor->extractClassName(new $className()));
  }

  public function extractClassNameFromObjectDataProvider()
  {
    yield 'extract from stdClass' => [
      'className' => '\stdClass',
      'expectation' => 'stdClass'
    ];

    yield 'extract from extractor class' => [
      'className' => \App\Service\Extractor\ClassName::class,
      'expectation' => 'ClassName'
    ];

    yield 'extract from entity class' => [
      'className' => \App\Entity\Projects::class,
      'expectation' => 'Projects'
    ];
  }

  /**
   * @dataProvider extractClassNameFromFqdnDataProvider
   *
   * @param string $className
   * @param string $expectation
   * @return void
   */
  public function testExtractClassNameFromFqdn($className, $expectation)
  {
    $extractor = new ClassName();
    $this->assertSame($expectation, $extractor->extractClassNameFromFqdn($className));
  }

  public function extractClassNameFromFqdnDataProvider()
  {
    yield 'extract from simple class' => [
      'className' => 'TestClass',
      'expectation' => 'TestClass'
    ];

    yield 'extract from simple class with leading \ ' => [
      'className' => '\TestClass',
      'expectation' => 'TestClass'
    ];

    yield 'extract from full FQDN class name' => [
      'className' => 'Full\Path\To\Class',
      'expectation' => 'Class'
    ];

    yield 'extract from full FQDN class name with leading \ ' => [
      'className' => '\Full\Path\To\Class',
      'expectation' => 'Class'
    ];
  }
}