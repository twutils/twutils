<?php

namespace App\TwUtils;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Upload;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class RawTweetsManager
{
    public function create(UploadedFile $uploadedFile, User $user) : Upload
    {
        $fileName = Str::uuid().'.js';

        $uploadedFile->storeAs(Upload::UPLOADS_DIR, $fileName, ['disk' => config('filesystems.cloud')]);

        $upload = Upload::create([
            'filename'      => $fileName,
            'user_id'       => $user->id,
            'original_name' => $uploadedFile->getClientOriginalName(),
        ]);

        return $upload;
    }

    public function mapResponseToTweet(array $tweet): array
    {
        return [
            'id_str'                  => $tweet['id_str'],
            'extended_entities'       => $tweet['extended_entities'] ?? [],
            'text'                    => $tweet['full_text'],
            'retweet_count'           => $tweet['retweet_count'] ?? null,
            'favorite_count'          => $tweet['favorite_count'] ?? null,
            'tweet_created_at'        => Carbon::createFromTimeString($tweet['created_at']),
        ];
    }
}
