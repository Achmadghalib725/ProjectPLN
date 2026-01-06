@props(['id' => 'confirm-delete-modal'])

<div
    x-data="{
        show: false,
        title: '',
        message: '',
        formAction: '',
        open(title, message, formAction) {
            this.title = title;
            this.message = message;
            this.formAction = formAction;
            this.show = true;
            document.body.classList.add('overflow-y-hidden');
        },
        close() {
            this.show = false;
            document.body.classList.remove('overflow-y-hidden');
        },
        submit() {
            this.$refs.deleteForm.action = this.formAction;
            this.$refs.deleteForm.submit();
        }
    }"
    x-on:open-delete-modal.window="open($event.detail.title, $event.detail.message, $event.detail.action)"
    x-on:keydown.escape.window="show && close()"
    x-show="show"
    x-cloak
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transform transition-all"
        @click="close()"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    {{-- Modal Panel --}}
    <div class="min-h-screen flex items-start justify-center pt-[15vh] sm:pt-[20vh]">
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full sm:max-w-md mx-4 sm:mx-auto"
            @click.stop
        >
        <div class="p-6">
            {{-- Header with Icon --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900" x-text="title"></h2>
                    <p class="mt-2 text-sm text-gray-600" x-text="message"></p>
                </div>
                {{-- Close Button --}}
                <button type="button" @click="close()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Hidden form for delete --}}
            <form x-ref="deleteForm" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            {{-- Action Buttons --}}
            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="close()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="submit()"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
        </div>
    </div>
</div>
