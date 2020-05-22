<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TasksExport extends BaseExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithColumnFormatting
{
    use Exportable;
    use RegistersEventListeners;

    protected $tweets;

    public function __construct(Collection $tweets)
    {
        $this->tweets = $tweets;

        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });
    }

    public static function beforeWriting(BeforeWriting $event)
    {
    }

    public static function beforeSheet(BeforeSheet $event)
    {
    }

    public static function afterSheet(AfterSheet $event)
    {
        // Definitions:
        $highestRow = $event->sheet->getHighestRow();

        $headerBorderStyle = [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color'       => ['rgb' => '8A8F8A'],
        ];

        // Set Alignment for all columns except 'F' (Text Column)
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'J', 'K'] as $column) {
            $event->sheet->styleCells("{$column}1:{$column}${highestRow}", [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
        }

        // 'K' Column: Permalink as a Hyperlink
        foreach ($event->sheet->getColumnIterator('K', 'K') as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $cell->setHyperlink(new Hyperlink($cell->getValue(), $cell->getValue()));
            }
        }

        // '1' Row: Header Styles
        $event->sheet->styleCells(
            'A1:K1',
            [
                'borders' => [
                    'allBorders' => $headerBorderStyle,
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'BEC0BE',
                    ],
                    'endColor' => [
                        'rgb' => 'BEC0BE',
                    ],
                ],
            ]
        );

        // 'A' Column: Styles
        $event->sheet->styleCells(
            'A2:A'.$event->sheet->getHighestRow(),
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => 'A8A8A8'],
                    ],
                    'top' => $headerBorderStyle,
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'DCDCDC',
                    ],
                    'endColor' => [
                        'rgb' => 'DCDCDC',
                    ],
                ],
            ]
        );
    }

    public function headings(): array
    {
        return [
            'date',
            'time',
            'username',
            'to',
            'retweets',
            'favorites',
            'text',
            'mentions',
            'hashtags',
            'id',
            'permalink',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'B' => NumberFormat::FORMAT_DATE_TIME1,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function collection()
    {
        $tweets = $this->tweets->map(
            function ($like) {
               return [
                   'tweet_date'  => Date::dateTimeToExcel($like->tweet_created_at),
                   'tweet_time'  => Date::dateTimeToExcel($like->tweet_created_at),
                   'username'    => $this->formatText($like->retweeted_status ? $like->retweeted_status['user']['screen_name'] : $like->tweep->screen_name),
                   'to'          => $this->formatText($like->in_reply_to_screen_name),

                   'retweets'    => $like->retweet_count,
                   'favorites'   => $like->favorite_count,

                   'text'        => $this->formatText($like->text),

                   'mentions'    => $this->formatText($like->mentions),
                   'hashtags'    => $this->formatText($like->hashtags),
                   'id'          => $like->id_str.' ',

                   'permalink'   => 'https://twitter.com/'.$like->tweep->screen_name.'/status/'.$like->id_str,
               ];
           }
        );

        return $tweets;
    }
}
