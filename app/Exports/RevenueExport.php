<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class RevenueExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $revenues;
    protected $franchiseName;
    protected $serviceType;

    // Constructor supports franchise + service type
    public function __construct($revenues, $franchiseName = '', $serviceType = null)
    {
        $this->revenues = $revenues;
        $this->franchiseName = $franchiseName;
        $this->serviceType = $serviceType;
    }

    public function collection()
    {
        return $this->revenues;
    }

    // Title logic improved
    public function headings(): array
    {
        // Determine the title dynamically based on selected dates
        $title = "Revenues for " . $this->franchiseName;

        if ($this->serviceType && $this->serviceType !== 'All') {
            $title .= " ({$this->serviceType} - ";
        } else {
            $title .= " (";
        }

        // Filter out Grand Total row
        $revenuesWithoutTotal = $this->revenues->filter(fn($r) => ($r->formatted_date ?? '') !== 'Grand Total');

        if ($revenuesWithoutTotal->isNotEmpty()) {
            $datesFormatted = $revenuesWithoutTotal
                ->pluck('formatted_date')
                ->toArray();

            $firstDate = $datesFormatted[0];
            $lastDate = $datesFormatted[count($datesFormatted) - 1];

            if ($firstDate === $lastDate) {
                $title .= $firstDate;
            } else {
                $title .= $firstDate . ' to ' . $lastDate;
            }
        } else {
            $title .= 'No Data';
        }

        $title .= ")";

        return [
            [$title],
            ['No', 'Date', 'Total Amount'], // Column headers
        ];
    }

    // Clean + correct numbering logic
    protected $rowIndex = 0;

    public function map($rev): array
    {
        $isTotalRow = ($rev->formatted_date ?? '') === 'Grand Total';

        if (!$isTotalRow) {
            $this->rowIndex++;
        }

        $date = $rev->formatted_date
            ?? $rev->date
            ?? $rev->month
            ?? '';

        $total = isset($rev->total) ? number_format($rev->total, 2) : '';

        return [
            $isTotalRow ? '' : $this->rowIndex,
            $date,
            '₱' . $total,
        ];
    }

    // Styling & column widths Start
    public function styles(Worksheet $sheet)
    {
        // Column widths Start
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(18);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]], // Title row
            2 => ['font' => ['bold' => true]],               // Header row
        ];
        // Column widths End
    }
    // Styling & column widths End

    // Auto-bold last row (Grand Total row) Start
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A{$lastRow}:C{$lastRow}")
                    ->getFont()->setBold(true);
            },
        ];
    }
    // Auto-bold last row (Grand Total row) End

}
