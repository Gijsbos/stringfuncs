<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FormatUriTest extends TestCase 
{
    public function testFormatUri() 
    {
        $this->assertEquals("api/users", format_uri("api/", "/users"));
    }

    public function testFormatUriKeepsLeadingAndTrailingSlash() 
    {
        $this->assertEquals("/api/users/", format_uri("/api//", "users/"));
    }

    public function testFormatUriScheme() 
    {
        $this->assertEquals("https://example.com/api/users", format_uri("https://example.com/", "/api/", "users"));
    }

    public function testFormatUriSkipsEmpty() 
    {
        $this->assertEquals("api/users", format_uri("api", "", null, "users"));
    }

    public function testFormatUriKeepsZero() 
    {
        $this->assertEquals("/users/0", format_uri("/users", "0"));
    }

    public function testFormatUriNumbers() 
    {
        $this->assertEquals("users/5", format_uri("users", 5));
    }

    public function testFormatUriNoArgs() 
    {
        $this->assertEquals("", format_uri());
    }
}
