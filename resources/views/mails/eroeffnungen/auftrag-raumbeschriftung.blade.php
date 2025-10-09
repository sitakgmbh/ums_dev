@extends('layouts.mail')

@section('header')
    <h2 style="margin:0;">Auftrag Raumbeschriftung</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Bitte beschrifte einen Raum mit der Bezeichnung <strong>{{ $eroeffnung->raumbeschriftung }}</strong> für einen neuen Mitarbeiter:</p>
@endsection

@section('body')
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    <p>Vielen Dank und freundliche Grüsse,<br>Ihre ICT</p>
@endsection
