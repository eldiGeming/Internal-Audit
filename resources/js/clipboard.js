/**
 * Copies text to the clipboard. `source` is a string or a promise of one (text fetched from the server
 * when the button is clicked). A promise goes to the clipboard as a ClipboardItem so the copy still
 * belongs to the click, which Safari requires. The Clipboard API only works on HTTPS or localhost, so
 * on a plain HTTP host it falls back to a hidden textarea and execCommand.
 * Resolves true when the text was copied.
 */
window.copyText = async (source) => {
    const text = Promise.resolve(source);

    if (typeof source !== 'string' && window.ClipboardItem && navigator.clipboard?.write) {
        try {
            const blob = text.then((value) => new Blob([value], { type: 'text/plain' }));
            await navigator.clipboard.write([new ClipboardItem({ 'text/plain': blob })]);

            return true;
        } catch {
            // Fall through to the simpler paths below.
        }
    }

    try {
        await navigator.clipboard.writeText(await text);

        return true;
    } catch {
        const area = document.createElement('textarea');
        area.value = await text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.append(area);
        area.select();

        const copied = document.execCommand('copy');
        area.remove();

        return copied;
    }
};
