<?php

namespace App\Exports;

use App\Models\Task;
use App\TwUtils\Base\Export;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Database\Eloquent\Builder;
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
use App\TwUtils\TwitterOperations\DestroyLikesOperation;
use App\TwUtils\TwitterOperations\DestroyTweetsOperation;

class TweetsListExport extends Export implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithColumnFormatting
{
    use Exportable;
    use RegistersEventListeners;

    protected Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;

        if (in_array($this->task->type, Task::TWEETS_MANAGED_DESTROY_TYPES)) {
            $this->task = Task::where('managed_by_task_id', $task->id)
                ->whereIn('type', Task::TWEETS_DESTROY_TWEETS_TYPES)
                ->sole();
        }

        $this->registerMacros();
    }

    public static function afterSheet(AfterSheet $event)
    {
        // Left Alignment for all columns except 'G' (Text Column)
        static::leftAlignColumns($event->sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'H', 'I', 'J', 'K']);

        // 'K' Column: Permalink as a Hyperlink
        static::heyperlinkColumn($event->sheet, 'K');

        // Header row Styles
        static::highlightHeader($event->sheet);

        // 'A' Column: Styles
        static::highlightColumn($event->sheet, 'A');
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
        /** var Builder */
        $tweetsQuery = $this->getTweetsQuery();

        return $tweetsQuery->get()->map(
            fn ($tweet) => [
                    'tweet_date'  => Date::dateTimeToExcel($tweet->tweet_created_at),
                    'tweet_time'  => Date::dateTimeToExcel($tweet->tweet_created_at),
                    'username'    => $this->formatText(
                        $tweet->retweeted_status ? $tweet->retweeted_status['user']['screen_name'] : $tweet->tweep->screen_name
                    ),
                    'to'          => $this->formatText($tweet->in_reply_to_screen_name),

                    'retweets'    => $tweet->retweet_count,
                    'favorites'   => $tweet->favorite_count,

                    'text'        => $this->formatText($tweet->text),

                    'mentions'    => $this->formatText($tweet->mentions),
                    'hashtags'    => $this->formatText($tweet->hashtags),
                    'id'          => $tweet->id_str.' ',

                    'permalink'   => 'https://twitter.com/'.$tweet->tweep->screen_name.'/status/'.$tweet->id_str,
                ]
        );
    }

    protected function getTweetsQuery(): Builder
    {
        return $this->getTweetsQueryFromTask($this->task);
    }

    protected function getTweetsQueryFromTask(Task $task): Builder
    {
        // First, recursively point to the targeted task
        if (
            in_array($task->type, Task::TWEETS_DESTROY_TWEETS_TYPES) &&
            ($targetedTask = $task->targetedTask)
        ) {
            return $this->getTweetsQueryFromTask($targetedTask);
        }

        if (in_array($task->type, Task::TWEETS_LISTS_LIKES_TYPES)) {
            return $task->likes()->getQuery();
        }

        if (in_array($task->type, Task::TWEETS_LISTS_USERTWEETS_TYPES)) {
            return $task->tweets()->getQuery();
        }

        if ($task->type === DestroyLikesOperation::class) {
            return $task
                ->likes()
                ->wherePivot('removed', '!=', null)
                ->getQuery();
        }

        if ($task->type === DestroyTweetsOperation::class) {
            return $task
                ->tweets()
                ->wherePivot('removed', '!=', null)
                ->getQuery();
        }
    }
}
