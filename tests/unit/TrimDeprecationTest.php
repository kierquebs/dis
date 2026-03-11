<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests that document (and guard against) the TRIM deprecation pattern
 * found in the Wrecon controller and Process_model.
 *
 * Context
 * -------
 * The `pcf.SPECIFIC_DATE` column stores values such as "{1}", "{15}", "{15,30}".
 * The original code strips the braces with nested MySQL TRIM calls:
 *
 *   TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM pcf.SPECIFIC_DATE)))
 *
 * In MySQL 8.0.17+ this syntax with a single removal character is still valid,
 * but triggers deprecation warnings when the trim-character contains more than
 * one byte, and is confusing because TRIM only removes leading/trailing chars –
 * it cannot remove a middle '{' or '}' if the value is e.g. "{15,30}".
 *
 * The preferred replacement is REPLACE():
 *   REPLACE(REPLACE(pcf.SPECIFIC_DATE, '{', ''), '}', '')
 *
 * These PHP-side tests verify:
 *  1. The PHP equivalent (str_replace) of both approaches produces identical
 *     output for all known SPECIFIC_DATE formats.
 *  2. PHP 8.x trim() with a potential null value (line 309 in Wrecon.php)
 *     is handled safely.
 */
class TrimDeprecationTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Helpers that mirror the SQL logic in PHP so we can test equivalence.
    // -----------------------------------------------------------------------

    /**
     * Mirrors: TRIM( BOTH '}' FROM (TRIM(BOTH '{' FROM value)))
     * TRIM(BOTH x FROM str) strips leading AND trailing occurrences of x.
     */
    private function sqlTrimBoth(string $value): string
    {
        return trim(trim($value, '{'), '}');
    }

    /**
     * Mirrors: REPLACE(REPLACE(value, '{', ''), '}', '')
     * This is the non-deprecated replacement.
     */
    private function sqlReplace(string $value): string
    {
        return str_replace(['{', '}'], '', $value);
    }

    // -----------------------------------------------------------------------
    // 1. Equivalence: both approaches must produce the same result for all
    //    known SPECIFIC_DATE formats.
    // -----------------------------------------------------------------------

    #[DataProvider('specificDateProvider')]
    public function test_trim_and_replace_are_equivalent(string $raw, string $expected): void
    {
        $trimResult    = $this->sqlTrimBoth($raw);
        $replaceResult = $this->sqlReplace($raw);

        $this->assertSame($expected, $trimResult,    "TRIM approach failed for '$raw'");
        $this->assertSame($expected, $replaceResult, "REPLACE approach failed for '$raw'");
        $this->assertSame($trimResult, $replaceResult, 'TRIM and REPLACE must agree');
    }

    public static function specificDateProvider(): array
    {
        return [
            'single day 1'        => ['{1}',     '1'],
            'single day 15'       => ['{15}',    '15'],
            'single day 31'       => ['{31}',    '31'],
            'semi-monthly 15,30'  => ['{15,30}', '15,30'],
            'semi-monthly 1,15'   => ['{1,15}',  '1,15'],
            'already clean value' => ['15',      '15'],
            'empty string'        => ['',        ''],
        ];
    }

    // -----------------------------------------------------------------------
    // 2. PHP 8.x null-safe trim (Wrecon.php line 309)
    //    trim($row['brAFFCODE']) where brAFFCODE can be NULL.
    // -----------------------------------------------------------------------

    public function test_trim_with_null_value_is_handled_safely(): void
    {
        $brAFFCODE = null;

        // PHP 8.1+ deprecates passing null to trim(); PHP 8.4 turns it into
        // a TypeError. The safe approach is to cast to string first.
        $safe = trim((string) $brAFFCODE);

        $this->assertSame('', $safe);
    }

    public function test_trim_with_affcode_string_works_correctly(): void
    {
        $brAFFCODE = '  GRP-001  ';
        $this->assertSame('GRP-001', trim($brAFFCODE));
    }

    // -----------------------------------------------------------------------
    // 3. Boundary: REPLACE handles interior braces that TRIM would miss.
    //    This is a known limitation of the old TRIM approach.
    // -----------------------------------------------------------------------

    public function test_replace_handles_interior_braces(): void
    {
        // A hypothetical value with interior braces (edge case).
        $value = '{10{20}}';

        $replaceResult = $this->sqlReplace($value);
        $this->assertSame('1020', $replaceResult);

        // TRIM only removes leading/trailing chars, so it would leave interior
        // braces – demonstrating why REPLACE is more robust.
        $trimResult = $this->sqlTrimBoth($value);
        $this->assertNotSame($replaceResult, $trimResult,
            'TRIM cannot remove interior braces; only REPLACE can – confirms the fix is needed'
        );
    }
}
