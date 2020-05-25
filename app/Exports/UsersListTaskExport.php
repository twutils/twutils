<?php

namespace App\Exports;

use App\Follower;
use App\Following;
use App\Task;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersListTaskExport extends BaseExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithColumnFormatting
{
    use Exportable;
    use RegistersEventListeners;

    protected $task;
    protected static $tweepsUrls;

    public function __construct(Task $task)
    {
        $this->task = $task;

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

        // Set Alignment for all columns except 'H' (Bio Column)
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'I', 'J', 'K', 'L', 'M', 'N', 'O'] as $column) {
            $event->sheet->styleCells("{$column}1:{$column}${highestRow}", [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
        }

        // 'O' Column: Permalink as a Hyperlink
        foreach ($event->sheet->getColumnIterator('O', 'O') as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getRow() === 1) {
                    continue;
                }
                $cell->setHyperlink(new Hyperlink($cell->getValue(), $cell->getValue()));
            }
        }

        // 'I' Column: Tweep Url as a Hyperlink
        foreach ($event->sheet->getColumnIterator('I', 'I') as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getRow() === 1 || empty($cell->getValue())) {
                    continue;
                }

                $cell->setHyperlink(new Hyperlink(static::$tweepsUrls[$cell->getValue()], $cell->getValue()));
            }
        }

        // '1' Row: Header Styles
        $event->sheet->styleCells(
            'A1:O1',
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
        $relationColumn = $this->task->type === FetchFollowingOperation::class ? 'follows_you' : 'followed_by_me';

        return [
            'order',                  // A
            'username',               // B
            'name',                   // C

            'user_following',         // D
            'user_followers',         // E
            'user_tweets',            // F
            'user_likes',             // G

            'bio',                    // H
            'user_url',               // I
            'user_location',          // J
            'user_is_verified',       // K

            $relationColumn,          // L

            'user_joined_twitter_at', // M
            'id',                     // N
            'permalink',              // O
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,

            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,

            '',
        ];
    }

    public function collection()
    {
        if ($this->task->type === FetchFollowingOperation::class) {
            return $this->followings();
        }

        if ($this->task->type === FetchFollowersOperation::class) {
            return $this->followers();
        }

        return collect();
    }

    protected function followings()
    {
        return $this->task->followings->reverse()->values()->map(
            function (Following $followoingPivot, $key) {
                $tweep = $followoingPivot->tweep;

                static::$tweepsUrls[$tweep->display_url] = $tweep->url;

                return [
                    'order'                   => $key + 1,
                    'username'                => $this->formatText($tweep->screen_name),
                    'name'                    => $this->formatText($tweep->name),

                    'user_following'          => $tweep->friends_count,
                    'user_followers'          => $tweep->followers_count,
                    'user_tweets'             => $tweep->statuses_count,
                    'user_likes'              => $tweep->favourites_count,

                    'bio'                     => $this->formatText($tweep->description),
                    'user_url'                => $this->formatText($tweep->display_url),
                    'user_location'           => $this->formatText($tweep->location),
                    'user_is_verified'        => $tweep->verified ? 'Yes' : 'No',

                    'follows_you'             => $followoingPivot->followed_by ? 'Yes' : 'No',

                    'user_joined_twitter_at'  => $tweep->tweep_created_at,
                    'id'                      => $tweep->id_str,
                    'permalink'               => 'https://twitter.com/intent/user?user_id='.$tweep->id_str,
                ];
            }
        );
    }

    protected function followers()
    {
        return $this->task->followers->reverse()->values()->map(
            function (Follower $followerPivot, $key) {
                $tweep = $followerPivot->tweep;

                static::$tweepsUrls[$tweep->display_url] = $tweep->url;

                return [
                    'order'                   => $key + 1,
                    'username'                => $this->formatText($tweep->screen_name),
                    'name'                    => $this->formatText($tweep->name),

                    'user_following'          => $tweep->friends_count,
                    'user_followers'          => $tweep->followers_count,
                    'user_tweets'             => $tweep->statuses_count,
                    'user_likes'              => $tweep->favourites_count,

                    'bio'                     => $this->formatText($tweep->description),
                    'user_url'                => $this->formatText($tweep->display_url),
                    'user_location'           => $this->formatText($tweep->location),
                    'user_is_verified'        => $tweep->verified ? 'Yes' : 'No',

                    'followed_by_me'          => $followerPivot->followed_by_me ? 'Yes' : 'No',

                    'user_joined_twitter_at'  => $tweep->tweep_created_at,
                    'id'                      => $tweep->id_str,
                    'permalink'               => 'https://twitter.com/intent/user?user_id='.$tweep->id_str,
                ];
            }
        );
    }
}
