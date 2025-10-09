@extends('layouts.mail')

@section('header')
    <h2 style="margin:0;">Bestätigung Mutation</h2>
@endsection

@section('intro')
    <p>Hallo {{ $mutation->vorname }} {{ $mutation->nachname }},</p>
    <p>Dein Antrag zur Mutation wurde erfolgreich erfasst.</p>
    <p>
        <a href="{{ url('/mutationen/' . $mutation->id) }}"
           style="color:#0d6efd; text-decoration:underline;">
            ➔ Antrag einsehen
        </a>
    </p>
@endsection


@section('body')
    @if($isSoon)
        <div style="border:1px solid #cc0000; color:#cc0000; padding:12px; margin-bottom:16px;">
            ⚠️ Hinweis: Die Änderung erfolgt bereits am 
            <strong>{{ \Carbon\Carbon::parse($mutation->vertragsbeginn)->format('d.m.Y') }}</strong>.
        </div>
    @endif

    <p><strong>Wichtige Daten zum Antrag:</strong></p>

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:200px; padding:2px 6px; font-weight:bold;">Vorname</td>
            <td style="padding:2px 6px;">{{ $mutation->vorname }}</td>
        </tr>
        <tr>
            <td style="width:200px; padding:2px 6px; font-weight:bold;">Nachname</td>
            <td style="padding:2px 6px;">{{ $mutation->nachname }}</td>
        </tr>
        <tr>
            <td style="width:200px; padding:2px 6px; font-weight:bold;">Vertragsbeginn</td>
            <td style="padding:2px 6px;">{{ optional($mutation->vertragsbeginn)->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Arbeitsort</td>
            <td style="padding:2px 6px;">{{ $mutation->arbeitsort?->name }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Unternehmenseinheit</td>
            <td style="padding:2px 6px;">{{ $mutation->unternehmenseinheit?->name }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Abteilung</td>
            <td style="padding:2px 6px;">{{ $mutation->abteilung?->name }}</td>
        </tr>
        @if($mutation->abteilung2)
            <tr>
                <td style="padding:2px 6px; font-weight:bold;">Zweite Abteilung</td>
                <td style="padding:2px 6px;">{{ $mutation->abteilung2?->name }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Funktion</td>
            <td style="padding:2px 6px;">{{ $mutation->funktion?->name }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Antragsteller</td>
            <td style="padding:2px 6px;">
                {{ $mutation->antragsteller?->name }} ({{ $mutation->antragsteller?->email }})
            </td>
        </tr>
    </table>
@endsection


@section('outro')
    <p>Liebe Grüsse,<br>deine ICT</p>
@endsection
