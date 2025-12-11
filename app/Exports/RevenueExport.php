<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;

class RevenueExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithEvents,
    ShouldAutoSize
{
    use Exportable;
    
    // We will store the calculated totals here
    protected array $totals = [];

    public function __construct(
        protected Collection $data,
        protected string $title,
        protected string $tabName
    ) {}

    /**
     * @return Collection
     */
    public function collection()
    {
        // Calculate Grand Total
        $this->totals['total_amount'] = $this->data->sum('total_amount');

        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            $this->tabName === 'franchise' ? 'Franchise' : 'Branch',
            'Date',
            'Amount'
        ];
    }

    /**
     * Maps and transforms the data for export
     */
    public function map($row): array
    {
        // 1. Resolve Name
        $nameKey = $this->tabName . '_name';
        $name = $row->$nameKey ?? 'N/A';

        // 2. Resolve Date Logic
        $dateDisplay = 'N/A';
        if (isset($row->month_name)) {
            // Monthly
            $dateDisplay = $row->month_name;
        } elseif (isset($row->week_start)) {
            // Weekly
            $start = Carbon::parse($row->week_start);
            $end = Carbon::parse($row->week_end);
            $dateDisplay = $start->format('M j') . ' - ' . $end->format('M j, Y');
        } elseif (isset($row->payment_date)) {
            // Daily (grouped by date)
            $dateDisplay = Carbon::parse($row->payment_date)->format('M j, Y');
        }

        // 3. Build Row
        return [
            $name,
            $dateDisplay,
            $row->total_amount ?? 0,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => '"₱"#,##0.00',
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // --- 1. Header Styling ---
                $sheet->insertNewRowBefore(1, 1);
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', $this->title);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A2:{$lastColumn}2")->getFont()->setBold(true);

                // --- 2. Grand Total Row ---
                $totalRowIndex = $lastRow + 2;

                // Label "GRAND TOTAL" (Merge A-B)
                $sheet->mergeCells("A{$totalRowIndex}:B{$totalRowIndex}");
                $sheet->setCellValue("A{$totalRowIndex}", "GRAND TOTAL");
                $sheet->getStyle("A{$totalRowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRowIndex}")->getAlignment()->setHorizontal('right');

                // --- 3. Fill Grand Total Amount ---
                $this->setTotalCell($sheet, 3, $totalRowIndex, $this->totals['total_amount']);
            },
        ];
    }

    /**
     * Helper to set value, bold it, and format currency
     */
    private function setTotalCell($sheet, $colIndex, $rowIndex, $value)
    {
        $colLetter = Coordinate::stringFromColumnIndex($colIndex);
        $sheet->setCellValue("{$colLetter}{$rowIndex}", $value);
        $sheet->getStyle("{$colLetter}{$rowIndex}")->getFont()->setBold(true);
        $sheet->getStyle("{$colLetter}{$rowIndex}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');
    }
}