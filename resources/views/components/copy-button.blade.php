@props(['text' => null, 'fetch' => null, 'label' => 'Salin', 'copiedMessage' => 'Disalin ke clipboard.'])

{{--
    Copies text (see resources/js/clipboard.js), swaps to a check for 2 seconds and announces it with a toast.
    Pass `text` for text already on the page, or `fetch` with a JS expression returning a promise of the text
    (e.g. a $wire call) for anything that must not be rendered into the HTML, such as a password.
--}}
<button type="button" x-data="{ copied: false }"
        x-on:click="
            copied = await window.copyText({{ $fetch ?? \Illuminate\Support\Js::from($text) }});
            $dispatch('toast', copied
                ? { message: @js($copiedMessage), type: 'success' }
                : { message: 'Gagal menyalin. Coba lagi.', type: 'error' });
            setTimeout(() => copied = false, 2000);
        "
        {{ $attributes->class(['btn']) }}>
    <x-icon name="copy" class="size-4" x-show="! copied" />
    <x-icon name="check" class="size-4" x-show="copied" x-cloak />
    <span x-text="copied ? 'Tersalin' : @js($label)">{{ $label }}</span>
</button>
