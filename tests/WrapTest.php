<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WrapTest extends TestCase 
{
    public function testWrap() 
    {
        $input = "example";
        $result = wrap($input, "{", "}");
        $expectedResult = "{example}";
        $this->assertEquals($expectedResult, $result);
    }

    public function testWrapNoEffect() 
    {
        $input = "{example}";
        $result = wrap($input, "{", "}");
        $expectedResult = "{example}";
        $this->assertEquals($expectedResult, $result);
    }
}