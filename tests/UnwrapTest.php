<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UnwrapTest extends TestCase 
{
    public function testUnwrap() 
    {
        $input = "{example}";
        $result = unwrap($input, "{", "}");
        $expectedResult = "example";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUnwrapNoEffect() 
    {
        $input = "example";
        $result = unwrap($input, "{", "}");
        $expectedResult = "example";
        $this->assertEquals($expectedResult, $result);
    }
}