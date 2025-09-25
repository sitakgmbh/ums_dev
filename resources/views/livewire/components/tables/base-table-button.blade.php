<div class="d-inline-flex align-items-center gap-2">
    @foreach ($buttons as $btn)
        @php
            $idParam = $btn['idParam'] ?? 'id';
            $idValue = data_get($row, $idParam);
            $method  = $btn['method'] ?? null;
            $url     = $btn['url'] ?? null;
            $icon    = $btn['icon'] ?? 'mdi mdi-help';
            $attrs   = $btn['attrs'] ?? [];
        @endphp

        {{-- Livewire-Methode --}}
        @if($method)
            <a href="javascript:void(0)"
               wire:click="{{ $method }}({{ $idValue }})"
               class="action-icon">

                {{-- Normales Icon --}}
                <i class="{{ $icon }}"
                   wire:loading.remove
                   wire:target="{{ $method }}({{ $idValue }})"></i>

                {{-- Spinner während Action --}}
                <span class="spinner-border spinner-border-sm text-secondary"
                      role="status"
                      aria-hidden="true"
                      wire:loading
                      wire:target="{{ $method }}({{ $idValue }})"></span>
            </a>

        {{-- URL-Link --}}
        @elseif($url)
            <a href="{{ is_callable($url) ? $url($row) : $url }}"
               class="action-icon"
               @foreach($attrs as $attr => $val)
                   {{ $attr }}="{{ $val }}"
               @endforeach>
                <i class="{{ $icon }}"></i>
            </a>
        @endif
    @endforeach
</div>
