@extends('emails.default.base')

@section('content')
    <div>{{ $displayName }} have shared <strong>{{ $itemName }}</strong> with you.</div>
    @if($emailMessage)
        <br>
        {{ $emailMessage }}
        <br>
    @endif
    <br>
    <div><a href="{{ $link }}" target="_blank">Click here to view</a>.</div>
@endsection