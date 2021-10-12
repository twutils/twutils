<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskAddRequest;
use Illuminate\Database\Eloquent\Builder;
use App\TwUtils\Services\RawTweetsService;
use App\TwUtils\Services\TasksService;
use App\TwUtils\Tasks\Factory as TaskFactory;
use Symfony\Component\HttpFoundation\Response;
use App\TwUtils\TwitterOperations\FetchLikesOperation;
use App\TwUtils\TwitterOperations\FetchFollowersOperation;
use App\TwUtils\TwitterOperations\FetchFollowingOperation;
use App\TwUtils\TwitterOperations\FetchUserTweetsOperation;

class TasksController extends Controller
{
    public function __construct(
        protected RawTweetsService $rawTweetsService,
        protected TasksService $tasksService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tasks = $request->user()->tasks;

        return $tasks->toArray();
    }

    public function create(TaskAddRequest $request)
    {
        $addTask = new TaskFactory($request->taskFullType, $request->settings, $request->relatedTask, auth()->user());

        return response(['ok'=> true, 'errors' => [], 'data' => ['task_id' => $addTask->getTask()->id]], Response::HTTP_OK);
    }

    // Create Upload

    // Create Task
    public function uploadTask(Request $request)
    {
        $this->validate(
            $request->merge(['purpose' => ucfirst($request->purpose)]),
            [
                'purpose'   => ['required', Rule::in(['DestroyTweets', 'DestroyLikes'])],
                'file'      => ['required', 'file', 'mimetypes:text/*'], // TODO: ',application/zip' ?
            ]
        );

        $uplaod = $this->rawTweetsService->create($request->file('file'), auth()->user(), $request->purpose);

        return $uplaod;
    }

    public function uploads(Request $request)
    {
        $this->validate(
            $request->merge(['purpose' => ucfirst($request->purpose)]),
            [
                'purpose'   => ['required', Rule::in(['DestroyTweets', 'DestroyLikes'])],
            ]
        );

        return $request->user()->uploads()->where('purpose', $request->purpose)->with(['rawTweetsFirst', 'rawTweetsLast'])->withCount('rawTweets')->get();
    }

    public function deleteUpload(Request $request, Upload $upload)
    {
        $this->authorize('delete', $upload);

        $upload->delete();

        return [];
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        return $task->load('view');
    }

    public function view(Request $request, Task $task)
    {
        $this->authorize('view', $task);
        $this->validate($request, [
            'search' => ['nullable', 'string'],
        ]);

        if (in_array($task->type, Task::TWEETS_LISTS_TYPES)) {
            return $this->getTweetsListView($request, $task);
        }

        if (in_array($task->type, Task::USERS_LISTS_TYPES)) {
            return $this->getUsersListView($request, $task);
        }

        return [];
    }

    public function getManagedTasks(Request $request, Task $task)
    {
        $this->authorize('seeManagedTasks', $task);

        return $task->managedTasks;
    }

    public function getTaskData(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        if (in_array($task->type, Task::TWEETS_LISTS_TYPES)) {
            $perPage = round($task->likes_count / 15);

            return $task->likes()
                    ->with('media.mediaFiles')
                    ->paginate($perPage < 200 ? 200 : $perPage);
        }

        if (in_array($task->type, [FetchFollowingOperation::class])) {
            $perPage = round($task->followings_count / 15);

            return $task
                    ->followings()
                    ->with('tweep')
                    ->paginate($perPage < 500 ? 500 : $perPage);
        }

        if (in_array($task->type, [FetchFollowersOperation::class])) {
            $perPage = round($task->followers_count / 15);

            return $task
                    ->followers()
                    ->with('tweep')
                    ->paginate($perPage < 500 ? 500 : $perPage);
        }

        if (in_array($task->type, Task::TWEETS_DESTROY_TWEETS_TYPES)) {
            return $task->targetedTask
                ->tweets()
                ->wherePivot('removed', '!=', null)
                ->paginate(1000);
        }

        return $task;
    }

    // TODO: test: task is not related to another ongoing task
    // TODO: test: do something to break job operations if the task is ongoing
    public function delete(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        try {
            $task->delete();

            return ['ok' => true];
        } catch (\Exception $e) {
            return ['ok' => false];
        }
    }

    public function listLikesTasks(Request $request)
    {
        return $request->user()
                ->tasks()
                ->where('type', FetchLikesOperation::class)
                ->where('status', 'completed')
                ->get();
    }

    public function listUserTweetsTasks(Request $request)
    {
        return $request->user()
                ->tasks()
                ->where('type', FetchUserTweetsOperation::class)
                ->where('status', 'completed')
                ->get();
    }

    protected function getUsersListView(Request $request, Task $task)
    {
        $this->validate($request, [
            'orderFields'       => [
                'array',
                'max:8',
                'required_with:orderDirections',
                'lte:orderDirections',
                'gte:orderDirections',
            ],
            'orderFields.*' => [
                'string',
                Rule::in([
                    'following_id',
                    'screen_name',
                    'name',
                    $task->type === FetchFollowingOperation::class ? 'followed_by' : 'followed_by_me',
                    'friends_count',
                    'followers_count',
                    'statuses_count',
                    'description',
                ]),
            ],
            'orderDirections'   => [
                'array',
                'max:8',
                'required_with:orderFields',
                'lte:orderFields',
                'gte:orderFields',
            ],
            'orderDirections.*' => [
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ]);

        $query = null;

        $relatedTableName = null;

        if (in_array($task->type, [FetchFollowingOperation::class])) {
            $query = $task->followings()->with('tweep');
            $relatedTableName = 'followings';
        }

        if (in_array($task->type, [FetchFollowersOperation::class])) {
            $query = $task->followers()->with('tweep');
            $relatedTableName = 'followers';
        }

        $totalCount = $query->count();

        if ($request->search) {
            $query = $query->whereHas('tweep', function (Builder $query) use ($request) {
                return $query->where(function (Builder $query) use ($request) {
                    foreach (['screen_name', 'name', 'description'] as $field) {
                        $query = $query->OrwhereRaw('lower('.$field.') like ?', ['%'.mb_strtolower($request->search).'%']);
                    }

                    return $query;
                });
            });
        }

        $query = $query->join('tweeps', 'tweeps.id_str', '=', $relatedTableName.'.tweep_id_str');

        foreach (($request->orderFields ?? []) as $key => $field) {
            $orderColumn = 'tweeps.'.$field;

            if ($field === 'following_id') {
                $orderColumn = $relatedTableName.'.id';
            }

            if (in_array($field, ['followed_by', 'followed_by_me'])) {
                $orderColumn = $relatedTableName.'.'.$field;
            }

            $query = $query->orderBy($orderColumn, $request->orderDirections[$key]);
        }

        return array_merge($query->paginate($request->perPage ?? 200)->toArray(), ['totalCount' => $totalCount]);
    }

    protected function getTweetsListView(Request $request, Task $task)
    {
        $this->validate($request, [
            'month'             => ['sometimes', 'integer', 'min:1', 'max:12'],
            'year'              => ['sometimes', 'integer', 'min:2006', 'max:'.now()->year],
            'searchOptions'     => ['sometimes', 'array'],
            'searchOptions.*'   => [Rule::in(['photo', 'animated_gif', 'video', 'withTextOnly'])],
            'searchKeywords'    => ['nullable', 'string'],
            'searchOnlyInMonth' => ['sometimes', 'boolean'],
        ]);

        $query = $task->getTweetsQuery();

        $selectedMonth = $request->month;
        $selectedYear = $request->year;

        if (
            $request->searchOnlyInMonth &&
            (is_null($selectedMonth) || is_null($selectedYear)) &&
            ($lastTweetData = $query->max('tweet_created_at'))
        ) {
            $lastTweetData = Carbon::parse($lastTweetData);

            $selectedMonth = $lastTweetData->startOfMonth()->format('m');
            $selectedYear = $lastTweetData->startOfMonth()->format('Y');
        }

        if (
            (empty($request->searchKeywords) || $request->searchOnlyInMonth) &&
            ! (is_null($selectedMonth) || is_null($selectedYear))
        ) {
            $startOfMonth = Carbon::parse($selectedYear.'-'.$selectedMonth);

            $query = $query->where('tweets.tweet_created_at', '>=', $startOfMonth)
                           ->where('tweets.tweet_created_at', '<', (clone $startOfMonth)->endOfMonth());
        }

        $query = $query->where(function ($query) use ($request) {
            foreach (($request->searchOptions ?? []) as $searchOption) {
                if ($searchOption === 'withTextOnly') {
                    $query = $query->OrwhereDoesntHave('media');
                    continue;
                }

                $lookupTypes = [
                    'photo'        => 'photo',
                    'animated_gif' => 'animated_gif',
                    'video'        => 'video',
                ];

                $lookup = $lookupTypes[$searchOption];

                $query = $query->OrWhereHas('media', function ($query) use ($lookup) {
                    return $query->where('type', $lookup);
                });
            }
        });

        if ($request->searchKeywords) {
            $query = $query->where(function ($query) use ($request) {
                foreach (explode(' ', $request->searchKeywords) as $keyword) {
                    $query = $query->where('text', 'like', '%'.$keyword.'%');
                }
            });
        }

        return $task->view->toArray() + $query->paginate($request->perPage ?? 200)->toArray();
    }
}
