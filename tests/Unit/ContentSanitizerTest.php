<?php

namespace Tests\Unit;

use App\Services\ContentSanitizer;
use Tests\TestCase;

class ContentSanitizerTest extends TestCase
{
    public function test_removes_script_tags_event_handlers_and_javascript_links(): void
    {
        $clean = app(ContentSanitizer::class)->sanitize(
            '<p onclick="alert(1)">Hello</p><script>alert(1)</script><a href="javascript:alert(1)">bad link</a>'
        );

        $this->assertStringContainsString('<p>Hello</p>', $clean);
        $this->assertStringNotContainsString('<script', strtolower($clean));
        $this->assertStringNotContainsString('onclick', strtolower($clean));
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
    }

    public function test_removes_external_inline_image_sources_even_when_the_path_looks_local(): void
    {
        config(['app.url' => 'http://localhost:8080']);
        $clean = app(ContentSanitizer::class)->sanitize('<p>Text</p><img src="https://evil.example/storage/remote.png">');
        $this->assertStringContainsString('<p>Text</p>', $clean);
        $this->assertStringNotContainsString('<img', $clean);
    }
    public function test_keeps_an_image_url_from_the_configured_application_origin(): void
    {
        config(['app.url' => 'http://localhost:8080']);
        $clean = app(ContentSanitizer::class)->sanitize('<p>Text</p><img src="http://localhost:8080/storage/media/test.png" alt="test">');
        $this->assertStringContainsString('src="/storage/media/test.png"', $clean);
        $this->assertStringNotContainsString('localhost:8080', $clean);
    }
}
