// Keeps a copy in memory of the file picked in any single-file <input type="file">.
//
// On phones a file picked from Google Drive, Photos, WhatsApp or a download notification is only
// lent to the page; by the time the form is submitted (often after filling more fields or paying)
// the browser may no longer be allowed to read it and the upload fails with
// ERR_UPLOAD_FILE_CHANGED ("Your file couldn't be accessed"). Reading it right away and putting the
// in-memory copy back into the input means the submit never goes back to the original.
//
// Inputs with `multiple` manage their own lists (e.g. the business gallery) and are left alone,
// as is any input marked data-no-file-copy.
(function () {
    if (typeof DataTransfer === 'undefined' || !window.File || !Blob.prototype.arrayBuffer) {
        return;
    }

    const isGu = (document.documentElement.lang || '').toLowerCase().startsWith('gu');
    const unreadable = isGu
        ? 'આ ફાઇલ વાંચી શકાઈ નથી. કૃપા કરીને ફાઇલ પહેલા ફોનમાં ડાઉનલોડ / સેવ કરો અને પછી ફરી પસંદ કરો.'
        : 'This file could not be read. Please download / save it to your phone first, then select it again.';

    // Copies still being read (a Drive file is downloaded first, which can take a few seconds)
    const pending = new Map();

    document.addEventListener('change', function (e) {
        const input = e.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.multiple
            || input.hasAttribute('data-no-file-copy') || !input.files || input.files.length !== 1) {
            return;
        }

        const picked = input.files[0];
        // Skip our own copy, should something re-dispatch a change event
        if (picked.__inMemory) {
            return;
        }

        const copying = picked.arrayBuffer().then(function (buffer) {
            // The user may have picked another file meanwhile
            if (input.files[0] !== picked) {
                return;
            }
            const copy = new File([buffer], picked.name, { type: picked.type, lastModified: picked.lastModified });
            copy.__inMemory = true;
            const dt = new DataTransfer();
            dt.items.add(copy);
            input.files = dt.files;
        }).catch(function () {
            // Already unreadable: better to say so now than to fail on submit
            if (input.files[0] === picked) {
                input.value = '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            alert(unreadable);
        }).finally(function () {
            if (pending.get(input) === copying) {
                pending.delete(input);
            }
        });
        pending.set(input, copying);
    }, true);

    // A submit while a copy is still being read would send the original file: wait for the copy,
    // then submit again. Runs after the page's own submit handlers and respects their preventDefault.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (e.defaultPrevented || !(form instanceof HTMLFormElement)) {
            return;
        }

        const waits = [];
        pending.forEach(function (copying, input) {
            if (input.form === form) {
                waits.push(copying);
            }
        });
        if (!waits.length) {
            return;
        }

        e.preventDefault();
        const submitter = e.submitter || null;
        if (submitter) submitter.disabled = true;
        Promise.all(waits).then(function () {
            if (submitter) submitter.disabled = false;
            // requestSubmit re-runs the form's validation, so a required file that was cleared is caught
            if (form.requestSubmit) {
                form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
            } else {
                form.submit();
            }
        });
    });
})();
