<div>
    @isset($jsPath)
        <script>{!! file_get_contents($jsPath) !!}</script>
    @endisset
    @isset($cssPath)
        <style>{!! file_get_contents($cssPath) !!}</style>
    @endisset

    <div
            x-data="LivewireUIModal()"
            x-on:close.stop="setShowPropertyTo(false)"
            x-on:keydown.escape.window="show && closeModalOnEscape()"
            x-show="show"
            class="modal fade"
            x-bind:class="{ 'show': show }"
            style="display: none;"
            x-bind:style="show ? 'display: block !important; padding-right: 17px;' : 'display: none'"
    >
        <div class="modal-dialog modal-dialog-centered" role="document" x-bind:class="{
            'modal-sm': modalWidth === 'sm',
            'modal-lg': modalWidth === 'lg',
            'modal-xl': modalWidth === 'xl' || modalWidth === '2xl'
        }">
            <div
                    x-show="show"
                    x-on:click="closeModalOnClickAway()"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="modal-backdrop fade show position-fixed"
                    style="top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; background-color: rgba(0,0,0,0.5);"
            ></div>

            <div
                    x-show="show && showActiveComponent"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="modal-content"
                    id="modal-container"
                    x-trap.noscroll.inert="show && showActiveComponent"
                    aria-modal="true"
                    x-on:click.stop
            >
                @forelse($components as $id => $component)
                    <div x-show.immediate="activeComponent == '{{ $id }}'" x-ref="{{ $id }}" wire:key="{{ $id }}">
                        @livewire($component['name'], $component['arguments'], key($id))
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
