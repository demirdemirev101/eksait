<x-filament-panels::page>
    <div
        @if ($this->shouldPollBankTransferConfirmation())
            wire:poll.5s="pollOrderState"
        @elseif ($this->shouldPollShipmentStatus())
            wire:poll.60s="pollOrderState"
        @endif
    >
        {{ $this->content }}
    </div>
</x-filament-panels::page>
