@extends('layouts.mail')

@section('header')
    <h2>Auftrag Berufskleider</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Bitte bereite die Berufskleidung für einen neuen Mitarbeiter vor:</p>
@endsection

@section('body')
	<p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
