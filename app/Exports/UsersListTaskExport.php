<?php

namespace App\Exports;

use App\Models\Task;
use App\TwUtils\Base\Export;
use Maatwebsite\Excel\Events\AfterSheet;
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

abstract class UsersListTaskExport extends Export implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithColumnFormatting
{
    use Exportable;
    use RegistersEventListeners;

    public const COLUMN_ORDER = 'A';

    public const COLUMN_USERNAME = 'B';

    public const COLUMN_NAME = 'C';

    public const COLUMN_USER_FOLLOWING = 'D';

    public const COLUMN_USER_FOLLOWERS = 'E';

    public const COLUMN_USER_TWEETS = 'F';

    public const COLUMN_USER_LIKES = 'G';

    public const COLUMN_BIO = 'H';

    public const COLUMN_USER_URL = 'I';

    public const COLUMN_USER_LOCATION = 'J';

    public const COLUMN_USER_IS_VERIFIED = 'K';

    public const COLUMN_RELATION_COLUMN = 'L';

    public const COLUMN_USER_JOINED_TWITTER_AT = 'M';

    public const COLUMN_ID = 'N';

    public const COLUMN_PERMALINK = 'O';

    public const ALL_COLUMNS = [
        self::COLUMN_ORDER,
        self::COLUMN_USERNAME,
        self::COLUMN_NAME,
        self::COLUMN_USER_FOLLOWING,
        self::COLUMN_USER_FOLLOWERS,
        self::COLUMN_USER_TWEETS,
        self::COLUMN_USER_LIKES,
        self::COLUMN_BIO,
        self::COLUMN_USER_URL,
        self::COLUMN_USER_LOCATION,
        self::COLUMN_USER_IS_VERIFIED,
        self::COLUMN_RELATION_COLUMN,
        self::COLUMN_USER_JOINED_TWITTER_AT,
        self::COLUMN_ID,
        self::COLUMN_PERMALINK,
    ];

    protected Task $task;

    protected static $tweepsUrls;

    public function __construct(Task $task)
    {
        $this->task = $task;

        $this->registerMacros();
    }

    public static function afterSheet(AfterSheet $event)
    {
        // Left Alignment for all columns except 'H' (Bio Column)
        static::leftAlignColumns(
            $event->sheet,
            array_filter(static::ALL_COLUMNS, fn (string $column) => $column !== static::COLUMN_BIO),
        );

        // 'O' Column: Permalink as a Hyperlink, skip first row
        static::heyperlinkColumn($event->sheet, static::COLUMN_PERMALINK, 1);

        // 'I' Column: Tweep Url as a Hyperlink, but retrieve the real url instead of 'display_url'
        static::heyperlinkColumn($event->sheet, static::COLUMN_USER_URL, 1, fn ($cellValue) => static::$tweepsUrls[$cellValue]);

        // Highlight first row
        static::highlightHeader($event->sheet);

        // Highlight first column
        static::highlightColumn($event->sheet, static::COLUMN_ORDER);
    }

    abstract protected function getRelationColumn(): string;

    public function headings(): array
    {
        return [
            'order',                    // A
            'username',                 // B
            'name',                     // C

            'user_following',           // D
            'user_followers',           // E
            'user_tweets',              // F
            'user_likes',               // G

            'bio',                      // H
            'user_url',                 // I
            'user_location',            // J
            'user_is_verified',         // K

            $this->getRelationColumn(), // L

            'user_joined_twitter_at',   // M
            'id',                       // N
            'permalink',                // O
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
        ];
    }
}
