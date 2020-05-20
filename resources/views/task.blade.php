@extends('layout.app')

@section('content')
    <task></task>
@endsection

@section('head_end')
<script>
    window.TwUtils.tasks = []

    @foreach($tasks as $key => $task)

    window.TwUtils.tasks[{{$key}}] = @json($task, JSON_HEX_APOS);

    @endforeach

    window.TwUtils.isLocal = true;

    @if (isset($managedTasks))
    window.TwUtils.managedTasks = @json($managedTasks, JSON_HEX_APOS);
    @endif
</script>
@endsection
