<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrMustEndWithTest extends TestCase 
{
    public function testStrMustEndWith1()
    {
        $result = str_must_end_with("test", "st");
        $this->assertEquals("test", $result);
    }

    public function testStrMustEndWith2()
    {
        $result = str_must_end_with("te", "st");
        $this->assertEquals("test", $result);
    }
}