@php
use \App\TwUtils\UserManager;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{asset("/apple-touch-icon.png")}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset("/favicon-32x32.png")}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset("/favicon-16x16.png")}}">

    <link rel="preload" as="image" href="{{ empty($isLocal) ? asset('images/loading.gif') : 'assets/images/loading.gif' }}">

    <link rel="manifest" href="{{asset("/site.webmanifest")}}">
    <link rel="mask-icon" href="{{asset("/safari-pinned-tab.svg")}}" color="#535353">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    @auth
        <meta name="api-token" content="{{auth()->user()->api_token}}">
    @endauth

    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <script type="text/javascript">
        @php
            $clientData = app(UserManager::class)->getClientData();
        @endphp
        window.TwUtils = @json($clientData, JSON_HEX_APOS)
    </script>
    <!-- Styles -->
    @yield('styles')
    @yield('head_end')
</head>
<body class="locale-{{app()->getLocale()}}">
    <div id="twutils" class="twutils__container">
        @if (empty($isLocal))
            @include('layout._navbar')
        @endif
        <main id="mainContainer" data-turbolinks="false" class="container-fluid">
        @if(session()->has('message'))
                @php
                    $message = session('message');
                @endphp
            <div data-hide-after="1000" class="{{app()->getLocale() === 'ar' ? 'rtl':'ltr'}} alert alert-{{$message['type']}}" role="alert">
                {{$message['message']}}
            </div>
        @endif
        @if(auth()->check() && auth()->user()->remove_at !== null)
            <account-removal></account-removal>
        @endif
            @yield('content')
        </main>
        <portal-target name="modal" multiple></portal-target>
    </div>
    <div class="loading-gif">
        <span class="loading-gif-content"></span>
    </div>

    @include('layout._footer')

    <!-- Scripts -->
    @yield('scripts')
    @yield('doc_end')
</body>
</html>
