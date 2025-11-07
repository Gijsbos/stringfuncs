<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class IsWrappedInQuotesTest extends TestCase 
{
    public function testIsWrappedInQuotesTrue() 
    {
        $input = "'hi'";
        $result = is_wrapped_in_quotes($input);
        $this->assertTrue($result);
    }

    public function testIsWrappedInQuotesFalseSingleQuote() 
    {
        $input = "hi'";
        $result = is_wrapped_in_quotes($input);
        $this->assertFalse($result);
    }

    public function testIsWrappedInQuotesFalse() 
    {
        $input = "hi";
        $result = is_wrapped_in_quotes($input);
        $this->assertFalse($result);
    }
}