<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EvenTest extends TestCase 
{
    public function testEvenTrue() 
    {
        $result = even(2);
        $this->assertTrue($result);
    }

    public function testEvenFalse() 
    {
        $result = even(1);
        $this->assertFalse($result);
    }

    public function testEvenNegative() 
    {
        $this->assertTrue(even(-2));
        $this->assertFalse(even(-3));
    }
}