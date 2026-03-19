<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for the Summary export pipeline.
 *
 * Route under test: GET /summary/export?search=1
 *
 * Full pipeline:
 *   1. Summary::export()
 *        → Sys_model::getTransactionSummary_part3($where, …)   [DB query]
 *        → Summary::arr_result($query)                          [row formatter]
 *             pa_id    field → My_lib::paNumber()   e.g. 1 → "Z000001"
 *             TOTAL_FV field → number_format($v, 2) e.g. 1234.5 → "1,234.50"
 *        → Download_file::summary_report(['filename'=>'PA SUMMARY'], $arr)
 *             Builds a 5-column PHPExcel workbook:
 *               col A: PAYMENT ADVICE ID
 *               col B: MERCHANT ID
 *               col C: MERCHANT NAME
 *               col D: TOTAL AMOUNT
 *               col E: PAYMENT DUE DATE
 *             Appends date + ".xls" to filename and streams to browser.
 *
 * Because the full CI framework (auth, DB, HTTP headers) cannot be booted
 * in unit tests, we test two things independently:
 *   a) The formatting helpers (paNumber, number_format) that arr_result() uses
 *   b) The PHPExcel workbook built by summary_report() — we replicate its
 *      logic, save to a temp file, read the file back, and assert on every
 *      cell to confirm correct headers, data, ordering, and column count.
 */
class SummaryExportTest extends TestCase
{
    private My_lib $lib;

    protected function setUp(): void
    {
        $this->lib = new My_lib();
    }

    // -----------------------------------------------------------------------
    // Helper: replicate the PHPExcel workbook that summary_report() produces,
    // persist to a temp .xls file, and return the re-loaded spreadsheet.
    // -----------------------------------------------------------------------

    /**
     * @param stdClass[] $rows  Pre-formatted rows (as arr_result() returns them)
     */
    private function buildWorkbook(array $rows): PHPExcel
    {
        $wb = new PHPExcel();
        $wb->getProperties()->setTitle('PA SUMMARY')->setDescription('');
        $wb->setActiveSheetIndex(0);
        $sheet = $wb->getActiveSheet();

        // summary_report() writes headers with 0-based column indexing
        $headers = [
            'PAYMENT ADVICE ID',
            'MERCHANT ID',
            'MERCHANT NAME',
            'TOTAL AMOUNT',
            'PAYMENT DUE DATE',
        ];
        $col = 0;
        foreach ($headers as $label) {
            $sheet->setCellValueByColumnAndRow($col, 1, $label);
            $col++;
        }

        // Data rows start at row 2
        $x = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$x}", $row->pa_id);
            $sheet->setCellValue("B{$x}", $row->m_id);
            $sheet->setCellValue("C{$x}", $row->legalname);
            $sheet->setCellValue("D{$x}", $row->TOTAL_FV);
            $sheet->setCellValue("E{$x}", $row->pa_duedate);
            $x++;
        }

        // Save → reload so we exercise the full file round-trip
        $tmp = tempnam(sys_get_temp_dir(), 'pa_summary_test_') . '.xls';
        PHPExcel_IOFactory::createWriter($wb, 'Excel5')->save($tmp);
        $loaded = PHPExcel_IOFactory::load($tmp);
        unlink($tmp);

        return $loaded;
    }

    /** Build one stdClass row the same way arr_result() would. */
    private function makeRow(
        int    $rawPaId,
        string $mId,
        string $legalname,
        float  $totalFv,
        string $dueDate
    ): stdClass {
        $row            = new stdClass();
        $row->pa_id     = $this->lib->paNumber($rawPaId);   // e.g. "Z000001"
        $row->m_id      = $mId;
        $row->legalname = $legalname;
        $row->TOTAL_FV  = number_format($totalFv, 2);        // e.g. "1,234.56"
        $row->pa_duedate = $dueDate;
        return $row;
    }

    // -----------------------------------------------------------------------
    // A. arr_result() formatters
    // -----------------------------------------------------------------------

    /**
     * @dataProvider paNumberProvider
     */
    public function test_arr_result_formats_pa_id_with_z_prefix_and_padding(
        int    $rawId,
        string $expected
    ): void {
        $this->assertSame($expected, $this->lib->paNumber($rawId));
    }

    public static function paNumberProvider(): array
    {
        return [
            'single digit'  => [1,       'Z000001'],
            'two digits'    => [99,      'Z000099'],
            'five digits'   => [12345,   'Z012345'],
            'six digits'    => [100000,  'Z100000'],
            'seven digits'  => [1234567, 'Z1234567'],
        ];
    }

    /**
     * @dataProvider totalFvProvider
     */
    public function test_arr_result_formats_total_fv_to_two_decimal_places(
        float  $raw,
        string $expected
    ): void {
        $this->assertSame($expected, number_format($raw, 2));
    }

    public static function totalFvProvider(): array
    {
        return [
            'whole number'   => [1000.0,  '1,000.00'],
            'decimal amount' => [1234.56, '1,234.56'],
            'large amount'   => [99999.99,'99,999.99'],
            'zero'           => [0.0,     '0.00'],
        ];
    }

    // -----------------------------------------------------------------------
    // B. Workbook header row
    // -----------------------------------------------------------------------

    public function test_export_row1_contains_correct_column_headers(): void
    {
        $wb    = $this->buildWorkbook([]);
        $sheet = $wb->getActiveSheet();

        $expected = [
            'A1' => 'PAYMENT ADVICE ID',
            'B1' => 'MERCHANT ID',
            'C1' => 'MERCHANT NAME',
            'D1' => 'TOTAL AMOUNT',
            'E1' => 'PAYMENT DUE DATE',
        ];

        foreach ($expected as $cell => $label) {
            $this->assertSame(
                $label,
                $sheet->getCell($cell)->getValue(),
                "Cell {$cell} should contain header \"{$label}\""
            );
        }
    }

    public function test_export_empty_result_produces_only_header_row(): void
    {
        $wb    = $this->buildWorkbook([]);
        $sheet = $wb->getActiveSheet();

        $this->assertSame(1, $sheet->getHighestRow(),
            'An empty result set should yield exactly 1 row (header only)');
    }

    // -----------------------------------------------------------------------
    // C. Single-row data integrity
    // -----------------------------------------------------------------------

    public function test_export_single_row_all_cells_match_input(): void
    {
        $row = $this->makeRow(1, 'MER001', 'Test Merchant Inc.', 1234.56, '2024-03-31');
        $wb    = $this->buildWorkbook([$row]);
        $sheet = $wb->getActiveSheet();

        $this->assertSame('Z000001',            $sheet->getCell('A2')->getValue(), 'A2: PA ID');
        $this->assertSame('MER001',             $sheet->getCell('B2')->getValue(), 'B2: Merchant ID');
        $this->assertSame('Test Merchant Inc.', $sheet->getCell('C2')->getValue(), 'C2: Merchant Name');
        $this->assertSame('1,234.56',           $sheet->getCell('D2')->getValue(), 'D2: Total Amount');
        $this->assertSame('2024-03-31',         $sheet->getCell('E2')->getValue(), 'E2: Due Date');
    }

    public function test_export_column_f_is_empty_confirming_exactly_5_columns(): void
    {
        $row = $this->makeRow(1, 'MER001', 'Merchant', 500.00, '2024-06-30');
        $wb    = $this->buildWorkbook([$row]);
        $sheet = $wb->getActiveSheet();

        $this->assertNotEmpty($sheet->getCell('E2')->getValue(), 'Column E must have data');
        $this->assertEmpty($sheet->getCell('F2')->getValue(),    'Column F must be empty');
    }

    // -----------------------------------------------------------------------
    // D. Multi-row integrity (?search=1 returns all matching PAs)
    // -----------------------------------------------------------------------

    public function test_export_row_count_is_header_plus_data_rows(): void
    {
        $rows = [
            $this->makeRow(1, 'MER001', 'Alpha Corp',  1000.00, '2024-04-01'),
            $this->makeRow(2, 'MER002', 'Beta Ltd',    2000.00, '2024-04-02'),
            $this->makeRow(3, 'MER003', 'Gamma Inc.',  3000.00, '2024-04-03'),
        ];

        $wb    = $this->buildWorkbook($rows);
        $sheet = $wb->getActiveSheet();

        // row 1 = headers, rows 2-4 = data
        $this->assertEquals(4, $sheet->getHighestRow(),
            'Should have 1 header row + 3 data rows = 4 total rows');
    }

    public function test_export_pa_ids_appear_in_input_order(): void
    {
        $rows = [
            $this->makeRow(1,  'MER001', 'Alpha Corp', 500.00, '2024-05-01'),
            $this->makeRow(10, 'MER002', 'Beta Ltd',   600.00, '2024-05-02'),
            $this->makeRow(99, 'MER003', 'Gamma Inc.', 700.00, '2024-05-03'),
        ];

        $wb    = $this->buildWorkbook($rows);
        $sheet = $wb->getActiveSheet();

        $this->assertSame('Z000001', $sheet->getCell('A2')->getValue(), 'Row 2 PA ID');
        $this->assertSame('Z000010', $sheet->getCell('A3')->getValue(), 'Row 3 PA ID');
        $this->assertSame('Z000099', $sheet->getCell('A4')->getValue(), 'Row 4 PA ID');
    }

    public function test_export_all_five_columns_populated_for_every_row(): void
    {
        $rows = [
            $this->makeRow(5, 'MER005', 'Delta Corp', 9999.99, '2024-12-31'),
            $this->makeRow(6, 'MER006', 'Epsilon Ltd', 0.01,   '2025-01-01'),
        ];

        $wb    = $this->buildWorkbook($rows);
        $sheet = $wb->getActiveSheet();

        foreach ([2, 3] as $rowNum) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
                $val = $sheet->getCell("{$col}{$rowNum}")->getValue();
                $this->assertNotNull($val,
                    "Cell {$col}{$rowNum} must not be null");
                $this->assertNotSame('',
                    (string)$val,
                    "Cell {$col}{$rowNum} must not be empty");
            }
        }
    }

    // -----------------------------------------------------------------------
    // E. search=1 filter behaviour (exercised via multiPANUM → paNumber decode)
    // -----------------------------------------------------------------------

    /**
     * When ?search=1 is passed, multiPANUM("1") decodes to the raw integer 1
     * so the WHERE clause becomes: paH.PA_ID in (1)
     * Verify that My_lib::multiPANUM correctly decodes a PA number string.
     */
    public function test_multi_pa_num_decodes_single_pa_id(): void
    {
        // multiPANUM("1") should return "1" (numeric, decoded from Z000001 format)
        $decoded = $this->lib->multiPANUM('1');
        $this->assertSame('1', $decoded,
            'multiPANUM("1") should return "1" for a plain numeric search');
    }

    public function test_multi_pa_num_decodes_z_prefixed_pa_id(): void
    {
        // paNumber("Z000001", decode=true) → ltrim("Z000001","Z") = "000001"
        // Leading zeros are preserved; only the "Z" prefix is stripped.
        $decoded = $this->lib->multiPANUM('Z000001');
        $this->assertSame('000001', $decoded,
            'multiPANUM("Z000001") strips the Z prefix but preserves leading zeros');
    }
}
