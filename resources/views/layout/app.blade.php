@extends('layout.master')

@section('styles')
    <link href="{{ empty($isLocal) ? mix('css/app.css') : 'assets/build_css/app.css' }}" rel="stylesheet">
@endsection

@section('scripts')
    <script src="{{ empty($isLocal) ? mix('js/manifest.js') : 'assets/js/manifest.js' }}"></script>
    <script src="{{ empty($isLocal) ? mix('js/vendor.js') : 'assets/js/vendor.js' }}"></script>
    <script src="{{ empty($isLocal) ? mix('js/app.js') : 'assets/js/app.js' }}"></script>
@endsection