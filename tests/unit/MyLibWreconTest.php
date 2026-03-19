<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for My_lib methods consumed by process/wrecon.
 *
 * These tests cover:
 *  - paymentTerms()          – maps term codes to label strings
 *  - setCFDate()             – resolves a specific-date cutoff to a Y-m-d value
 *  - setCFDay()              – resolves a day-of-week cutoff to a Y-m-d value
 *  - checkVAT()              – converts VAT condition to a rate
 *  - convertMFRATE()         – converts a merchant-fee value to a rate/percentage
 *  - computeMF()             – calculates the Marketing Fee amount
 *  - computeVAT()            – calculates the VAT amount
 *  - computeNETDUE()         – calculates the Net Due amount
 *  - computeExpectedDueDate()– adds calendar or working days to a date
 *  - digitalID()             – adds / strips the 'Z' prefix from a CP ID
 */
class MyLibWreconTest extends TestCase
{
    private My_lib $lib;

    protected function setUp(): void
    {
        $this->lib = new My_lib();
    }

    // -----------------------------------------------------------------------
    // paymentTerms
    // -----------------------------------------------------------------------

    #[DataProvider('paymentTermsProvider')]
    public function test_paymentTerms_returns_correct_label(int $code, string $expected): void
    {
        $this->assertSame($expected, $this->lib->paymentTerms($code));
    }

    public static function paymentTermsProvider(): array
    {
        return [
            'monthly (default)'   => [1, 'Monthly'],
            'semi-monthly'        => [2, 'Semi-Monthly'],
            'weekly'              => [3, 'Weekly'],
            'every 10 days'       => [4, 'Every 10 days'],
            'unknown code → monthly' => [99, 'Monthly'],
        ];
    }

    // -----------------------------------------------------------------------
    // setCFDay
    // -----------------------------------------------------------------------

    public function test_setCFDay_returns_last_occurrence_of_day(): void
    {
        // Ask for the last Monday; the result must be a Monday in the past.
        $result = $this->lib->setCFDay('Monday');

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
        $this->assertSame('Monday', date('l', strtotime($result)));
        $this->assertLessThanOrEqual(strtotime('today'), strtotime($result));
    }

    public function test_setCFDay_returns_last_friday(): void
    {
        $result = $this->lib->setCFDay('Friday');

        $this->assertSame('Friday', date('l', strtotime($result)));
    }

    // -----------------------------------------------------------------------
    // setCFDate
    // -----------------------------------------------------------------------

    public function test_setCFDate_returns_valid_date_format(): void
    {
        // Use a day-of-month that is guaranteed to produce a valid date.
        $result = $this->lib->setCFDate(15);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-15$/', $result);
    }

    public function test_setCFDate_day_31_handles_short_months(): void
    {
        // day 31 must clamp to the last day of the month.
        $result = $this->lib->setCFDate(31);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
        $ts = strtotime($result);
        $this->assertNotFalse($ts, 'setCFDate(31) must produce a parseable date');
    }

    // -----------------------------------------------------------------------
    // checkVAT
    // -----------------------------------------------------------------------

    public function test_checkVAT_taxable_returns_012(): void
    {
        $this->assertSame(0.12, $this->lib->checkVAT('Taxable'));
    }

    public function test_checkVAT_non_taxable_returns_zero(): void
    {
        $this->assertSame(0, $this->lib->checkVAT('Non-Taxable'));
        $this->assertSame(0, $this->lib->checkVAT(''));
    }

    // -----------------------------------------------------------------------
    // convertMFRATE
    // -----------------------------------------------------------------------

    public function test_convertMFRATE_to_decimal(): void
    {
        // MF stored as 2  =>  rate 0.02
        $this->assertEqualsWithDelta(0.02, $this->lib->convertMFRATE(2), 0.0001);
    }

    public function test_convertMFRATE_to_percentage_string(): void
    {
        $result = $this->lib->convertMFRATE(2, true);
        $this->assertSame('200%', $result);
    }

    // -----------------------------------------------------------------------
    // computeMF
    // -----------------------------------------------------------------------

    #[DataProvider('computeMFProvider')]
    public function test_computeMF(float $totalFV, int|float $MF, float $expected): void
    {
        // convertMFRATE(MF) divides by 100, so MF=2 → rate=0.02
        $result = $this->lib->computeMF($totalFV, $MF);
        $this->assertEqualsWithDelta($expected, $result, 0.01);
    }

    public static function computeMFProvider(): array
    {
        return [
            // totalFV=199, stored MF=2  =>  rate=0.02  =>  MF=3.98
            'standard MF'        => [199, 2, 3.98],
            // totalFV=1000, MF=5  =>  rate=0.05  =>  MF=50.00
            'larger fee'         => [1000, 5, 50.0],
            // totalFV=0 edge case
            'zero total'         => [0, 2, 0.0],
        ];
    }

    // -----------------------------------------------------------------------
    // computeVAT
    // -----------------------------------------------------------------------

    public function test_computeVAT_taxable(): void
    {
        // totalFV=199, MF=2  =>  marketingFee=3.98  =>  VAT=3.98*0.12=0.4776 ≈ 0.48
        $result = $this->lib->computeVAT(199, 2, 0.12);
        $this->assertEqualsWithDelta(0.48, $result, 0.01);
    }

    public function test_computeVAT_non_taxable_returns_zero(): void
    {
        $result = $this->lib->computeVAT(199, 2, 0);
        $this->assertSame(0.0, $result);
    }

    // -----------------------------------------------------------------------
    // computeNETDUE
    // -----------------------------------------------------------------------

    public function test_computeNETDUE_taxable(): void
    {
        // totalFV=199, MF=2, VAT=0.12
        //  marketingFee = 199 * 0.02 = 3.98
        //  VAT          = 3.98 * 0.12 = 0.4776
        //  NET_DUE      = 199 - 3.98 - 0.4776 = 194.5424 ≈ 194.54
        $result = $this->lib->computeNETDUE(199, 2, 0.12);
        $this->assertEqualsWithDelta(194.54, $result, 0.01);
    }

    public function test_computeNETDUE_non_taxable(): void
    {
        // No VAT: NET_DUE = 199 - 3.98 = 195.02
        $result = $this->lib->computeNETDUE(199, 2, 0);
        $this->assertEqualsWithDelta(195.02, $result, 0.01);
    }

    public function test_computeNETDUE_zero_total(): void
    {
        $result = $this->lib->computeNETDUE(0, 2, 0.12);
        $this->assertSame(0.0, $result);
    }

    // -----------------------------------------------------------------------
    // computeExpectedDueDate
    // -----------------------------------------------------------------------

    public function test_computeExpectedDueDate_calendar_days(): void
    {
        $base   = '2024-01-10';
        $result = $this->lib->computeExpectedDueDate(1, 5, $base);

        $this->assertSame('2024-01-15', $result);
    }

    public function test_computeExpectedDueDate_working_days_skips_weekend(): void
    {
        // 2024-01-12 is a Friday; +1 working day = Monday 2024-01-15
        $result = $this->lib->computeExpectedDueDate(2, 1, '2024-01-12');

        $this->assertSame('2024-01-15', $result);
    }

    public function test_computeExpectedDueDate_calendar_type_3_same_as_1(): void
    {
        // dayType 3 behaves like calendar days
        $result = $this->lib->computeExpectedDueDate(3, 5, '2024-01-10');
        $this->assertSame('2024-01-15', $result);
    }

    // -----------------------------------------------------------------------
    // digitalID
    // -----------------------------------------------------------------------

    public function test_digitalID_encodes_cpid(): void
    {
        $this->assertSame('Z42', $this->lib->digitalID(42));
    }

    public function test_digitalID_decodes_cpid(): void
    {
        $this->assertSame('42', $this->lib->digitalID('Z42', true));
    }
}
