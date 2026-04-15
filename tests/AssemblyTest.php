<?php
declare(strict_types=1);

namespace GS1\Tests;

use PHPUnit\Framework\TestCase;

final class AssemblyTest extends TestCase {
    public function test_predefined_first_variable_last(): void {
        $r = assemble_gs1([
            ['10', 'LOT2024A'],   // variable
            ['01', '15901234123454'], // predefined
            ['15', '260115'],     // predefined
        ]);
        // Order: 01, 15, 10
        $this->assertSame('(01)15901234123454(15)260115(10)LOT2024A', $r['hri']);
    }

    public function test_no_fnc1_when_only_predefined_or_when_variable_is_last(): void {
        $r = assemble_gs1([
            ['01', '15901234123454'],
            ['15', '260115'],
            ['10', 'LOT2024A'],
        ]);
        $this->assertStringNotContainsString('<FNC1>', $r['machine']);
        $this->assertSame('011590123412345415260115' . '10LOT2024A', $r['machine']);
    }

    public function test_fnc1_between_two_variable_ais(): void {
        $r = assemble_gs1([
            ['10', 'LOT2024A'],
            ['21', 'SN000123'],
        ]);
        // After ordering both are variable; first variable AI is followed by another → FNC1.
        $this->assertSame('10LOT2024A<FNC1>21SN000123', $r['machine']);
    }

    public function test_segments_carry_needs_fnc1_flag(): void {
        $r = assemble_gs1([
            ['01', '15901234123454'],
            ['10', 'LOT2024A'],
            ['21', 'SN000123'],
        ]);
        $segments = $r['segments'];
        $this->assertCount(3, $segments);
        $this->assertSame('01', $segments[0]['code']);
        $this->assertFalse($segments[0]['needs_fnc1']);
        $this->assertSame('10', $segments[1]['code']);
        $this->assertTrue($segments[1]['needs_fnc1']);
        $this->assertSame('21', $segments[2]['code']);
        $this->assertFalse($segments[2]['needs_fnc1']);
    }
}
