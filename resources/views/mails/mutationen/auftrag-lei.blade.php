@extends('layouts.mail')

@section('header')
    <h2>Information Leistungserbringer</h2>
@endsection

@section('intro')
    <p>Hallo</p>
	<p>Der nachfolgende Mitarbeiter wurde uns als Leistungserbringer gemeldet.</p>
	
2
@endsection

@section('body')
    <p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
