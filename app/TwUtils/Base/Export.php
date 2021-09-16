<?php

namespace App\TwUtils\Base;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Events\Event;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Export
{
    protected static function leftAlignColumns(Event $event, array $columns = [])
    {
        $highestRow = (int) $event->sheet->getHighestRow();

        foreach ($columns as $column) {
            $event->sheet->styleCells("{$column}1:{$column}${highestRow}", [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
        }
    }

    protected function registerMacros()
    {
        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });
    }

    public function formatText($text)
    {
        if (Str::startsWith($text, '=')) {
            $text = Str::start($text, '\'');
        }

        return $text;
    }
}
