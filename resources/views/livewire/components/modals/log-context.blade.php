@extends('livewire.components.modals.base-modal')

@section('body')
    @if($log)
        
        <p><strong>Nachricht:</strong> {{ $log->message }}</p>

<h6 class="mt-3">Kontext:</h6>
<pre class="bg-light p-3 rounded"
     style="max-height: 400px; overflow-y: auto; font-size: 0.85rem; white-space: pre;">
{!! json_encode(json_decode($log->context, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</pre>



        <ul class="list-unstyled mb-0">
            <li><strong>Level:</strong> {{ $log->level instanceof \App\Enums\LogLevel ? $log->level->label() : $log->level }}</li>
			<li><strong>Kategorie:</strong> {{ $log->category instanceof \App\Enums\LogCategory ? $log->category->label() : $log->category }}</li>
            <li><strong>Erstellt am:</strong> {{ $log->created_at->format('d.m.Y H:i:s') }}</li>
        </ul>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection
