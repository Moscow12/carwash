/**
 * Global Select2 Initialization Script
 * Automatically converts all select elements with class 'select2' to Select2 dropdowns
 */

(function() {
    'use strict';

    /**
     * Initialize Select2 on all eligible select elements
     */
    function initializeSelect2Elements() {
        // Find all select elements that should use Select2
        const selectElements = document.querySelectorAll('select.select2:not(.select2-hidden-accessible)');

        selectElements.forEach(function(selectElement) {
            const $select = $(selectElement);

            // Skip if already initialized
            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            // Determine if this select is inside a modal
            const isInModal = $select.closest('.modal').length > 0;

            // Configure Select2 options
            const select2Options = {
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: !selectElement.hasAttribute('required'),
                placeholder: selectElement.getAttribute('data-placeholder') || 'Select an option...',
            };

            // If in modal, set dropdownParent to prevent z-index issues
            if (isInModal) {
                select2Options.dropdownParent = $select.closest('.modal');
            }

            // Initialize Select2
            $select.select2(select2Options);

            // For Livewire integration - sync changes back to Livewire
            if (selectElement.hasAttribute('wire:model') || selectElement.hasAttribute('wire:model.live')) {
                const wireModel = selectElement.getAttribute('wire:model') || selectElement.getAttribute('wire:model.live');

                $select.on('change', function(e) {
                    const component = Livewire.find(
                        selectElement.closest('[wire\\:id]')?.getAttribute('wire:id')
                    );

                    if (component) {
                        component.set(wireModel, $(this).val());
                    }
                });
            }
        });
    }

    /**
     * Destroy Select2 instances (useful for cleanup)
     */
    function destroySelect2Elements() {
        $('.select2-hidden-accessible').select2('destroy');
    }

    /**
     * Re-initialize Select2 (destroy and reinitialize)
     */
    window.reinitializeSelect2 = function() {
        destroySelect2Elements();
        setTimeout(initializeSelect2Elements, 100);
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeSelect2Elements, 200);
        });
    } else {
        setTimeout(initializeSelect2Elements, 200);
    }

    // Re-initialize after Livewire updates (if Livewire is present)
    if (typeof Livewire !== 'undefined') {
        // Livewire 3.x
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initializeSelect2Elements, 200);
        });

        Livewire.hook('morph.updated', ({ el, component }) => {
            setTimeout(initializeSelect2Elements, 100);
        });

        Livewire.hook('commit', ({ component, respond }) => {
            setTimeout(initializeSelect2Elements, 100);
        });
    }

    // Bootstrap modal events
    $(document).on('shown.bs.modal', '.modal', function() {
        setTimeout(initializeSelect2Elements, 100);
    });

    // Optional: Reinitialize when modal is hidden (cleanup)
    $(document).on('hidden.bs.modal', '.modal', function() {
        $(this).find('.select2-hidden-accessible').select2('destroy');
    });

    // Expose initialization function globally
    window.initializeSelect2 = initializeSelect2Elements;

})();
