



@if ($showNotification)
    <div class="fixed top-4 right-4 z-50 transition-all duration-300 ease-in-out">
        <div
            class="border-l-4 p-4 rounded shadow-lg {{ $notificationType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' }}">
            <div class="flex justify-between items-center">
                <p class="font-medium">{{ $notificationMessage }}</p>
                <button wire:click="hideNotification" class="ml-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif


<script>
    // Auto-hide notification après 3 secondes
    document.addEventListener('livewire:init', () => {
        Livewire.on('auto-hide-notification', () => {
            setTimeout(() => {
                @this.hideNotification();
            }, 3000);
        });
    });
</script>
