{{-- Meta tags --}}

{{--{{dd(config('devschat.path'))}}--}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="route" content="{{ $route }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('').'/'.config('devschat.path') }}" data-user="{{ Auth::user()->id }}">

{{-- scripts --}}
<script src="{{ asset('js/devschat/font.awesome.min.js') }}"></script>
<script src="{{ asset('js/devschat/autosize.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>
<script src="https://cdn.socket.io/socket.io-3.0.1.min.js"></script>


{{-- styles --}}
<link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css'/>
<link href="{{ asset('css/devschat/style.css') }}" rel="stylesheet" />
<link href="{{ asset('css/devschat/'.$dark_mode.'.mode.css') }}" rel="stylesheet" />
<link href="{{ asset('css/app.css') }}" rel="stylesheet" />

{{-- Messenger Color Style--}}
@include('DevsFort::layouts.messengerColor')