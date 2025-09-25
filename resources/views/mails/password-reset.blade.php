@extends('layouts.mail')

@section('header')
    <h2 style="margin:0;">🔑 Passwort zurücksetzen</h2>
@endsection

@section('intro')
    <p>Hallo {{ $user->firstname ?? $user->name }},</p>
    <p>du erhältst diese Nachricht, weil für dein Konto bei <strong>{{ config('app.name') }}</strong>
       ein Zurücksetzen des Passworts angefordert wurde.</p>
@endsection

@section('body')
    <p>Klicke auf den folgenden Link, um ein neues Passwort festzulegen:</p>

    <p style="margin-top: 1rem;">
        <a href="{{ $url }}" class="btn">Passwort zurücksetzen</a>
    </p>

    <p>Dieser Link ist <strong>{{ config('auth.passwords.users.expire') }} Minuten</strong> gültig.</p>
@endsection

@section('outro')
    <p>Falls du keinen Link zum Zurücksetzen deines Passworts angefordert hast, kannst du diese E-Mail ignorieren.</p>
@endsection
