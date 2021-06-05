<?php
namespace App\Tests\Unit;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Include functionality for accessing protected/private members and methods
 */
abstract class BaseTestCase extends TestCase
{
    protected static function setProperty($object, $propertyName, $propertyValue) {
        $reflection = new ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $propertyValue);
    }

    protected static function getProperty($object, $propertyName) {
        $reflection = new ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    protected static function callMethod($object, $methodName, $arguments = []) {
        $reflection = new ReflectionClass($object);
        $reflectionMethod = $reflection->getMethod($methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $arguments);
    }
}
