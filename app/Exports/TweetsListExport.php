<?php

namespace App\Exports;

use App\TwUtils\Base\Export;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class TweetsListExport extends Export implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithColumnFormatting
{
    use Exportable;
    use RegistersEventListeners;

    protected $tweets;

    public function __construct(Collection $tweets)
    {
        $this->tweets = $tweets;

        $this->registerMacros();
    }

    public static function afterSheet(AfterSheet $event)
    {
        // Left Alignment for all columns except 'G' (Text Column)
        static::leftAlignColumns($event, ['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'J', 'K']);

        // 'K' Column: Permalink as a Hyperlink
        static::heyperlinkColumn($event, 'K');

        // '1' Row: Header Styles
        $event->sheet->styleCells(
            'A1:K1',
            [
                'borders' => [
                    'allBorders' => static::headerBorderStyle(),
                ],
                'fill' => static::headerFillStyle(),
            ]
        );

        // 'A' Column: Styles
        $event->sheet->styleCells(
            'A2:A'.$event->sheet->getHighestRow(),
            [
                'borders' => [
                    'allBorders' => static::highlightedBordersStyle(),
                    'top'        => static::headerBorderStyle(),
                ],
                'fill' => static::highlightedFillStyle(),
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
