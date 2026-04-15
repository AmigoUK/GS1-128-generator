<?php
declare(strict_types=1);

namespace GS1\Tests;

use PHPUnit\Framework\TestCase;

final class AiValueTest extends TestCase {
    public function test_ai01_requires_valid_gtin14(): void {
        $this->assertTrue(validate_ai_value('01', '15901234123454')['ok']);
        $this->assertFalse(validate_ai_value('01', '15901234123450')['ok']);
        $this->assertFalse(validate_ai_value('01', '123')['ok']);
    }

    public function test_ai10_alphanumeric_charset_82(): void {
        $this->assertTrue(validate_ai_value('10', 'LOT2024A')['ok']);
        // Sample of allowed punctuation within the 20-char limit
        $this->assertTrue(validate_ai_value('10', "A!%&'()*+,-.:_z")['ok']);
        $r = validate_ai_value('10', 'BAD SPACE');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('Set 82', $r['error']);
    }

    public function test_ai10_length_limits(): void {
        $this->assertFalse(validate_ai_value('10', '')['ok']);
        $this->assertFalse(validate_ai_value('10', str_repeat('A', 21))['ok']);
        $this->assertTrue(validate_ai_value('10', str_repeat('A', 20))['ok']);
    }

    public function test_dates_yymmdd_and_iso(): void {
        $r = validate_ai_value('15', '260115');
        $this->assertTrue($r['ok']); $this->assertSame('260115', $r['encoded']);
        $r = validate_ai_value('15', '2026-01-15');
        $this->assertTrue($r['ok']); $this->assertSame('260115', $r['encoded']);
    }

    public function test_dates_dd_zero_means_end_of_month(): void {
        // DD=00 must validate (last-day-of-month convention, non-healthcare)
        $r = validate_ai_value('15', '260100');
        $this->assertTrue($r['ok']); $this->assertSame('260100', $r['encoded']);
    }

    public function test_dates_invalid_month(): void {
        $r = validate_ai_value('15', '261301');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('Month', $r['error']);
    }

    public function test_dates_nonexistent_calendar_day(): void {
        $r = validate_ai_value('15', '260230'); // Feb 30
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('does not exist', $r['error']);
    }

    public function test_weight_310x_decimal_resolution(): void {
        // 1.250 kg → AI 3103, encoded 001250
        $r = validate_ai_value('310', '1.250');
        $this->assertTrue($r['ok']);
        $this->assertSame('3103', $r['resolved_code']);
        $this->assertSame('001250', $r['encoded']);

        // 12.5 kg → AI 3101, encoded 000125
        $r = validate_ai_value('310', '12.5');
        $this->assertSame('3101', $r['resolved_code']);
        $this->assertSame('000125', $r['encoded']);

        // 250 kg → AI 3100, encoded 000250
        $r = validate_ai_value('310', '250');
        $this->assertSame('3100', $r['resolved_code']);
        $this->assertSame('000250', $r['encoded']);
    }

    public function test_weight_rejects_non_numeric_and_overflow(): void {
        $this->assertFalse(validate_ai_value('310', 'abc')['ok']);
        $this->assertFalse(validate_ai_value('310', '12345678')['ok']); // 8 digits combined
    }

    public function test_count_ai37_integer_only(): void {
        $this->assertTrue(validate_ai_value('37', '24')['ok']);
        $this->assertFalse(validate_ai_value('37', '24.5')['ok']);
        $this->assertFalse(validate_ai_value('37', '999999999')['ok']); // > 8 digits
    }

    public function test_purchase_order_ai400(): void {
        $this->assertTrue(validate_ai_value('400', 'PO-2026-0001')['ok']);
        $this->assertFalse(validate_ai_value('400', str_repeat('A', 31))['ok']);
    }
}
