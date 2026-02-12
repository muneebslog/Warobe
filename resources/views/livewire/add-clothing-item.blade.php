<div>
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">{{ __('Add clothing item') }}</flux:heading>
        <form wire:submit="save" class="space-y-4">
            <flux:input wire:model="name" :label="__('Name')" type="text" required />
            <flux:error name="name" />

            <div>
                <flux:label>{{ __('Type') }}</flux:label>
                <flux:select wire:model="type" class="mt-1">
                    @foreach ($typeOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Image (optional)') }}</flux:label>
                <input type="file" wire:model="image" accept="image/*"
                    class="mt-1 block w-full text-sm text-zinc-500 file:me-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-zinc-700 dark:file:bg-zinc-700 dark:file:text-zinc-200" />
                <flux:error name="image" />
                <div wire:loading wire:target="image" class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Uploading & detecting color…') }}
                </div>
                @if ($image)
                    <div
                        class="mt-3 space-y-3"
                        x-data="{
                            cropper: null,
                            initCropper() {
                                const img = this.$refs.cropImage;
                                if (!img) return;
                                if (typeof window.Cropper === 'undefined') {
                                    setTimeout(() => this.initCropper(), 150);
                                    return;
                                }
                                if (this.cropper) {
                                    this.cropper.destroy();
                                    this.cropper = null;
                                }
                                this.cropper = new window.Cropper(img, {
                                    aspectRatio: NaN,
                                    viewMode: 1,
                                    dragMode: 'move',
                                    autoCropArea: 0.7,
                                });
                            },
                            detectFromCrop() {
                                if (!this.cropper) return;
                                const canvas = this.cropper.getCroppedCanvas({ maxWidth: 800, maxHeight: 800 });
                                if (!canvas) return;
                                const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                                $wire.detectFromCroppedArea(dataUrl);
                            }
                        }"
                        x-init="$nextTick(() => initCropper())"
                    >
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Drag to move, resize the box to focus on the shirt or pant, then detect color from that area.') }}
                        </p>
                        <div
                            class="cropper-wrap rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 overflow-hidden"
                            style="height: 320px; width: 100%;"
                        >
                            <img
                                x-ref="cropImage"
                                src="{{ $image->temporaryUrl() }}"
                                alt=""
                                style="max-width: 100%; max-height: 320px; display: block;"
                                @load="initCropper()"
                            />
                        </div>
                        <flux:button type="button" variant="outline" class="mt-2" x-on:click="detectFromCrop()" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="detectFromCroppedArea">{{ __('Detect color from selected area') }}</span>
                            <span wire:loading wire:target="detectFromCroppedArea">{{ __('Detecting…') }}</span>
                        </flux:button>
                        @if ($color_hex)
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Detected color') }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-8 h-8 rounded-full border-2 border-zinc-300 dark:border-zinc-600 shrink-0"
                                            style="background-color: {{ $color_hex }};"></span>
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst($color_family) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <flux:label>{{ __('Color') }}</flux:label>
                @if ($image && $color_detection_error)
                    <p class="mt-1 mb-1 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2 py-1.5 rounded">
                        {{ __('Color detection failed: :message', ['message' => $color_detection_error]) }}
                    </p>
                @endif
                <flux:select wire:model="color_family" class="mt-1" required>
                    @foreach ($colorFamilyOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="color_family" />
            </div>

            <div>
                <flux:label>{{ __('Formality') }}</flux:label>
                <flux:select wire:model="formality" class="mt-1">
                    @foreach ($formalityOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Season') }}</flux:label>
                <flux:select wire:model="season" class="mt-1">
                    @foreach ($seasonOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('Add item') }}</span>
                <span wire:loading wire:target="save">{{ __('Adding…') }}</span>
            </flux:button>
        </form>
    </flux:card>
</div>