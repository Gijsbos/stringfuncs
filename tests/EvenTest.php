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
}