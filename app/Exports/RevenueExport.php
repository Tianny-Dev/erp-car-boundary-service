<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;

class RevenueExport implements
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
                $totalRows = $this->data->count() + 2; // +1 title, +1 header
                $grandTotalRow = $totalRows; // last row

                // Insert Title
                $event->sheet->insertNewRowBefore(1, 1);
                $event->sheet->mergeCells('A1:C1');
                $event->sheet->setCellValue('A1', $this->title);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Header row styling
                $event->sheet->getStyle('A2:C2')->getFont()->setBold(true);

                // Currency formatting (ALL rows including grand total)
                $event->sheet->getStyle("C3:C{$grandTotalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('₱#,##0.00');

                // Bold the grand total row
                $event->sheet->getStyle("A{$grandTotalRow}:C{$grandTotalRow}")
                    ->getFont()
                    ->setBold(true);

                // Right align amount column
                $event->sheet->getStyle("C3:C{$grandTotalRow}")
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
        // Align the amount column to the right
        $lastRow = $this->data->count() + 2; // +1 heading, +1 title
        $sheet->getStyle("C3:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal('right');

        // Align the grand total label
        $sheet->getStyle("A" . ($lastRow - 1))
            ->getAlignment()
            ->setHorizontal('right');
    }
}