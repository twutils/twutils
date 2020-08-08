@php
    $activeRoute = request()->route()->getName();
@endphp
<nav class="navbar navbar-expand-md navbar-laravel fixed-top">
    <div class="container-fluid">

        @if ($activeRoute === 'app')
            <button class="navbar-toggler d-block d-md-none" type="button" data-toggle="collapse" data-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="ellipsesPart"></span>
                <span class="ellipsesPart"></span>
                <span class="ellipsesPart"></span>
            </button>
        @endif
        <div class="brandWrapper">
            <div class="p-0" style="">
                <a  class="brand text-white" href="{{ url('/') }}">
                    <img class="img-fluid" style="height: 30px;" src="{{url('images/twutlis-typo-white-small.png')}}">
                </a>
            </div>
            <small class="versionLabel">
                {{config('app.version', 'x.x.x-x')}}
            </small>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#welcomeNavbar" aria-controls="welcomeNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="ellipsesPart"></span>
            <span class="ellipsesPart"></span>
            <span class="ellipsesPart"></span>
        </button>

        <div class="collapse navbar-collapse" id="welcomeNavbar">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li>
                    <a href="{{route('switchLang',['lang'=>session()->get('locale') === 'en' ? 'ar':'en'])}}" data-turbolinks="false" class="nav-link switchLangAnchor">
                        <span class="{{(session()->get('locale') === 'en' ? 'text-muted':'')}}">EN</span>/<span class="{{(session()->get('locale') === 'ar' ? 'text-muted':'')}}">عر</span>
                    </a>
                </li>
            </ul>
            @auth
            <ul class="navbar-nav m-auto">
                <li class="{{$activeRoute === 'app' ? 'active':''}}">
                    <a href="{{route('app')}}#/" data-turbolinks="false" class="nav-link {{$activeRoute == 'app' ? 'active':''}}">
                        @if(app()->getLocale() == "en")
                            Dashboard
                        @else
                            لوحة التحكم
                        @endif
                    </a>
                </li>
            </ul>
            @endauth
            <ul class="navbar-nav m-auto">
                <li class="">
                    <a class="{{$activeRoute === 'about' ? 'active':''}} nav-link" href="{{route('about')}}">
                        @if(app()->getLocale() == "en")
                            About
                        @else
                            من نحن
                        @endif
                    </a>
                </li>
                <li class="">
                    <a class="{{$activeRoute === 'contact' ? 'active':''}} nav-link" href="{{route('contact')}}">
                        @if(app()->getLocale() == "en")
                            Contact
                        @else
                            تواصل معنا
                        @endif
                    </a>
                </li>
                <li class="">
                    <a class="{{$activeRoute === 'privacy' ? 'active':''}} nav-link" href="{{route('privacy')}}">
                        @if(app()->getLocale() == "en")
                            Privacy
                        @else
                            الخصوصية
                        @endif
                    </a>
                </li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li>
                        <a class="nav-link" href="{{route('twitter.login')}}">
                            <small>{{__('messages.login_with')}}</small> {{__('messages.twitter')}}
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown navbarDropdown__container">
                        <a class="nav-link dropdown-toggle text-white navbarDropdown__user" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="{{asset('storage/' . Auth::user()->avatar)}}" class="rounded-circle avatar">
                            <span class="mx-2">{{\Illuminate\Support\Str::limit(auth()->user()->name, 15)}}</span>
                            <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu" style="animation-duration: 0.3s;" aria-labelledby="navbarDropdown">
                            <a href="{{route('app')}}#/" data-turbolinks="false" class="dropdown-item {{$activeRoute == 'app' ? 'active':''}}">
                                @if(app()->getLocale() == "en")
                                    Dashboard
                                @else
                                    لوحة التحكم
                                @endif
                            </a>
                            <a class="dropdown-item {{$activeRoute == 'profile' ? 'active':''}}" data-turbolinks="false" href="{{ route('profile') }}">
                                {{__('messages.profile')}}
                            </a>
                            <hr>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{__('messages.logout')}}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>