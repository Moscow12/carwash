/**
 * Shared report printing (used by <x-print.button>).
 *
 * Copies the page's [data-print-area] sections (or the closest card) into a new window that uses the
 * shared A4 print design, then opens the print dialog. With a Livewire component that uses the
 * WithPrintMode trait, it first asks the server for every filtered row and restores the page after.
 *
 * Kept in its own file on purpose: inline scripts must not contain literal </head> or </body>,
 * which Livewire treats as the place to inject its own assets.
 */
(function () {
    'use strict';

    // Interactive bits that make no sense on paper
    var STRIP = [
        'button', 'input', 'select', 'textarea', 'label', 'script', 'template', 'style:not([data-keep])',
        '.pagination', '.d-print-none', '[data-print-exclude]', '.modal-footer', '.dropdown-menu',
        '.select2-container', '.nav-tabs', '.nav-pills', '[wire\\:loading]', '.spinner-border', '.table-loading'
    ].join(',');

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function areasFor(button) {
        var root = button.closest('[wire\\:id]') || document.body;
        var marked = Array.prototype.slice.call(root.querySelectorAll('[data-print-area]'))
            // A marked area inside another marked area is printed once, as part of its parent
            .filter(function (el) { return !el.parentElement.closest('[data-print-area]'); })
            // Hidden sections (closed modal, inactive tab) are skipped
            .filter(function (el) { return el.offsetParent !== null || el.getClientRects().length > 0; });

        if (marked.length) {
            return marked;
        }

        return [button.closest('.card') || root];
    }

    function cleanCopy(element) {
        var copy = element.cloneNode(true);

        // Sortable headers and [data-print-text] controls keep their text; the control itself is dropped
        copy.querySelectorAll('th button, th a, [data-print-text]').forEach(function (control) {
            control.replaceWith(document.createTextNode(control.textContent.replace(/\s+/g, ' ').trim()));
        });

        copy.querySelectorAll(STRIP).forEach(function (node) { node.remove(); });

        // Links print as plain text
        copy.querySelectorAll('a').forEach(function (link) {
            link.replaceWith.apply(link, Array.prototype.slice.call(link.childNodes));
        });

        // Scrollable boxes must show everything on paper
        copy.querySelectorAll('[style*="max-height"], [style*="overflow"]').forEach(function (node) {
            node.style.maxHeight = 'none';
            node.style.overflow = 'visible';
        });

        copy.removeAttribute('data-print-area');

        return '<div class="print-section">' + copy.innerHTML + '</div>';
    }

    function templateHtml(button, name) {
        var template = button.parentElement.querySelector('template[data-print-' + name + ']');
        return template ? template.innerHTML : '';
    }

    function openWindow(button, contentHtml) {
        var title = button.getAttribute('data-print-title') || document.title;
        var printWindow = window.open('', '_blank');

        if (!printWindow) {
            alert('Please allow pop-ups for this site to print.');
            return;
        }

        var html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            + '<meta name="viewport" content="width=device-width, initial-scale=1">'
            + '<title>' + escapeHtml(title) + '</title>'
            + templateHtml(button, 'styles')
            + '</head><body class="client-print">'
            + '<div class="toolbar">'
            + '<button type="button" class="primary" onclick="window.print()">Print / Save as PDF</button>'
            + '<button type="button" class="secondary" onclick="window.close()">Close</button>'
            + '<small>Choose "Save as PDF" in the print dialog to keep a file.</small>'
            + '</div>'
            + '<div class="page">'
            + templateHtml(button, 'header')
            + contentHtml
            + templateHtml(button, 'footer')
            + '</div></body></html>';

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();

        // Wait for the theme CSS so the copied content is styled before printing
        printWindow.addEventListener('load', function () {
            setTimeout(function () {
                printWindow.focus();
                printWindow.print();
            }, 250);
        });
    }

    function nextFrame() {
        return new Promise(function (resolve) { requestAnimationFrame(function () { requestAnimationFrame(resolve); }); });
    }

    window.ReportPrint = {
        /**
         * @param {HTMLElement} button  the clicked print button
         * @param {object|null} wire    the Livewire component ($wire) when every filtered row should be printed
         */
        print: async function (button, wire) {
            var loadAll = wire && typeof wire.startPrinting === 'function';

            try {
                if (loadAll) {
                    await wire.startPrinting();
                    await nextFrame();
                }

                openWindow(button, areasFor(button).map(cleanCopy).join(''));
            } finally {
                if (loadAll) {
                    wire.stopPrinting();
                }
            }
        }
    };
})();
