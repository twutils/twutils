@php
use \App\TwUtils\UserManager;
@endphp

@extends('layout.app')

@section('content')
    <div class="container">
      <div class="py-5 text-center">
    @if($user->avatar)
        <img class="d-block mx-auto mb-4" src="{{asset('storage/'.$user->avatar)}}" alt="" width="72" height="72">
    @endif
        <h2>{{$user->username}}</h2>
      </div>
      <div class="row {{app()->getLocale() == "en" ? "ltr":"rtl"}}">
        <div class="col-md-4 order-md-2 mb-4 ">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="">{{__('messages.twitter_account')}}</span>
          </h4>
          <ul class="list-group mb-3 ltr">
            <li class="list-group-item  d-flex justify-content-between lh-condensed">
              <div>
                <h6 class="my-0"><i>@</i>{{$user->socialUser->nickname}}</h6>
                <small class="text-secondary">id: {{$user->socialUser->social_user_id}}</small>
              </div>
            </li>
          </ul>
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="">{{__('messages.activity')}}</span>
          </h4>
          <ul class="list-group mb-3">
            <li class="list-group-item  d-flex justify-content-between lh-condensed">
              <div>
                <h6 class="my-0">{{__('messages.last_login')}}</h6>
                <small class="text-secondary">{{$lastLogin->diffForHumans()}}</small>
              </div>
            </li>
            <li class="list-group-item  d-flex justify-content-between lh-condensed">
              <div>
                <h6 class="my-0">{{__('messages.registered')}}</h6>
                <small class="text-secondary">{{$user->created_at->diffForHumans()}}</small>
              </div>
            </li>
          </ul>
        </div>
        <div class="col-md-8 order-md-1">
          @if(session()->has('success'))
          <div class="alert alert-success" role="alert">
            {{session()->get('success')}}
          </div>
          @endif
          <h4 class="mb-3">{{__('messages.profile')}}</h4>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="email">{{__('validation.attributes.email')}}</label>
                <input type="text" readonly="readonly" class="form-control" id="email" name="email" placeholder="" value="{{$user->email}}">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12 my-2">
                <h5>
                  {{__('messages.twitter_connections')}}
                </h5>
              </div>
              <div class="col-sm-12 mb-3">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th></th>
                      <th><label class="text-secondary">{{__('messages.privilege')}}</label><br></th>
                      <th><label class="text-secondary">{{__('messages.created_at')}}</label><br></th>
                      <th><label class="text-secondary">{{__('messages.updated_at')}}</label><br></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                @foreach (auth()->user()->socialUsers as $socialUser)
                    <tr>
                      <td class="ltr">
                      <i>@</i>{{$socialUser->nickname}}
                      </td>
                      <td>
                      {{ $socialUser->scopeString }}
                      </td>
                      <td>
                      {{$socialUser->created_at->diffForHumans()}}
                      </td>
                      <td>
                      {{$socialUser->updated_at->diffForHumans()}}
                      </td>
                      <td>
                      @if($socialUser->token === '')
                        @php
                          $addScopeLink = '#';
                          if (implode($socialUser->scope) === 'read')
                            $addScopeLink = route('twitter.login');
                          else if (\Illuminate\Support\Str::contains(implode($socialUser->scope), 'write'))
                            $addScopeLink = route('twitter.rw.login');
                        @endphp
                          <a href="{{$addScopeLink}}" class="btn btn-outline-success">
                            {{__('messages.add')}}
                          </a>
                      @else
                      <form method="POST" action="{{route('revokeSocialUser', ['socialUser' => $socialUser->id])}}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                          {{__('messages.revoke_access')}}
                        </button>
                      </form>
                      @endif
                      </td>
                    </tr>
                @endforeach
                @foreach (['read','write'] as $scope)
                    @if(app(UserManager::class)->resolveUser(auth()->user(), $scope) === null)
                    <tr>
                      <td>
                        XXXXX
                      </td>
                      <td>
                        {{ __('messages.' . $scope) }}
                      </td>
                      <td>
                        XXXXX
                      </td>
                      <td>
                        XXXXX
                      </td>
                      <td>
                        @php
                          $addScopeLink = '#';
                          if ($scope === 'read')
                            $addScopeLink = route('twitter.login');
                          else if ($scope === 'write')
                            $addScopeLink = route('twitter.rw.login');
                        @endphp
                          <a href="{{$addScopeLink}}" class="btn btn-outline-success">
                            {{__('messages.add')}}
                          </a>
                      </td>
                    </tr>
                  @endif
                @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <hr class="mb-4">
          <h4 class="mb-3">
            {{__('messages.danger_zone')}}
          </h4>
          <form class="border p-3 border-danger deleteMe" onsubmit="return confirm('{{__('messages.confirmDeleteMe')}}');" action="{{route('deleteMe')}}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-9 mb-3">
                <h5>
                  {{__('messages.deleteMe')}}
                </h5>
                <span class="">
                  {{__('messages.deleteMe_desc')}}
                </span>
                <div class="border border-danger p-3 mt-3">
                  <span class="text-secondary">
                    {{__('messages.deleteMe_guide')}}
                    <br>
                  </span>
                  <div class="row mt-4">
                    <div class="col-sm-4">
                      <input type="number" class="form-control {{ $errors->has('day') ? ' is-invalid' : '' }}" id="day" name="day" placeholder="day" value="{{old('day',request()->get('day'))}}">
                      @if ($errors->has('day'))
                          <span class="invalid-feedback">
                              <strong>{{ $errors->first('day') }}</strong>
                          </span>
                      @endif
                    </div>
                    <div class="col-sm-4">
                      <input type="number" class="form-control {{ $errors->has('hour') ? ' is-invalid' : '' }}" id="hour" name="hour" placeholder="hour" value="{{old('hour',request()->get('hour'))}}">
                      @if ($errors->has('hour'))
                          <span class="invalid-feedback">
                              <strong>{{ $errors->first('hour') }}</strong>
                          </span>
                      @endif
                    </div>
                    <div class="col-sm-4">
                      <input type="number" class="form-control {{ $errors->has('minute') ? ' is-invalid' : '' }}" id="minute" name="minute" placeholder="minute" value="{{old('minute',request()->get('minute'))}}">
                      @if ($errors->has('minute'))
                          <span class="invalid-feedback">
                              <strong>{{ $errors->first('minute') }}</strong>
                          </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3 d-flex justify-content-center align-items-center">
                <button class="btn btn-danger mt-3" type="submit">{{__('messages.remove')}}</button>
              </div>
              @if(auth()->user()->remove_at !== null)
              <div class="col-12 d-flex justify-content-center align-items-center">
                <div class="alert alert-warning">
                  {{__('messages.deleteMe_pending')}}
                  <a href="{{route('cancelDeleteMe')}}" class="btn btn-outline-success">
                    {{__('messages.cancel')}}
                  </a>
                </div>
              </div>
              @endif
            </div>
          </form>
          <hr class="mb-4">
        </div>
      </div>
@endsection
@section('doc_end')

@endsection