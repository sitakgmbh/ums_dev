@extends('layouts.mail')

@section('header')
    <h2>Auftrag Garderobe</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Bitte richte eine Garderobe für einen neuen Mitarbeiter ein:</p>
@endsection

@section('body')
	<p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
