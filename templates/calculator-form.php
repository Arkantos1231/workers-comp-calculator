<?php
/**
 * Plantilla del Formulario de la Calculadora
 * Diseño responsivo mobile-first con lógica condicional
 */

// Salir si se accede directamente
if (!defined('ABSPATH')) {
    exit;
}

// Obtener estado desde la URL
$current_state = WCC_Routing::get_state_from_url();
$states_list = WCC_Routing::get_states_list();
?>

<?php $wcc_icon_bg = get_option('wcc_header_icon_bg', ''); ?>
<?php if ($wcc_icon_bg): ?>
<style>.wcc-header-icon { background: <?php echo esc_attr($wcc_icon_bg); ?> !important; }</style>
<?php endif; ?>

<div class="wcc-calculator-container">
    <div class="wcc-calculator-card">

        <!-- Encabezado del formulario -->
        <div class="wcc-header">
            <div class="wcc-header-content">
                <h1 class="wcc-title"><?php echo esc_html(WCC_i18n::get('form.title')); ?></h1>
                <?php if ($current_state): ?>
                    <p class="wcc-subtitle"><?php echo esc_html(WCC_i18n::get('form.subtitle_state')); ?> <?php echo esc_html($current_state); ?></p>
                <?php else: ?>
                    <p class="wcc-subtitle"><?php echo esc_html(WCC_i18n::get('form.subtitle_generic')); ?></p>
                <?php endif; ?>
            </div>
            <div class="wcc-header-icon">
                <?php $wcc_logo = get_option('wcc_header_logo', ''); ?>
                <?php if ($wcc_logo): ?>
                    <img src="<?php echo esc_url($wcc_logo); ?>" alt="" style="max-height:60px;  display:block;">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                        </path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario Principal -->
        <div class="wcc-processing-overlay" id="wcc-processing">
            <div class="wcc-pulse-ring">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <div class="wcc-processing-text"><?php echo esc_html(WCC_i18n::get('form.loading_text')); ?></div>
        </div>
        <form id="wcc-calculator-form" class="wcc-form" novalidate>

            <!-- Barra de Progreso -->
            <div class="wcc-step-indicator">
                <div class="wcc-step-bar">
                    <div class="wcc-step-progress" style="width: 50%;"></div>
                </div>
                <div class="wcc-step-labels">
                    <span class="wcc-step-label active"><?php echo esc_html(WCC_i18n::get('progress.step1')); ?></span>
                    <span class="wcc-step-label"><?php echo esc_html(WCC_i18n::get('progress.step2')); ?></span>
                </div>
            </div>

            <!-- Paso 1: Detalles de la Lesión -->
            <div class="wcc-wizard-step" id="wcc-step-1">
                <div class="wcc-form-section">
                    <h3 class="wcc-section-heading"><?php echo esc_html(WCC_i18n::get('form.step1_title')); ?></h3>

                    <div class="wcc-grid-row">
                        <!-- Selección de Estado -->
                        <div class="wcc-field-group">
                            <label for="wcc-state" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.state_label')); ?> <span class="wcc-required">*</span></label>
                            <!-- Input oculto para el envío del formulario -->
                            <input type="hidden" id="wcc-state" name="state"
                                value="<?php echo esc_attr($current_state); ?>" required>

                            <!-- Dropdown personalizado -->
                            <div class="wcc-custom-select" id="wcc-state-dropdown">
                                <div class="wcc-select-trigger" tabindex="0">
                                    <span class="wcc-selected-text">
                                        <?php echo $current_state ? esc_html($current_state) : '<span class="placeholder">' . esc_html(WCC_i18n::get('form.state_placeholder')) . '</span>'; ?>
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </div>

                                <!-- Contenido del Dropdown -->
                                <div class="wcc-select-dropdown">
                                    <div class="wcc-select-search-wrapper">
                                        <input type="text" class="wcc-select-search" placeholder="<?php echo esc_attr(WCC_i18n::get('form.state_search_placeholder')); ?>"
                                            autocomplete="off">
                                    </div>
                                    <div class="wcc-select-options">
                                        <div class="wcc-select-option" data-value=""><?php echo esc_html(WCC_i18n::get('form.state_select_default')); ?></div>
                                        <?php foreach ($states_list as $state): ?>
                                            <div class="wcc-select-option <?php echo ($current_state === $state) ? 'selected' : ''; ?>"
                                                data-value="<?php echo esc_attr($state); ?>">
                                                <?php echo esc_html($state); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="wcc-error-message" id="wcc-state-error"></span>
                        </div>

                        <!-- Fecha de Lesión -->
                        <div class="wcc-field-group">
                            <label for="wcc-date-injury" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.date_label')); ?> <span
                                    class="wcc-required">*</span></label>
                            <div class="wcc-input-wrapper">
                                <input type="text" id="wcc-date-injury" name="date_injury" class="wcc-input"
                                    placeholder="<?php echo esc_attr(WCC_i18n::get('form.date_placeholder')); ?>" autocomplete="off" required>

                            </div>
                            <span class="wcc-error-message" id="wcc-date-injury-error"></span>
                        </div>
                    </div>



                    <!-- Salario Semanal Promedio -->
                    <div class="wcc-field-group">
                        <label for="wcc-wage" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.wage_label')); ?> <span
                                class="wcc-required">*</span></label>
                        <div class="wcc-input-wrapper with-prefix">
                            <span class="wcc-input-prefix">$</span>
                            <input type="number" id="wcc-wage" name="wage" class="wcc-input" min="0" step="0.01"
                                placeholder="0.00" required>
                        </div>
                        <span class="wcc-help-text"><?php echo esc_html(WCC_i18n::get('form.wage_help_text')); ?></span>
                        <span class="wcc-error-message" id="wcc-wage-error"></span>
                    </div>

                    <!-- Sección de Lesiones Físicas (Envuelta para toggle) -->
                    <div id="wcc-physical-injuries-section">
                        <div class="wcc-section-separator"></div>
                        <h4 class="wcc-subsection-heading" style="color:#043144"><?php echo esc_html(WCC_i18n::get('form.physical_injuries_heading')); ?></h4>
                        <div id="wcc-body-parts-container">
                            <!-- Las filas dinámicas se agregarán aquí -->
                            <div class="wcc-body-part-row" data-index="0">
                                <div class="wcc-grid-row">
                                    <div class="wcc-field-group">
                                        <label class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.body_part_label')); ?> <span class="wcc-required">*</span></label>
                                        <div class="wcc-input-wrapper with-icon">
                                            <span class="wcc-input-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="11" cy="11" r="8"></circle>
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                                </svg>
                                            </span>
                                            <input type="text" name="body_parts[0][part]"
                                                class="wcc-input wcc-searchable wcc-body-part-input"
                                                placeholder="<?php echo esc_attr(WCC_i18n::get('form.body_part_placeholder')); ?>" autocomplete="off" required>
                                        </div>
                                        <div class="wcc-suggestions"></div>
                                    </div>
                                    <div class="wcc-field-group">
                                        <label class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.impairment_percentage_label')); ?> <span
                                                class="wcc-required">*</span></label>
                                        <div class="wcc-input-wrapper with-suffix">
                                            <input type="number" name="body_parts[0][impairment]"
                                                class="wcc-input wcc-impairment-input" min="0" max="100" step="1"
                                                placeholder="0" required>
                                            <span class="wcc-input-suffix">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wcc-field-group">
                            <button type="button" id="wcc-add-body-part" class="wcc-btn wcc-btn-secondary wcc-btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <?php echo esc_html(WCC_i18n::get('form.add_body_part_button')); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Reclamos Psicológicos -->
                    <div class="wcc-section-separator"></div>
                    <div class="wcc-field-group">
                        <label class="wcc-toggle-wrapper">
                            <input type="checkbox" id="wcc-psychological-claim" name="psychological_claim"
                                class="wcc-toggle-input" value="1">
                            <div class="wcc-toggle-slider"></div>
                            <span class="wcc-toggle-label-text"><?php echo esc_html(WCC_i18n::get('form.psychological_claim_toggle')); ?></span>
                        </label>
                    </div>

                    <div class="wcc-field-group wcc-conditional-field" id="wcc-psychological-details"
                        style="display: none;">
                        <label for="wcc-psych-impairment" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.psychological_impairment_label')); ?></label>
                        <div class="wcc-input-wrapper with-suffix">
                            <input type="number" id="wcc-psych-impairment" name="psych_impairment" class="wcc-input"
                                min="0" max="100" step="1" placeholder="0">
                            <span class="wcc-input-suffix">%</span>
                        </div>
                    </div>

                    <!-- Toggle de Enfermedad Ocupacional -->
                    <div class="wcc-field-group">
                        <label class="wcc-toggle-wrapper">
                            <input type="checkbox" id="wcc-occupational-disease" name="occupational_disease"
                                class="wcc-toggle-input" value="1">
                            <div class="wcc-toggle-slider"></div>
                            <span class="wcc-toggle-label-text"><?php echo esc_html(WCC_i18n::get('form.occupational_disease_toggle')); ?></span>
                        </label>
                    </div>

                    <!-- Detalles de Enfermedad Ocupacional (Condicional) -->
                    <div class="wcc-field-group wcc-conditional-field" id="wcc-occupational-details"
                        style="display: none;">
                        <div class="wcc-field-group">
                            <label for="wcc-occupational-impairment" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.occupational_impairment_label')); ?></label>
                            <div class="wcc-input-wrapper with-suffix">
                                <input type="number" id="wcc-occupational-impairment" name="occupational_impairment"
                                    class="wcc-input" min="0" max="100" step="1" placeholder="0"
                                    style="max-width: 200px;">
                                <span class="wcc-input-suffix" style="left: 170px;">%</span>
                            </div>
                        </div>
                        <div class="wcc-field-group">
                            <label for="wcc-occupational-description" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.occupational_description_label')); ?></label>
                            <textarea id="wcc-occupational-description" name="occupational_description"
                                class="wcc-textarea" rows="4"
                                placeholder="<?php echo esc_attr(WCC_i18n::get('form.occupational_description_placeholder')); ?>"></textarea>
                        </div>
                    </div>

                    <!-- Acciones del Paso 1 -->
                    <div class="wcc-field-group wcc-nav-group">
                        <button type="button" class="wcc-btn wcc-btn-primary wcc-next-btn">
                            <?php echo esc_html(WCC_i18n::get('general.next_step')); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Información de Contacto -->
            <div class="wcc-wizard-step" id="wcc-step-2" style="display: none;">
                <div class="wcc-contact-section">
                    <div class="wcc-step-header" style="text-align: center; margin-bottom: 32px;">
                        <div
                            style="width: 48px; height: 48px; background: #fff7ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: var(--wcc-brand-primary);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <h3 class="wcc-section-heading"
                            style="justify-content: center; font-size: 20px; color: var(--wcc-text-main);"><?php echo esc_html(WCC_i18n::get('form.step2_heading')); ?></h3>
                        <p class="wcc-section-description" style="color: var(--wcc-text-muted); font-size: 15px;"><?php echo esc_html(WCC_i18n::get('form.step2_subheading')); ?></p>
                    </div>

                    <div class="wcc-field-group">
                        <label for="wcc-name" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.full_name_label')); ?> <span class="wcc-required">*</span></label>
                        <div class="wcc-input-wrapper with-icon">
                            <span class="wcc-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input type="text" id="wcc-name" name="name" class="wcc-input" placeholder="<?php echo esc_attr(WCC_i18n::get('form.full_name_placeholder')); ?>"
                                required>
                        </div>
                        <span class="wcc-error-message" id="wcc-name-error"></span>
                    </div>

                    <div class="wcc-grid-row">
                        <!-- Correo electrónico -->
                        <div class="wcc-field-group">
                            <label for="wcc-email" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.email_label')); ?> <span class="wcc-required">*</span></label>
                            <div class="wcc-input-wrapper with-icon">
                                <span class="wcc-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                        </path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </span>
                                <input type="email" id="wcc-email" name="email" class="wcc-input"
                                    placeholder="<?php echo esc_attr(WCC_i18n::get('form.email_placeholder')); ?>" required>
                            </div>
                            <span class="wcc-error-message" id="wcc-email-error"></span>
                        </div>

                        <!-- Teléfono -->
                        <div class="wcc-field-group">
                            <label for="wcc-phone" class="wcc-label"><?php echo esc_html(WCC_i18n::get('form.phone_label_short')); ?> <span class="wcc-required">*</span></label>
                            <div class="wcc-input-wrapper with-icon">
                                <span class="wcc-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                        </path>
                                    </svg>
                                </span>
                                <input type="tel" id="wcc-phone" name="phone" class="wcc-input"
                                    placeholder="(555) 123-4567" required>
                            </div>
                            <span class="wcc-error-message" id="wcc-phone-error"></span>
                        </div>
                    </div>

                    <!-- Casilla de Consentimiento -->
                    <div class="wcc-field-group wcc-checkbox-group">
                        <label class="wcc-checkbox-wrapper">
                            <input type="checkbox" id="wcc-consent" name="consent" class="wcc-checkbox" required>
                            <span class="wcc-checkbox-custom">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                            <span class="wcc-checkbox-text">
                                <?php echo esc_html(WCC_i18n::get('form.consent_text')); ?> <span class="wcc-required">*</span>
                            </span>
                        </label>
                        <span class="wcc-error-message" id="wcc-consent-error"></span>
                    </div>

                    <!-- Acciones del Paso 2 -->
                    <div class="wcc-field-group wcc-nav-group">
                        <button type="button" class="wcc-btn wcc-btn-secondary wcc-back-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            <?php echo esc_html(WCC_i18n::get('form.back_button')); ?>
                        </button>
                        <button type="submit" class="wcc-submit-btn wcc-btn-primary" id="wcc-submit-btn">
                            <span class="wcc-btn-text"><?php echo esc_html(WCC_i18n::get('form.submit_button')); ?></span>
                            <span class="wcc-btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </span>
                            <span class="wcc-btn-loader" style="display: none;">
                                <span class="wcc-spinner"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

        </form>

        <!-- Sección de Resultados (Oculta inicialmente) -->
        <div id="wcc-results" class="wcc-results" style="display: none;">
            <!-- Los resultados se cargarán aquí vía AJAX -->
        </div>

    </div>
</div>