<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OddTest extends TestCase 
{
    public function testOddTrue() 
    {
        $result = odd(1);
        $this->assertTrue($result);
    }

    public function testOddFalse() 
    {
        $result = odd(2);
        $this->assertFalse($result);
    }

    public function testOddNegative() 
    {
        $this->assertTrue(odd(-3));
        $this->assertFalse(odd(-2));
    }
}