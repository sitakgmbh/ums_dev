@extends('layouts.mail')

@section('header')
    <h2>Auftrag Berufskleider</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren,</p>
    <p>Bitte bereite die Berufskleidung für einen neuen Mitarbeiter vor:</p>
@endsection

@section('body')
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    <p>Vielen Dank und freundliche Gruesse,<br>Ihre ICT</p>
@endsection
