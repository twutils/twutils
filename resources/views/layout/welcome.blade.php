@extends('layout.master')

@section('styles')
    <link href="{{ mix('css/welcome.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script data-turbolinks-eval="false" src="{{ mix('js/manifest.js') }}"></script>
    <script data-turbolinks-eval="false" src="{{ mix('js/vendor.js') }}"></script>
    <script data-turbolinks-eval="false" src="{{ mix('js/welcome.js') }}"></script>
@endsection