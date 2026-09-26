<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrStartsEndsWithTest extends TestCase 
{
    public function testStrStartsEndsWith() 
    {
        $this->assertTrue(str_starts_ends_with("/a/", "/"));
        $this->assertFalse(str_starts_ends_with("/a", "/"));
    }

    public function testStrStartsEndsWithDifferentEnd() 
    {
        $this->assertTrue(str_starts_ends_with("[a]", "[", "]"));
        $this->assertFalse(str_starts_ends_with("[a[", "[", "]"));
    }
}
