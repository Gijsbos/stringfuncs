<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrEqualsTest extends TestCase 
{
    public function testStrEquals() 
    {
        $this->assertTrue(str_equals("abc", "abc"));
        $this->assertFalse(str_equals("abc", "abd"));
        $this->assertFalse(str_equals("abc", "ABC"));
    }
}
