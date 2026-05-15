/**
 * Workers' Comp Calculator JavaScript
 * Handles form validation, conditional logic, and AJAX submission
 */

(function ($) {
    'use strict';

    // Common body parts for autocomplete
    const BODY_PARTS = wccI18n.strings.parts;

    // Counter for dynamic rows
    let bodyPartIndex = 1;

    /**
     * Initialize calculator when DOM is ready
     */
    $(document).ready(function () {
        initCalculator();
    });

    /**
     * Initialize calculator functionality
     */
    function initCalculator() {
        // Occupational disease toggle
        handleOccupationalDiseaseToggle();

        // Body part autocomplete
        initBodyPartAutocomplete();

        // Form validation
        initFormValidation();

        // Wizard navigation
        initWizardNavigation();

        // Form submission
        handleFormSubmission();

        // Phone number formatting
        formatPhoneNumber();

        // Repeater Logic
        handleRepeaterFields();

        // Psychological Toggle
        handlePsychologicalToggle();

        // Initialize Datepicker
        initDatePicker();

        // Initialize Custom Select
        initCustomSelect();
    }

    /**
     * Initialize Wizard Navigation
     */
    function initWizardNavigation() {
        const $step1 = $('#wcc-step-1');
        const $step2 = $('#wcc-step-2');
        const $nextBtn = $('.wcc-next-btn');
        const $backBtn = $('.wcc-back-btn');
        const $progressBar = $('.wcc-step-progress');
        const $stepLabels = $('.wcc-step-label');

        // Next Button Click
        $nextBtn.on('click', function () {
            if (validateStep($step1)) {
                $step1.fadeOut(200, function () {
                    $step2.fadeIn(200);
                    updateProgress(2);
                    // Scroll to Top of Form
                    $('html, body').animate({
                        scrollTop: $('.wcc-calculator-card').offset().top - 40
                    }, 400);
                });
            }
        });

        // Back Button Click
        $backBtn.on('click', function () {
            $step2.fadeOut(200, function () {
                $step1.fadeIn(200);
                updateProgress(1);
            });
        });

        function updateProgress(step) {
            if (step === 1) {
                $progressBar.css('width', '50%');
                $stepLabels.eq(0).addClass('active');
                $stepLabels.eq(1).removeClass('active');
            } else {
                $progressBar.css('width', '100%');
                $stepLabels.eq(1).addClass('active');
            }
        }
    }

    /**
     * Validate a specific step container
     */
    function validateStep($container) {
        let isValid = true;

        $container.find('input[required]:not(:disabled), select[required]:not(:disabled), textarea[required]:not(:disabled)').each(function () {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        if (!isValid) {
            // Scroll to first error
            const $firstError = $container.find('.error').first();
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
            }
        }

        return isValid;
    }

    /**
     * Handle Repeater Fields (Add/Remove Body Parts)
     */
    function handleRepeaterFields() {
        const $container = $('#wcc-body-parts-container');
        const $addButton = $('#wcc-add-body-part');

        $addButton.on('click', function () {
            const index = bodyPartIndex++;
            const template = `
                <div class="wcc-body-part-row" data-index="${index}" style="display:none;">
                    <button type="button" class="wcc-remove-row-btn" aria-label="${wccI18n.strings.remove_part_aria}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                    <div class="wcc-grid-row">
                        <div class="wcc-field-group">
                            <label class="wcc-label">${wccI18n.strings.body_part_label} <span class="wcc-required">*</span></label>
                            <div class="wcc-input-wrapper with-icon">
                                <span class="wcc-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </span>
                                <input type="text" name="body_parts[${index}][part]"
                                    class="wcc-input wcc-searchable wcc-body-part-input"
                                    placeholder="${wccI18n.strings.search_body_part_placeholder}" autocomplete="off" required>
                            </div>
                            <div class="wcc-suggestions"></div>
                        </div>
                        <div class="wcc-field-group">
                            <label class="wcc-label">${wccI18n.strings.impairment_label} <span class="wcc-required">*</span></label>
                            <div class="wcc-input-wrapper with-suffix">
                                <input type="number" name="body_parts[${index}][impairment]"
                                    class="wcc-input wcc-impairment-input" min="0" max="100" step="1"
                                    placeholder="0" required>
                                <span class="wcc-input-suffix">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const $row = $(template);
            $container.append($row);
            $row.slideDown(200);

            // Re-init autocomplete for the new input
            initBodyPartAutocomplete($row.find('.wcc-searchable'));

            // Re-init validation for new inputs
            $row.find('input').on('blur', function () {
                validateField($(this));
            });
        });

        // Remove Row
        $container.on('click', '.wcc-remove-row-btn', function () {
            $(this).closest('.wcc-body-part-row').slideUp(200, function () {
                $(this).remove();
            });
        });
    }

    /**
     * Handle Psychological Toggle
     */
    function handlePsychologicalToggle() {
        $('#wcc-psychological-claim').on('change', function () {
            const isChecked = $(this).is(':checked');
            const detailsField = $('#wcc-psychological-details');

            if (isChecked) {
                detailsField.slideDown(300);
            } else {
                detailsField.slideUp(300);
                // Clear input to prevent ghost data
                $('#wcc-psych-impairment').val('');
            }
        });
    }

    /**
     * Handle Occupational Disease Toggle
     */
    function handleOccupationalDiseaseToggle() {
        $('#wcc-occupational-disease').on('change', function () {
            const isChecked = $(this).is(':checked');
            const detailsField = $('#wcc-occupational-details');
            const physicalSection = $('#wcc-physical-injuries-section');

            if (isChecked) {
                // Determine Occ Disease Logic
                detailsField.slideDown(300);

                // Hide Physical Section
                physicalSection.slideUp(300);

                // Clear and disable Physical Inputs (prevent validation errors/ghost data)
                physicalSection.find('input').val('').prop('disabled', true);
            } else {
                detailsField.slideUp(300);

                // Show Physical Section
                physicalSection.slideDown(300);

                // Re-enable Physical Inputs
                physicalSection.find('input').prop('disabled', false);

                // Clear inputs to prevent ghost data
                $('#wcc-occupational-impairment').val('');
                $('#wcc-occupational-description').val('');
            }
        });
    }

    /**
     * Initialize body part autocomplete
     */
    /**
     * Initialize body part autocomplete
     * Modified to handle dynamic inputs
     */
    function initBodyPartAutocomplete($specificInput = null) {
        //If specific input passed, use it. Otherwise use all .wcc-searchable
        const $inputs = $specificInput ? $specificInput : $('.wcc-searchable');

        $inputs.each(function () {
            const $input = $(this);
            // Check if already initialized to avoid double binding
            if ($input.data('autocomplete-init')) return;
            $input.data('autocomplete-init', true);

            const $wrapper = $input.closest('.wcc-field-group');
            // Dynamically find suggestions container relative to input
            let $suggestions = $wrapper.find('.wcc-suggestions');
            if ($suggestions.length === 0) {
                // Fallback or create if missing
                $suggestions = $('<div class="wcc-suggestions"></div>');
                $wrapper.append($suggestions);
            }

            let selectedIndex = -1;

            // Show suggestions on input
            $input.on('input', function () {
                const value = $(this).val().toLowerCase();

                if (value.length === 0) {
                    $suggestions.hide().empty();
                    return;
                }

                // Filter body parts
                const filtered = BODY_PARTS.filter(part =>
                    part.toLowerCase().includes(value)
                );

                if (filtered.length > 0) {
                    displaySuggestions(filtered, $suggestions, $input);
                } else {
                    $suggestions.hide().empty();
                }
            });

            // Hide suggestions when clicking outside - Handled globally

            // Handle keyboard navigation
            $input.on('keydown', function (e) {
                const $items = $suggestions.find('.wcc-suggestion-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, $items.length - 1);
                    updateSelection($items, selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection($items, selectedIndex);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    $items.eq(selectedIndex).click();
                } else if (e.key === 'Escape') {
                    $suggestions.hide();
                    selectedIndex = -1;
                }
            });
        });

        /**
         * Display suggestions (Scoped)
         */
        function displaySuggestions(items, $container, $targetInput) {
            $container.empty();

            items.forEach(function (item) {
                const $item = $('<div>')
                    .addClass('wcc-suggestion-item')
                    .text(item)
                    .on('click', function () {
                        $targetInput.val(item);
                        $container.hide();
                    });

                $container.append($item);
            });

            $container.show();
        }

        /**
         * Update selection highlight
         */
        function updateSelection($items, index) {
            $items.removeClass('active');
            if (index >= 0) {
                $items.eq(index).addClass('active');
            }
        }
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        const $form = $('#wcc-calculator-form');

        // Real-time validation
        $form.find('input, select, textarea').on('blur', function () {
            validateField($(this));
        });

        // Clear errors on input
        $form.find('input, select, textarea').on('input change', function () {
            const $field = $(this);
            if ($field.hasClass('error')) {
                clearFieldError($field);
            }
        });
    }

    /**
     * Validate individual field
     */
    function validateField($field) {
        const name = $field.attr('name') || '';
        const value = $field.val() || '';
        const $errorMsg = $('#' + $field.attr('id') + '-error');

        // Clear previous error
        clearFieldError($field);

        // Required field check
        if ($field.prop('required') && !value.trim()) {
            showFieldError($field, wccI18n.strings.req_field);
            return false;
        }

        // Email validation
        if (name === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                showFieldError($field, wccI18n.strings.req_email);
                return false;
            }
        }

        // Phone validation
        if (name === 'phone' && value) {
            const phoneRegex = /^[\d\s\(\)\-]+$/;
            const digitsOnly = value.replace(/\D/g, '');
            if (!phoneRegex.test(value) || digitsOnly.length < 10) {
                showFieldError($field, wccI18n.strings.req_phone);
                return false;
            }
        }

        // Impairment validation
        if ((name.includes('impairment') || name === 'impairment') && value) {
            const num = parseFloat(value);
            if (isNaN(num) || num < 0 || num > 100) {
                showFieldError($field, wccI18n.strings.req_impairment);
                return false;
            }
        }

        // Wage validation
        if (name === 'wage' && value) {
            const num = parseFloat(value);
            if (isNaN(num) || num <= 0) {
                showFieldError($field, wccI18n.strings.req_wage);
                return false;
            }
        }

        // Date validation
        if (name === 'date_injury' && value) {

            const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
            const match = value.match(dateRegex);

            if (!match) {
                showFieldError($field, wccI18n.strings.req_date_format);
                return false;
            }

            const mm = parseInt(match[1], 10);
            const dd = parseInt(match[2], 10);
            const yyyy = parseInt(match[3], 10);

            const d = new Date(yyyy, mm - 1, dd);

            const isValidDate = d.getFullYear() === yyyy && d.getMonth() === mm - 1 && d.getDate() === dd;

            if (!isValidDate) {
                showFieldError($field, wccI18n.strings.req_date_format);
                return false;
            }

            const today = new Date();
            if (d > today) {
                showFieldError($field, wccI18n.strings.req_date_future);
                return false;
            }
        }

        return true;
    }

    /**
     * Show field error
     */
    function showFieldError($field, message) {
        $field.addClass('error');
        const $errorMsg = $('#' + $field.attr('id') + '-error');
        $errorMsg.text(message).addClass('show');
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        $field.removeClass('error');
        const $errorMsg = $('#' + $field.attr('id') + '-error');
        $errorMsg.removeClass('show').text('');
    }

    /**
     * Handle form submission
     */
    function handleFormSubmission() {
        $('#wcc-calculator-form').on('submit', function (e) {
            e.preventDefault();

            // Validate all fields (final check, mainly for Step 2)
            let isValid = true;
            const $form = $(this);

            // Re-validate Step 2 specifically
            $('#wcc-step-2').find('input[required], select[required], textarea[required]').each(function () {
                if (!validateField($(this))) {
                    isValid = false;
                }
            });

            // Check consent checkbox
            if (!$('#wcc-consent').is(':checked')) {
                showFieldError($('#wcc-consent'), wccI18n.strings.req_consent);
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            // Submit form via AJAX
            submitForm($form);
        });
    }

    /**
     * Submit form via AJAX
     */
    function submitForm($form) {
        const $submitBtn = $('#wcc-submit-btn');
        const $btnText = $submitBtn.find('.wcc-btn-text');
        const $btnLoader = $submitBtn.find('.wcc-btn-loader');

        // Show Processing Overlay (Add class for Flex support)
        $('#wcc-processing').addClass('active');

        // Collect form data
        const formData = {
            action: 'wcc_calculate',
            nonce: wccAjax.nonce,
            state: $('#wcc-state').val(),
            date_injury: $('#wcc-date-injury').val(),
            // Collect dynamic body parts
            body_parts: getBodyPartsData(),
            wage: $('#wcc-wage').val(),
            // Psych settings
            psychological_claim: $('#wcc-psychological-claim').is(':checked') ? 1 : 0,
            psych_impairment: $('#wcc-psych-impairment').val(),
            // Occ details
            occupational_disease: $('#wcc-occupational-disease').is(':checked') ? 1 : 0,
            occupational_impairment: $('#wcc-occupational-impairment').val(),
            occupational_description: $('#wcc-occupational-description').val(),
            name: $('#wcc-name').val(),
            email: $('#wcc-email').val(),
            phone: $('#wcc-phone').val(),
            consent: $('#wcc-consent').is(':checked') ? 1 : 0
        };

        // Send AJAX request
        $.ajax({
            url: wccAjax.ajaxurl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    // Hide form wizard
                    $('.wcc-wizard-step').hide();

                    displayResults(response.data);
                    // Scroll to results
                    $('html, body').animate({
                        scrollTop: $('#wcc-results').offset().top - 50
                    }, 500);
                } else {
                    alert(wccI18n.strings.error_prefix + (response.data.message || wccI18n.strings.error_generic));
                }
            },
            error: function () {
                alert(wccI18n.strings.error_network);
            },
            complete: function () {
                // Re-enable button
                $submitBtn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
                $('#wcc-processing').removeClass('active');
            }
        });
    }

    /**
     * Display calculation results
     */
    function displayResults(data) {
        const $results = $('#wcc-results');

        let html = '<div class="wcc-results-card">';

        // Header
        html += '<div class="wcc-result-header">';
        html += '<div class="wcc-result-icon">⚖️</div>';
        html += '<h2 class="wcc-results-title">' + wccI18n.strings.results_title + '</h2>';
        html += '</div>';

        // The Big Number
        html += '<div class="wcc-valuation-range">';
        html += '<div class="wcc-valuation-amount">' + data.valuation_range + '</div>';
        html += '<div class="wcc-valuation-label">' + wccI18n.strings.results_label + '</div>';
        html += '</div>';

        // Disclaimer / Context
        html += '<div class="wcc-result-context">';
        html += '<p>' + data.disclaimer + '</p>';
        html += '</div>';

        // Call to Action
        html += '<div class="wcc-result-cta">';
        html += '<a href="tel:410-525-5337" class="wcc-cta-button">'; // Replace with actual number/link if needed
        html += '<span>' + wccI18n.strings.cta_button + '</span>';
        html += '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
        html += '</a>';
        html += '<p class="wcc-cta-subtext">' + wccI18n.strings.cta_subtext + '</p>';
        html += '</div>';

        html += '</div>'; // End card

        $results.html(html).slideDown(500);
    }

    /**
     * Format phone number as user types
     */
    function formatPhoneNumber() {
        $('#wcc-phone').on('input', function () {
            let value = $(this).val().replace(/\D/g, '');

            if (value.length >= 6) {
                value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
            } else if (value.length >= 3) {
                value = '(' + value.substring(0, 3) + ') ' + value.substring(3);
            }

            $(this).val(value);
        });
    }

    /**
         * Helper to collect body parts array from inputs
         */
    function getBodyPartsData() {
        const parts = [];
        $('.wcc-body-part-row').each(function () {
            const $row = $(this);
            const part = $row.find('.wcc-body-part-input').val();
            const imp = $row.find('.wcc-impairment-input').val();
            if (part && imp) {
                parts.push({
                    part: part,
                    impairment: imp
                });
            }
        });
        return parts;
    }





    /**
     * Initialize jQuery UI Datepicker
     */
    function initDatePicker() {
        const $dateInput = $('#wcc-date-injury');
        if ($dateInput.length) {
            $dateInput.datepicker({
                dateFormat: 'mm/dd/yy', // jQuery UI uses 'yy' for 4-digit year
                changeMonth: true,
                changeYear: true,
                yearRange: "-50:+0", // Allow past 50 years up to current
                maxDate: 0 // No future dates
            });
        }
    }

    // ==========================================
    // Custom Searchable Select
    // ==========================================
    function initCustomSelect() {
        const $dropdown = $('#wcc-state-dropdown');
        const $trigger = $dropdown.find('.wcc-select-trigger');
        const $list = $dropdown.find('.wcc-select-dropdown');
        const $search = $dropdown.find('.wcc-select-search');
        const $options = $dropdown.find('.wcc-select-option');
        const $hiddenInput = $('#wcc-state');
        const $selectedText = $dropdown.find('.wcc-selected-text');

        // Toggle Open/Close
        $trigger.on('click', function (e) {
            e.stopPropagation();
            const isOpen = $list.hasClass('open');

            // Close all others if needed (though we only have one)
            $('.wcc-select-dropdown').removeClass('open');
            $('.wcc-select-trigger').removeClass('active');

            if (!isOpen) {
                $list.addClass('open');
                $trigger.addClass('active');
                $search.focus();
            }
        });

        // Search Filter
        $search.on('input', function () {
            const term = $(this).val().toLowerCase();
            $options.each(function () {
                const text = $(this).text().toLowerCase();
                const val = $(this).data('value');
                if (val === "") return;

                if (text.indexOf(term) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Select Option
        $options.on('click', function (e) {
            e.stopPropagation();
            const val = $(this).data('value');
            const text = $(this).text();

            // Update UI
            if (val === "") {
                $selectedText.html('<span class="placeholder">' + wccI18n.strings.select_state_placeholder + '</span>');
            } else {
                $selectedText.text(text);
                $selectedText.find('.placeholder').remove();
            }

            // Update Hidden Input
            $hiddenInput.val(val).trigger('change');

            // Active Class
            $options.removeClass('selected');
            $(this).addClass('selected');

            // Close
            $list.removeClass('open');
            $trigger.removeClass('active');
            $search.val(''); // Reset search
            $options.show(); // Reset filter
        });

        // Close on Click Outside
        $(document).on('click', function () {
            $list.removeClass('open');
            $trigger.removeClass('active');
        });

        // Close on Escape
        $(document).on('keydown', function (e) {
            if (e.key === "Escape") {
                $list.removeClass('open');
                $trigger.removeClass('active');
            }
        });
    }

})(jQuery);