/**
 * Local draft safety net for autosaved audit fields.
 *
 * Fields save to the server when they lose focus (wire:model.live.blur). Text typed but not yet
 * blurred would be lost on a reload, a crash or a closed tab, so every keystroke is also copied to
 * localStorage. On page load (and after Livewire re-renders, e.g. switching department) a draft the
 * server does not have yet is put back into its field and sent to the server. A draft is deleted once
 * a request sent after its last keystroke has succeeded while the field was no longer being edited,
 * i.e. once the server has taken it (it may have trimmed or validated it; the server stays the source of truth).
 *
 * Only fields inside [data-draft-scope] with a live wire:model take part, so search boxes, login and
 * account forms (passwords) are never stored. Storage can be unavailable (private mode, blocked site
 * data); then the app simply behaves as before.
 */

const PREFIX = 'audit-draft:';
const MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000;
const SKIPPED_TYPES = ['password', 'file', 'search', 'checkbox', 'radio', 'hidden'];

const storage = {
    read(key) {
        try {
            return JSON.parse(localStorage.getItem(key));
        } catch {
            return null;
        }
    },
    write(key, draft) {
        try {
            localStorage.setItem(key, JSON.stringify(draft));
        } catch {
            // Quota or blocked storage: the server autosave still works.
        }
    },
    remove(key) {
        try {
            localStorage.removeItem(key);
        } catch {
            // Ignore.
        }
    },
    keys() {
        try {
            return Object.keys(localStorage).filter((key) => key.startsWith(PREFIX));
        } catch {
            return [];
        }
    },
};

function modelName(el) {
    const attribute = el.getAttributeNames().find((name) => name.startsWith('wire:model') && name.includes('.live'));

    return attribute ? el.getAttribute(attribute) : null;
}

/** The draft key for a field, or null when the field does not take part. */
function draftKey(el) {
    if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement) || SKIPPED_TYPES.includes(el.type)) {
        return null;
    }

    const scope = el.closest('[data-draft-scope]')?.dataset.draftScope;
    const model = modelName(el);

    return scope && model ? `${PREFIX}${scope}:${model}` : null;
}

/** The $wire of the Livewire component that owns the field. */
function wireOf(el) {
    const root = el.closest('[wire\\:id]');

    return root ? window.Livewire.find(root.getAttribute('wire:id')) : null;
}

function serverValue(wire, model) {
    const value = wire.$get(model);

    return value === null || value === undefined ? '' : String(value);
}

/** Drafts already sent during this page view, so a re-render never sends the same draft twice. */
let pushed = new Set();

function fields(root = document) {
    return [...root.querySelectorAll('[data-draft-scope] input, [data-draft-scope] textarea')].filter((el) => draftKey(el));
}

/** Tells the "Tersimpan otomatis" indicator whether anything on this page is only saved locally. */
function announce() {
    const pending = fields().some((el) => storage.read(draftKey(el)) !== null);

    window.dispatchEvent(new CustomEvent('audit-draft', { detail: { pending } }));
}

/** Drops drafts the server has received: typed before the request left, and the field is no longer being edited. */
function forgetSavedDrafts(root, sentAt) {
    for (const el of fields(root)) {
        const key = draftKey(el);
        const draft = storage.read(key);

        if (draft && el !== document.activeElement && draft.savedAt <= sentAt) {
            storage.remove(key);
        }
    }
}

/** Puts unsaved drafts back into their fields and sends them to the server. Returns how many. */
function restore(root = document) {
    let restored = 0;

    for (const el of fields(root)) {
        const key = draftKey(el);
        const draft = storage.read(key);
        const wire = wireOf(el);

        if (!draft || !wire || el === document.activeElement || pushed.has(key)) {
            continue;
        }

        if (serverValue(wire, modelName(el)) === draft.value) {
            storage.remove(key);
            continue;
        }

        pushed.add(key);
        el.value = draft.value;
        wire.$set(modelName(el), draft.value);
        restored++;
    }

    return restored;
}

function sweepExpired() {
    const now = Date.now();

    for (const key of storage.keys()) {
        const draft = storage.read(key);

        if (!draft || typeof draft.value !== 'string' || now - (draft.savedAt ?? 0) > MAX_AGE_MS) {
            storage.remove(key);
        }
    }
}

document.addEventListener(
    'input',
    (event) => {
        const key = draftKey(event.target);

        if (key) {
            storage.write(key, { value: event.target.value, savedAt: Date.now() });
            announce();
        }
    },
    true,
);

document.addEventListener('livewire:init', () => {
    sweepExpired();

    window.Livewire.interceptMessage(({ message, onSend, onSuccess }) => {
        let sentAt = Date.now();

        onSend(() => {
            sentAt = Date.now();
        });
        onSuccess(() => {
            setTimeout(() => {
                forgetSavedDrafts(message.component.el, sentAt);
                announce();
            });
        });
    });

    // A department switch swaps the fields in place; their drafts need the same treatment as a page load.
    window.Livewire.hook('morphed', ({ component }) => {
        setTimeout(() => restore(component.el));
    });
});

document.addEventListener('livewire:navigated', () => {
    pushed = new Set();

    setTimeout(() => {
        const restored = restore();

        if (restored > 0) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'info', message: `${restored} isian yang belum tersimpan dipulihkan dari draft di perangkat ini.` },
            }));
        }

        announce();
    });
});
