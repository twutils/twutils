@extends('layout.welcome')

@section('content')
    <div class="mainViewContainer container bg-white px-sm-5 pt-5">
      <div class="animated stretchXTop">
        @include('layout._header')

        @include('layout._divider')

        @include('welcome._what_is_it')

        @include('layout._divider')

        @include('welcome._features')

        @include('layout._divider')

        @include('welcome._how_it_works')

        @include('layout._divider')
        
        <div class="w-100 my-3"></div>
        @guest

        <div class="row callToAction__container p-4">
          <div class="col-md-8 text-center">
            <p>{{__('messages.call_to_action_desc')}}</p>
          </div>
          <div class="col-md-4">
            <a class="btn btn-lg callToAction__button" href="{{route('twitter.login')}}">
              <small>{{__('messages.login_with')}}</small> {{__('messages.twitter')}}
            </a>
          </div>
        </div>

        @endguest
      </div>
    </div>
@endsection