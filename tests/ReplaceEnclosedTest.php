<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReplaceEnclosedTest extends TestCase 
{
    public function testReplaceEnclosed()
    {
        $string = "this is (is a) test";
        $result = \replace_enclosed("(", ")", $string, "is", "was");
        $expectedResult = "this is (was a) test";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedNoCloseParentheses()
    {
        $string = "this is (is a test";
        $result = \replace_enclosed("(", ")", $string, "is", "was");
        $expectedResult = "this is (is a test";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedMultiCharacterOpenCloseSymbol()
    {
        $string = "this is {{is a test}}";
        $result = \replace_enclosed("{{", "}}", $string, "is", "was");
        $expectedResult = "this is {{was a test}}";
        $this->assertEquals($expectedResult, $result);
    }
}