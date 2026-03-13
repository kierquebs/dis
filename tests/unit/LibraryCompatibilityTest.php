<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Library compatibility tests for PHP 8.1.2 / CI 3.1.5
 *
 * Covers:
 *  PHPExcel (bundled in application/libraries/classes/) – already patched for PHP 8.1
 *  Dompdf 0.8.3 (bundled in system/dompdf/) – Iterator #[\ReturnTypeWillChange] patches
 *
 * Decision rationale
 * ------------------
 * PHPExcel  : Kept as-is. Curly-brace and Iterator issues were fixed in earlier commits.
 *             Migrating to PhpSpreadsheet would require a non-trivial API migration
 *             (0-based → 1-based column indexing in setCellValueByColumnAndRow, class
 *             renames, writer format string changes) across a large library surface.
 *
 * Dompdf    : Patched in-place. Downloading dompdf 2.x via Composer would require
 *             enabling the CI Composer autoloader (config change) and removing the
 *             manual `require_once BASEPATH.'/dompdf/autoload.inc.php'` from four
 *             production controllers – more risk than benefit for the targeted PHP 8.1
 *             fix. The fix is surgical: add #[\ReturnTypeWillChange] to the five
 *             Iterator method signatures in FrameTreeIterator and FrameListIterator.
 */
class LibraryCompatibilityTest extends TestCase
{
    // -----------------------------------------------------------------------
    // PHPExcel tests
    // -----------------------------------------------------------------------

    /**
     * Verify PHPExcel can be instantiated and that cell values round-trip correctly.
     */
    public function test_phpexcel_creates_spreadsheet_and_sets_values(): void
    {
        $spreadsheet = new PHPExcel();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Hello');
        $sheet->setCellValue('B1', 'World');
        $sheet->setCellValue('A2', 42);

        $this->assertSame('Hello', $sheet->getCell('A1')->getValue());
        $this->assertSame('World', $sheet->getCell('B1')->getValue());
        // PHPExcel stores numbers as floats internally
        $this->assertEquals(42, $sheet->getCell('A2')->getValue());
    }

    /**
     * PHPExcel uses 0-based column indexing in setCellValueByColumnAndRow.
     * This documents the expected behaviour used throughout Download_file.php.
     */
    public function test_phpexcel_uses_0based_column_indexing(): void
    {
        $spreadsheet = new PHPExcel();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 0; // PHPExcel: 0 = column A
        foreach (['Alpha', 'Beta', 'Gamma'] as $val) {
            $sheet->setCellValueByColumnAndRow($col, 1, $val);
            $col++;
        }

        $this->assertSame('Alpha', $sheet->getCell('A1')->getValue());
        $this->assertSame('Beta',  $sheet->getCell('B1')->getValue());
        $this->assertSame('Gamma', $sheet->getCell('C1')->getValue());
    }

    /**
     * PHPExcel CSV writer must produce output containing the cell values.
     */
    public function test_phpexcel_csv_writer_produces_output(): void
    {
        $spreadsheet = new PHPExcel();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Alice');

        $writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'CSV');

        ob_start();
        $writer->save('php://output');
        $csv = ob_get_clean();

        $this->assertStringContainsString('ID', $csv);
        $this->assertStringContainsString('Name', $csv);
        $this->assertStringContainsString('Alice', $csv);
    }

    /**
     * PHPExcel Xls writer must not throw an exception.
     */
    public function test_phpexcel_xls_writer_saves_to_temp_file(): void
    {
        $spreadsheet = new PHPExcel();
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Test');

        $writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');
        $tmp = tempnam(sys_get_temp_dir(), 'phpexcel_test_') . '.xls';
        $writer->save($tmp);

        $this->assertFileExists($tmp);
        $this->assertGreaterThan(0, filesize($tmp));

        unlink($tmp);
    }

    /**
     * PHPExcel IOFactory::load must parse a file written by the Excel5 writer.
     */
    public function test_phpexcel_round_trip_write_and_read(): void
    {
        $spreadsheet = new PHPExcel();
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'RoundTrip');

        $tmp = tempnam(sys_get_temp_dir(), 'phpexcel_rt_') . '.xls';
        PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5')->save($tmp);

        $loaded = PHPExcel_IOFactory::load($tmp);
        $value  = $loaded->getActiveSheet()->getCell('A1')->getValue();

        $this->assertSame('RoundTrip', $value);

        unlink($tmp);
    }

    // -----------------------------------------------------------------------
    // Dompdf tests
    // -----------------------------------------------------------------------

    /**
     * Dompdf must instantiate without errors – confirms autoloader works and
     * the bundled 0.8.3 library is loadable on PHP 8.1+.
     */
    public function test_dompdf_instantiates_successfully(): void
    {
        $options = new \Dompdf\Options();
        $dompdf  = new \Dompdf\Dompdf($options);

        $this->assertInstanceOf(\Dompdf\Dompdf::class, $dompdf);
    }

    /**
     * FrameTreeIterator must satisfy the Iterator interface.
     * Before the patch this triggered PHP 8.1 deprecation warnings because
     * the five Iterator methods lacked #[\ReturnTypeWillChange].
     */
    public function test_dompdf_frame_tree_iterator_implements_iterator(): void
    {
        $this->assertTrue(
            in_array(Iterator::class, class_implements(\Dompdf\Frame\FrameTreeIterator::class), true),
            'FrameTreeIterator must implement Iterator'
        );
    }

    /**
     * FrameListIterator must satisfy the Iterator interface.
     */
    public function test_dompdf_frame_list_iterator_implements_iterator(): void
    {
        $this->assertTrue(
            in_array(Iterator::class, class_implements(\Dompdf\Frame\FrameListIterator::class), true),
            'FrameListIterator must implement Iterator'
        );
    }

    /**
     * All five Iterator methods on FrameTreeIterator must carry the
     * #[\ReturnTypeWillChange] attribute so PHP 8.1 does not emit
     * "Return type should be compatible" deprecation notices.
     *
     * @dataProvider frameTreeIteratorMethodProvider
     */
    public function test_dompdf_frame_tree_iterator_has_return_type_will_change(string $method): void
    {
        $this->assertIteratorMethodHasAttribute(\Dompdf\Frame\FrameTreeIterator::class, $method);
    }

    public static function frameTreeIteratorMethodProvider(): array
    {
        return [
            'rewind'  => ['rewind'],
            'valid'   => ['valid'],
            'key'     => ['key'],
            'current' => ['current'],
            'next'    => ['next'],
        ];
    }

    /**
     * All five Iterator methods on FrameListIterator must carry the
     * #[\ReturnTypeWillChange] attribute.
     *
     * @dataProvider frameListIteratorMethodProvider
     */
    public function test_dompdf_frame_list_iterator_has_return_type_will_change(string $method): void
    {
        $this->assertIteratorMethodHasAttribute(\Dompdf\Frame\FrameListIterator::class, $method);
    }

    public static function frameListIteratorMethodProvider(): array
    {
        return [
            'rewind'  => ['rewind'],
            'valid'   => ['valid'],
            'key'     => ['key'],
            'current' => ['current'],
            'next'    => ['next'],
        ];
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function assertIteratorMethodHasAttribute(string $class, string $method): void
    {
        $ref        = new ReflectionMethod($class, $method);
        $attributes = $ref->getAttributes(ReturnTypeWillChange::class);

        $this->assertNotEmpty(
            $attributes,
            sprintf(
                '%s::%s() must have #[\\ReturnTypeWillChange] to suppress PHP 8.1 Iterator deprecation',
                $class,
                $method
            )
        );
    }
}
