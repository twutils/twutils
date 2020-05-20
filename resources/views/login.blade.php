@extends('layout.welcome')

@section('content')
<div class="row pt-5 loginContainer justify-content-center">
    <div class="col-md-8">
        <div class="card card-default">
            <div class="card-header">{{__('messages.login')}}</div>
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-md-12">
                        {{__('messages.login_with') }} {{__('messages.twitter')}} {{__('messages.will_start')}} <span class="conunter">10</span> {{__('messages.seconds')}}..
                        <hr>
                        <a href="{{route('twitter.login')}}" class="btn btn-success loginButton">
                            <small>{{__('messages.login_with') }}</small> {{__('messages.twitter') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('doc_end')
<script>
    var counterInterval = setInterval(function() {
        var element = document.querySelector('.conunter')
        var counter = parseInt(element.textContent) || 10
        if(counter === 1) {
            clearInterval(counterInterval)
            window.location.href = "{{route('twitter.login')}}";
        } else {
            element.textContent = counter -1
        }
    }, 1000)
</script>
@endsection
