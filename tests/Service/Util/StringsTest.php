<?php

namespace App\Tests\Service\Util;

use App\Service\Util\Strings;
use PHPUnit\Framework\TestCase;

class StringsTest extends TestCase
{
  /**
   * @dataProvider convertToCamelCaseProvider
   *
   * @param mixed $content
   * @param mixed $expectation
   * 
   * @return void
   */
  public function testConvertToCamelCase($content, $separator, $expectation)
  {
    self::assertSame($expectation, Strings::convertToCamelCase($content, $separator));
  }

  public function convertToCamelCaseProvider()
  {
    yield 'nothing to convert in single word' => [
      'content' => 'TestWord',
      'separator' => '_',
      'expectation' => 'TestWord'
    ];

    yield 'replace single separator' => [
      'content' => 'test_word',
      'separator' => '_',
      'expectation' => 'testWord'
    ];

    yield 'replace multiple founds with mixed camel case' => [
      'content' => 'test_word_With_teSdst_filE_',
      'separator' => '_',
      'expectation' => 'testWordWithTeSdstFilE_'
    ];

    yield 'replace multiple founds with regex reserved char' => [
      'content' => 'test?word?With?teSdst?filE',
      'separator' => '?',
      'expectation' => 'testWordWithTeSdstFilE'
    ];

    yield 'replace string with only separators' => [
      'content' => '?????????????????????',
      'separator' => '?',
      'expectation' => '?????????????????????'
    ];
  }

  /**
   * @dataProvider convertFromCamelCaseProvider
   *
   * @param string $content
   * @param string $separator
   * @param string $expectation
   *
   * @return void
   */
  public function testConvertFromCamelCase($content, $separator, $expectation)
  {
    self::assertSame($expectation, Strings::convertFromCamelCase($content, $separator));
  }

  public function convertFromCamelCaseProvider()
  {
    yield 'nothing to convert in single word' => [
      'content' => 'Testword',
      'separator' => '_',
      'expectation' => 'testword'
    ];

    yield 'replace single camel case' => [
      'content' => 'TestWord',
      'separator' => '_',
      'expectation' => 'test_word'
    ];

    yield 'replace in only uppercase string' => [
      'content' => 'TESTWORD',
      'separator' => '_',
      'expectation' => 't_e_s_t_w_o_r_d'
    ];

    yield 'replace in only uppercase string and regex reserved character' => [
      'content' => 'TESTWORD',
      'separator' => '*',
      'expectation' => 't*e*s*t*w*o*r*d'
    ];
  }

  /**
   * @dataProvider makeStringLinkSaveProvider
   *
   * @param string $content
   * @param string $expectation
   *
   * @return void
   */
  public function testMakeStringLinkSave($content, $expectation)
  {
    self::assertSame($expectation, Strings::makeStringLinkSave($content));
  }

  public function makeStringLinkSaveProvider()
  {
    yield 'convert only single lowercase word' => [
      'content' => 'testword',
      'expectation' => 'testword'
    ];

    yield 'convert only single camel case word' => [
      'content' => 'TestWord',
      'expectation' => 'testword'
    ];

    yield 'convert two word' => [
      'content' => 'Test Word',
      'expectation' => 'test-word'
    ];

    yield 'convert complete sentence' => [
      'content' => 'Attention: This is complete sentence who have to converted to link save format',
      'expectation' => 'attention-this-is-complete-sentence-who-have-to-converted-to-link-save-format'
    ];
  }
}