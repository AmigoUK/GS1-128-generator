<?php
declare(strict_types=1);

namespace GS1\Tests;

use PHPUnit\Framework\TestCase;

final class CheckDigitTest extends TestCase {
    public function test_ean13_known_good(): void {
        $this->assertTrue(validate_ean13('5901234123457')['ok']);
        $this->assertTrue(validate_ean13('4006381333931')['ok']); // Staedtler classic
    }

    public function test_ean13_bad_check_digit(): void {
        $r = validate_ean13('5901234123450');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('should be 7', $r['error']);
    }

    public function test_ean13_wrong_length(): void {
        $this->assertFalse(validate_ean13('123456')['ok']);
        $this->assertFalse(validate_ean13('59012341234571')['ok']);
        $this->assertFalse(validate_ean13('5901234abcdef')['ok']);
    }

    public function test_gtin14_known_good(): void {
        // EAN-13 5901234123457 with packaging indicator 1 → GTIN-14 15901234123454
        $this->assertTrue(validate_gtin14('15901234123454')['ok']);
        $this->assertTrue(validate_gtin14('05901234123457')['ok']); // PI=0 case
    }

    public function test_gtin14_bad_check_digit(): void {
        $r = validate_gtin14('15901234123450');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('should be 4', $r['error']);
    }

    public function test_derive_gtin14_each_packaging_indicator(): void {
        $ean = '5901234123457';
        // Just confirm each PI 0-9 produces a 14-digit string with valid check digit.
        for ($pi = 0; $pi <= 9; $pi++) {
            $r = derive_gtin14_from_ean13($ean, $pi);
            $this->assertTrue($r['ok'], "PI=$pi failed: " . ($r['error'] ?? ''));
            $this->assertSame(14, strlen($r['gtin14']));
            $this->assertTrue(validate_gtin14($r['gtin14'])['ok']);
            $this->assertSame((string)$pi, $r['gtin14'][0]);
        }
    }

    public function test_derive_gtin14_known_pi_1(): void {
        $r = derive_gtin14_from_ean13('5901234123457', 1);
        $this->assertSame('15901234123454', $r['gtin14']);
    }

    public function test_derive_gtin14_rejects_bad_pi(): void {
        $this->assertFalse(derive_gtin14_from_ean13('5901234123457', 10)['ok']);
        $this->assertFalse(derive_gtin14_from_ean13('5901234123457', -1)['ok']);
    }
}
