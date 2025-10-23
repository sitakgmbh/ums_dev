@extends('layouts.mail')

@section('header')
    <h2>Testmail</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Dies ist eine automatisch generierte <strong>Testmail</strong> von {{ config('app.name') }} ({{ config('mail.from.address') }}).</p>
@endsection

@section('body')
    <p>Falls du diese Mail nicht erwartet hast, kannst du sie einfach ignorieren.</p>
@endsection

@section('outro')
@endsection
