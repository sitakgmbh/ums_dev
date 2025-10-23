@extends('layouts.mail')

@section('header')
    <h2>Bestätigung Eröffnung</h2>
@endsection

@section('intro')
    <p>Hallo {{ $eroeffnung->vorname }} {{ $eroeffnung->nachname }}</p>
    <p>Dein Antrag zur Eröffnung wurde erfolgreich erfasst.</p>
    <p><a href="{{ url('/eroeffnungen/' . $eroeffnung->id) }}" style="color:#0d6efd; text-decoration:underline;">➔ Antrag einsehen</a></p>
@endsection

@section('body')
    @if($isSoon)
        <div style="border:1px solid #cc0000; color:#cc0000; padding:12px; margin-bottom:30px;">
            ⚠️ Hinweis: Der Eintritt erfolgt bereits am <strong>{{ \Carbon\Carbon::parse($eroeffnung->vertragsbeginn)->format('d.m.Y') }}</strong>.
        </div>
    @endif

    <p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
