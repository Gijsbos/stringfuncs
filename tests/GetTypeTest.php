<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GetTypeTest extends TestCase 
{
    public function testGetType()
    {
        $result = get_type(1);
        $expectedResult = gettype(1);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTypeObject()
    {
        $result = get_type(new \DateTime());
        $expectedResult = "DateTime";
        $this->assertEquals($expectedResult, $result);
    }
}