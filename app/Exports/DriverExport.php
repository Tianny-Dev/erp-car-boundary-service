<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;

class DriverExport implements
    FromCollection,
    WithHeadings,
    WithEvents,
    ShouldAutoSize,
    WithStyles
{
    use Exportable;

    /**
     * @param Collection $data The transformed data to export.
     * @param array $headings The column headings.
     * @param string $title The main title for the document.
     */
    public function __construct(
        protected Collection $data,
        protected array $headings,
        protected string $title
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $columnCount = count($this->headings);
                $lastColumnLetter = Coordinate::stringFromColumnIndex($columnCount);

                $totalRows = $this->data->count() + 2; // +1 title, +1 header
                $grandTotalRow = $totalRows; // last row

                // Define the starting column for currency (4th column, which is 'D')
                $startCurrencyColumnIndex = 4;
                $startCurrencyColumnLetter = Coordinate::stringFromColumnIndex($startCurrencyColumnIndex);

                // Define the range for all currency data and the grand total amounts
                $currencyRange = "{$startCurrencyColumnLetter}3:{$lastColumnLetter}{$grandTotalRow}";

                // --- BOLDING: Insert and style the Title ---
                $event->sheet->insertNewRowBefore(1, 1);
                // Merge title across all columns (made dynamic)
                $event->sheet->mergeCells("A1:{$lastColumnLetter}1");
                $event->sheet->setCellValue('A1', $this->title);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // --- BOLDING: Header row styling ---
                // Style headers across all columns (made dynamic)
                $event->sheet->getStyle("A2:{$lastColumnLetter}2")->getFont()->setBold(true);

                // **Currency Formatting (Philippine Peso) and Comma Separation**
                // Applies to all data rows + grand total in columns D through the last column.
                // IMPORTANT: The data in these columns MUST be raw numeric types.
                $event->sheet->getStyle($currencyRange)
                    ->getNumberFormat()
                    // Custom format: "₱" + comma separation + two decimal places
                    ->setFormatCode('"₱"#,##0.00');

                // --- BOLDING: Bold the grand total row ---
                // Bold across all columns (made dynamic)
                $event->sheet->getStyle("A{$grandTotalRow}:{$lastColumnLetter}{$grandTotalRow}")
                    ->getFont()
                    ->setBold(true);

                // Right align all currency columns
                $event->sheet->getStyle($currencyRange)
                    ->getAlignment()
                    ->setHorizontal('right');
            },
        ];
    }


    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet): void
    {
        // Calculate dynamic row indices
        $lastRow = $this->data->count() + 2; // +1 title, +1 heading

        // Define the column where currency starts (4th column, which is 'D')
        $startCurrencyColumnIndex = 4;

        // Calculate the last column for the 'GRAND TOTAL' label merge (Column C, index 3)
        // This ensures the GRAND TOTAL label ends right before the currency values begin in column D.
        $mergeColumnLetter = Coordinate::stringFromColumnIndex($startCurrencyColumnIndex - 1);

        // Merge the non-currency cells (A to C) in the grand total row for the 'GRAND TOTAL' label
        $sheet->mergeCells("A{$lastRow}:{$mergeColumnLetter}{$lastRow}");

        // Align the grand total label to the right of the merged cell area
        $sheet->getStyle("A{$lastRow}")
            ->getAlignment()
            ->setHorizontal('right');

        // -------------------------------------------------------------------------------------
        // CRITICAL NOTE: If the Grand Total *values* are misaligned (e.g., Driver Earning total
        // appears under the Bank column), the data is being passed to this export in the wrong
        // column order.
        //
        // FIX: Check your controller/service where you create the Grand Total row to ensure
        // the columns are ordered exactly as they appear in the headings:
        // [ 'GRAND TOTAL', '', '', <Amount>, <Bank>, <Markup Fee>, ... ]
        // -------------------------------------------------------------------------------------
    }
}
