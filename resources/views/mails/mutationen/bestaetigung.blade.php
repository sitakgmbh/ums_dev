@extends('layouts.mail')

@section('header')
    <h2>Bestätigung Mutation</h2>
@endsection

@section('intro')
    <p>Hallo {{ $mutation->antragsteller->firstname }}</p>
    <p>Dein Antrag zur Mutation wurde erfolgreich erfasst.</p>
    <p><a href="{{ url('/mutationen/' . $mutation->id) }}" style="color:#0d6efd; text-decoration:underline;">➔ Antrag einsehen</a></p>
@endsection

@section('body')
    @if($isSoon)
        <div style="border:1px solid #cc0000; color:#cc0000; padding:12px; margin-bottom:16px;">
            ⚠️ Hinweis: Die Änderung erfolgt bereits am <strong>{{ \Carbon\Carbon::parse($mutation->vertragsbeginn)->format('d.m.Y') }}</strong>.
        </div>
    @endif

    <p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
