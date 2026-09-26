<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EnclosedRangesTest extends TestCase 
{
    public function testEnclosedRanges()
    {
        $result = enclosed_ranges("(", ")", "a (b (c)) (d)");
        $expectedResult = [[2, 9], [10, 13]];
        $this->assertEquals($expectedResult, $result);
    }

    public function testEnclosedRangesUnbalanced()
    {
        $result = enclosed_ranges("(", ")", "(a) ((b)");
        $expectedResult = [[0, 3]];
        $this->assertEquals($expectedResult, $result);
    }

    public function testEnclosedRangesMultiCharacter()
    {
        $result = enclosed_ranges("{{", "}}", "x {{a}} y");
        $expectedResult = [[2, 7]];
        $this->assertEquals($expectedResult, $result);
    }

    public function testEnclosedRangesDeeplyNested()
    {
        $string = str_repeat("(", 50000) . str_repeat(")", 50000);
        $result = enclosed_ranges("(", ")", $string);
        $this->assertEquals([[0, 100000]], $result);
    }
}
