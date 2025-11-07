<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReplacePlaceholderArrayTest extends TestCase 
{
    public function testReplacePlaceholderArray()
    {
        $string = "this is %{placeholder} result %{placeholder2}";
        $result = \replace_placeholder_array([
            "placeholder" => "the intended",
            "placeholder2" => "as expected",
        ], $string);
        $expectedResult = "this is the intended result as expected";
        $this->assertEquals($expectedResult, $result);
    }
}