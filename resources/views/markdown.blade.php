@extends('layout.welcome')

@section('content')
    <div class="mainViewContainer container bg-white px-sm-5 pt-5 line-height-25rem markdownContainer">
      @yield('before-markdown')
      {!! $content !!}
      @yield('after-markdown')
    </div>
@endsection