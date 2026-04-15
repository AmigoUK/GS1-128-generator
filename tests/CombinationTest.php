<?php
declare(strict_types=1);

namespace GS1\Tests;

use PHPUnit\Framework\TestCase;

final class CombinationTest extends TestCase {
    public function test_no_duplicates(): void {
        $r = validate_ai_combinations([['10', 'A'], ['10', 'B']]);
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('Duplicate', $r['errors'][0]);
    }

    public function test_01_and_02_mutually_exclusive(): void {
        $r = validate_ai_combinations([['01', '15901234123454'], ['02', '15901234123454'], ['37', '24']]);
        $this->assertFalse($r['ok']);
        $this->assertContains('AI (01) and AI (02) cannot appear together.', $r['errors']);
    }

    public function test_02_requires_37(): void {
        $r = validate_ai_combinations([['02', '15901234123454']]);
        $this->assertFalse($r['ok']);
        $this->assertContains('AI (02) and AI (37) must be used together.', $r['errors']);
    }

    public function test_37_requires_02(): void {
        $r = validate_ai_combinations([['37', '24']]);
        $this->assertFalse($r['ok']);
        $this->assertContains('AI (02) and AI (37) must be used together.', $r['errors']);
    }

    public function test_total_48_char_limit(): void {
        // Build a payload that will exceed 48 characters across all (code+value)
        $resolved = [
            ['01', '15901234123454'],     // 2+14 = 16
            ['10', str_repeat('X', 20)],  // 2+20 = 22
            ['21', str_repeat('Y', 20)],  // 2+20 = 22 → total 60
        ];
        $r = validate_ai_combinations($resolved);
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('48', implode(' ', $r['errors']));
    }

    public function test_valid_combination(): void {
        $r = validate_ai_combinations([
            ['01', '15901234123454'],
            ['15', '260115'],
            ['10', 'LOT2024A'],
            ['3103', '001250'],
        ]);
        $this->assertTrue($r['ok'], implode(' / ', $r['errors']));
    }
}
