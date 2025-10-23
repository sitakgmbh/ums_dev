@extends('layouts.mail')

@section('header')
    <h2>Mutation abgeschlossen</h2>
@endsection

@section('intro')
	<p>Hallo</p>
    <p>Die nachfolgende Mutation wurde abgeschlossen.</p>
@endsection

@section('body')
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
