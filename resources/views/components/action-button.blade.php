@props(['action', 'class' => 'btn-primary'])

@php
    // Automatisch erkennen, ob es ein Event oder eine Methode ist
    $isJsAction = str($action)->startsWith('$'); // z. B. $dispatch('save')
    $clickAttr = $isJsAction ? $action : $action . '()';
    $target = $isJsAction ? null : $action; // nur Methoden sollen loading anzeigen
@endphp

<button type="button"
        class="btn {{ $class }}"
        wire:click="{{ $clickAttr }}"
        @if($target) wire:loading.attr="disabled" wire:target="{{ $target }}" @endif>
    
    <span @if($target) wire:loading.remove wire:target="{{ $target }}" @endif>
        {{ $slot }}
    </span>

    <span @if($target) wire:loading wire:target="{{ $target }}" @endif>
        <i class="spinner-border spinner-border-sm me-2"></i>
        Bitte warten
    </span>
</button>
