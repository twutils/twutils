<?php

namespace App\TwUtils\Base;

use Closure;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Export
{
    protected static function leftAlignColumns(Sheet $sheet, array $columns = []): void
    {
        $highestRow = (int) $sheet->getHighestRow();

        foreach ($columns as $column) {
            $sheet->styleCells("{$column}1:{$column}${highestRow}", [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
        }
    }

    protected static function heyperlinkColumn(Sheet $sheet, string $column, ?int $skipRow = null, ?Closure $urlCallback = null): void
    {
        foreach ($sheet->getColumnIterator($column, $column) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getRow() === $skipRow || empty($cell->getValue())) {
                    continue;
                }

                $cell->setHyperlink(new Hyperlink(($urlCallback ? $urlCallback($cell->getValue()) : $cell->getValue()), $cell->getValue()));
            }
        }
    }

    protected static function highlightColumn(Sheet $sheet, string $column): void
    {
        $sheet->styleCells(
            "{$column}2:{$column}".$sheet->getHighestRow(),
            [
                'borders' => [
                    'allBorders' => static::highlightedBordersStyle(),
                    'top'        => static::headerBorderStyle(),
                ],
                'fill' => static::highlightedFillStyle(),
            ]
        );
    }

    protected static function headerBorderStyle(): array
    {
        return [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color'       => ['rgb' => '8A8F8A'],
        ];
    }

    protected static function headerFillStyle(): array
    {
        return [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'BEC0BE',
            ],
            'endColor' => [
                'rgb' => 'BEC0BE',
            ],
        ];
    }

    protected static function highlightedBordersStyle(): array
    {
        return [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color'       => ['rgb' => 'A8A8A8'],
        ];
    }

    protected static function highlightedFillStyle(): array
    {
        return [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'DCDCDC',
            ],
            'endColor' => [
                'rgb' => 'DCDCDC',
            ],
        ];
    }

    protected function registerMacros(): void
    {
        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });
    }

    public function formatText($text): ?string
    {
        if (Str::startsWith($text, '=')) {
            $text = Str::start($text, '\'');
        }

        return $text;
    }
}
