<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrMustNotEndWithTest extends TestCase 
{
    public function testStrMustNotEndWith1()
    {
        $result = str_must_not_end_with("test", "st");
        $this->assertEquals("te", $result);
    }

    public function testStrMustNotEndWith2()
    {
        $result = str_must_not_end_with("te", "st");
        $this->assertEquals("te", $result);
    }
}