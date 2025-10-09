@extends('layouts.mail')

@section('header')
    <h2 style="margin:0;">Bestätigung Eröffnung</h2>
@endsection

@section('intro')
    <p>Hallo {{ $eroeffnung->vorname }} {{ $eroeffnung->nachname }},</p>
    <p>Dein Antrag zur Eröffnung wurde erfolgreich erfasst.</p>
    <p>
        <a href="{{ url('/eroeffnungen/' . $eroeffnung->id) }}"
           style="color:#0d6efd; text-decoration:underline;">
            ➔ Antrag einsehen
        </a>
    </p>
@endsection


@section('body')
    @if($isSoon)
        <div style="border:1px solid #cc0000; color:#cc0000; padding:12px; margin-bottom:16px;">
            ⚠️ Hinweis: Der Eintritt erfolgt bereits am 
            <strong>{{ \Carbon\Carbon::parse($eroeffnung->vertragsbeginn)->format('d.m.Y') }}</strong>.
        </div>
    @endif

    <p><strong>Wichtige Daten zum Antrag:</strong></p>

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:200px; padding:2px 6px; font-weight:bold;">Vorname</td>
            <td style="padding:2px 6px;">{{ $eroeffnung->vorname }}</td>
        </tr>
        <tr>
            <td style="width:200px; padding:2px 6px; font-weight:bold;">Nachname</td>
            <td style="padding:2px 6px;">{{ $eroeffnung->nachname }}</td>
        </tr>
        <tr>
            <td style="width:200px; padding:2px 6px; font-weight:bold;">Vertragsbeginn</td>
            <td style="padding:2px 6px;">{{ optional($eroeffnung->vertragsbeginn)->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Arbeitsort</td>
            <td style="padding:2px 6px;">{{ $eroeffnung->arbeitsort?->name }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Unternehmenseinheit</td>
            <td style="padding:2px 6px;">{{ $eroeffnung->unternehmenseinheit?->name }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Abteilung</td>
            <td style="padding:2px 6px;">{{ $eroeffnung->abteilung?->name }}</td>
        </tr>
        @if($eroeffnung->abteilung2)
            <tr>
                <td style="padding:2px 6px; font-weight:bold;">Zweite Abteilung</td>
                <td style="padding:2px 6px;">{{ $eroeffnung->abteilung2?->name }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Funktion</td>
            <td style="padding:2px 6px;">{{ $eroeffnung->funktion?->name }}</td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Antragsteller</td>
            <td style="padding:2px 6px;">
                {{ $eroeffnung->antragsteller?->name }} ({{ $eroeffnung->antragsteller?->email }})
            </td>
        </tr>
        <tr>
            <td style="padding:2px 6px; font-weight:bold;">Bezugsperson</td>
            <td style="padding:2px 6px;">
                {{ $eroeffnung->bezugsperson?->name }} ({{ $eroeffnung->bezugsperson?->email }})
            </td>
        </tr>
    </table>
@endsection


@section('outro')
    <p>Liebe Grüsse,<br>deine ICT</p>
@endsection
