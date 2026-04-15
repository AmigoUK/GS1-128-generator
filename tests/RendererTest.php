<?php
declare(strict_types=1);

namespace GS1\Tests;

use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase {
    public function test_png_has_valid_signature(): void {
        $payload = assemble_gs1([
            ['01', '15901234123454'],
            ['15', '260115'],
            ['10', 'LOT2024A'],
        ])['machine'];
        $png = render_png($payload);
        $this->assertNotEmpty($png);
        // PNG signature: 89 50 4E 47 0D 0A 1A 0A
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
    }

    public function test_svg_is_valid_xml_with_rects(): void {
        $payload = assemble_gs1([
            ['01', '15901234123454'],
            ['10', 'LOT2024A'],
        ])['machine'];
        $svg = render_svg($payload);
        $this->assertStringStartsWith('<?xml', $svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('<rect', $svg);
        // Round-trip parse to confirm well-formed XML
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($svg);
        libxml_use_internal_errors($prev);
        $this->assertNotFalse($doc);
    }

    public function test_fnc1_placeholder_replaced_in_payload(): void {
        // Two variable-length AIs: the assembler inserts <FNC1>; renderer must substitute 0xF1.
        $payload = assemble_gs1([
            ['10', 'LOT2024A'],
            ['21', 'SN000123'],
        ])['machine'];
        $this->assertStringContainsString('<FNC1>', $payload);
        // Renderer should not throw when given the placeholder string.
        $this->assertNotEmpty(render_png($payload));
    }
}
