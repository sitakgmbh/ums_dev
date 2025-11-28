@extends('layouts.mail')

@section('header')
    <h2>Information Leistungserbringer</h2>
@endsection

@section('intro')
    <p>Hallo</p>
	<p>Der nachfolgende Mitarbeiter wurde uns als Leistungserbringer gemeldet.</p>

@endsection

@section('body')
	<p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection