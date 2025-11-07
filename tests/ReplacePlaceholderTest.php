<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReplacePlaceholderTest extends TestCase 
{
    public function testReplacePlaceholder()
    {
        $string = "this is %{placeholder} result";
        $result = \replace_placeholder("placeholder", "the intended", $string);
        $expectedResult = "this is the intended result";
        $this->assertEquals($expectedResult, $result);
    }
}