<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">🔑 API Key</x-slot>

        @if (! $editMode)
            {{-- Token Row --}}
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                <code style="flex:1; background:var(--gray-950,#0d0d0d); border:1px solid var(--gray-700,#374151); border-radius:6px; padding:6px 10px; font-size:12px; color:#d1d5db; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $revealed ? ($this->getToken() ?? '—') : $this->getMaskedToken() }}
                </code>
                <button wire:click="toggleReveal"
                    style="background:none; border:1px solid var(--gray-700,#374151); border-radius:6px; padding:5px 8px; cursor:pointer; color:#9ca3af; display:inline-flex; align-items:center;"
                    title="{{ $revealed ? 'Sembunyikan' : 'Tampilkan' }}">
                    @if($revealed)
                        <x-filament::icon icon="heroicon-o-eye-slash" style="width:14px;height:14px;" />
                    @else
                        <x-filament::icon icon="heroicon-o-eye" style="width:14px;height:14px;" />
                    @endif
                </button>
                <button x-data x-on:click="navigator.clipboard.writeText('{{ $this->getToken() }}').then(() => { $el.textContent='✓'; setTimeout(()=>{ $el.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><rect x=\'9\' y=\'9\' width=\'13\' height=\'13\' rx=\'2\' ry=\'2\'></rect><path d=\'M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1\'></path></svg>'; }, 1500) })"
                    style="background:none; border:1px solid var(--gray-700,#374151); border-radius:6px; padding:5px 8px; cursor:pointer; color:#9ca3af; display:inline-flex; align-items:center;"
                    title="Copy token">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
            </div>

            {{-- Actions Row --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                <x-filament::button
                    wire:click="generateToken"
                    wire:confirm="Generate token baru? Token lama tidak bisa digunakan lagi."
                    icon="heroicon-o-arrow-path"
                    size="sm"
                    color="warning"
                    outlined>
                    Generate Baru
                </x-filament::button>

                <x-filament::button
                    wire:click="startEdit"
                    icon="heroicon-o-pencil-square"
                    size="sm"
                    color="gray"
                    outlined>
                    Custom
                </x-filament::button>

                <a href="{{ route('docs.api') }}" target="_blank"
                   style="font-size:12px; color:rgb(var(--primary-500,245 158 11)); text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-left:auto;">
                    <x-filament::icon icon="heroicon-o-book-open" style="width:14px;height:14px;" />
                    Buka Docs
                </a>
            </div>

        @else
            {{-- Edit Mode --}}
            <div style="margin-bottom:10px;">
                <p style="font-size:12px; color:#6b7280; margin-bottom:6px;">Token custom (min. 16 karakter):</p>
                <x-filament::input.wrapper>
                    <x-filament::input wire:model="customToken" type="text" placeholder="token-custom..." autofocus />
                </x-filament::input.wrapper>
                @error('customToken')
                    <p style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</p>
                @enderror
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <x-filament::button wire:click="saveCustomToken" icon="heroicon-o-check" size="sm" color="success">Simpan</x-filament::button>
                    <x-filament::button wire:click="cancelEdit" icon="heroicon-o-x-mark" size="sm" color="gray" outlined>Batal</x-filament::button>
                </div>
            </div>
        @endif

        <p style="font-size:11px; color:#6b7280; margin:0;">
            Pakai sebagai <code style="background:#1f2937; padding:1px 5px; border-radius:3px; color:#f59e0b;">Authorization: Bearer</code> header.
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
