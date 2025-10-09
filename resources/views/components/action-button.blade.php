@props(['action', 'class' => 'btn-primary'])

<button type="button"
        class="btn {{ $class }}"
        wire:click="{{ $action }}"
        wire:loading.attr="disabled"
        wire:target="{{ $action }}">
    <span wire:loading.remove wire:target="{{ $action }}">
        {{ $slot }}
    </span>
    <span wire:loading wire:target="{{ $action }}">
        <i class="spinner-border spinner-border-sm me-2"></i>
        Bitte warten
    </span>
</button>
