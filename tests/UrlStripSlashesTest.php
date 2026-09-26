<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UrlStripSlashesTest extends TestCase 
{
    public function testUrlStripSlashes() 
    {
        $this->assertEquals("a/b", url_strip_slashes("/a/b/", true, true));
    }

    public function testUrlStripSlashesFrontOnly() 
    {
        $this->assertEquals("a/b/", url_strip_slashes("/a/b/", true, false));
    }

    public function testUrlStripSlashesBackOnly() 
    {
        $this->assertEquals("/a/b", url_strip_slashes("/a/b/", false, true));
    }

    public function testUrlStripSlashesEmpty() 
    {
        $this->assertEquals("", url_strip_slashes("", true, true));
    }
}
