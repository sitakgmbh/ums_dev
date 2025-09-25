@extends('layouts.mail')

@section('header')
    <h2 style="margin:0;">📩 Ditto Testmail</h2>
@endsection

@section('intro')
    <p>Hallo {{ $toAddress }},</p>
    <p>dies ist eine automatisch generierte <strong>Testmail</strong> von {{ config('app.name') }}.</p>
@endsection

@section('body')
    <p>Mit dieser Nachricht kannst du überprüfen, ob dein Mailserver korrekt eingerichtet ist.</p>

    <ul>
        <li>Absender: {{ config('mail.from.address') }}</li>
        <li>Versandzeit: {{ now()->format('d.m.Y H:i') }} Uhr</li>
    </ul>

    <p style="margin-top: 1rem;">
        <a href="{{ config('app.url') }}" class="btn">Zur Startseite</a>
    </p>
@endsection

@section('outro')
    <p>Falls du diese Mail nicht erwartet hast, kannst du sie einfach ignorieren.</p>
@endsection
