@extends('layouts.mail')

@section('header')
    <h2>Auftrag Raumbeschriftung</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Bitte beschrifte einen Raum mit der Bezeichnung <strong>{{ $eroeffnung->raumbeschriftung }}</strong> für einen neuen Mitarbeiter:</p>
@endsection

@section('body')
	<p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
