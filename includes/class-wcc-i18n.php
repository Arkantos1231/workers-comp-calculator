<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCC_i18n
{
    private static $lang = null;
    private static $strings = null;

    /**
     * Get the current language code
     * 
     * @return string 'en' or 'es'
     */
    public static function get_lang()
    {
        if (self::$lang === null) {
            self::$lang = get_option('wcc_calculator_language', 'en'); 
        }
        return self::$lang;
    }

    /**
     * Set language explicitly (useful for forcing emails or API requests)
     * 
     * @param string $lang 'en' or 'es'
     */
    public static function set_lang($lang)
    {
        self::$lang = in_array($lang, ['en', 'es']) ? $lang : 'en';
    }

    /**
     * Get a translated string by key
     * 
     * @param string $key The key in the dictionary
     * @return string The translated string, or the key itself if not found
     */
    public static function get($key)
    {
        if (self::$strings === null) {
            self::load_strings();
        }

        $lang = self::get_lang();

        // Support dot notation like 'step1.title'
        $keys = explode('.', $key);
        $value = self::$strings[$lang];

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $key; // Return key if not found
            }
        }

        return $value;
    }

    /**
     * Load the massive dictionary into memory
     */
    private static function load_strings()
    {
        self::$strings = [
            'en' => [
                'general' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                    'none' => 'None',
                    'next_step' => 'Next Step',
                    'back' => 'Back',
                    'submit' => 'Calculate Now',
                    'calculating' => 'Calculating...',
                    'see_analysis_below' => 'See AI analysis below',
                    'ai_estimation' => 'AI Estimation',
                    'seo_calc_title' => 'Workers Comp Settlement Calculator - Estimate Your Case Free',
                    'seo_calc_desc' => 'Use our free AI-powered {state} workers comp calculator to estimate your injury settlement value. Get a detailed analysis instantly.',
                    'default_state_name' => 'Workers Comp'
                ],
                'progress' => [
                    'step1' => '1. Injury Information',
                    'step2' => '2. Your Estimate'
                ],
                'form' => [
                    'title' => 'Workers\' Compensation Calculator',
                    'subtitle_state' => 'Estimate the value of your claim for',
                    'subtitle_generic' => 'Estimate the value of your workers\' compensation claim',
                    'step1_title' => 'Select Your State',
                    'step1_desc' => 'Workers\' compensation laws vary significantly by state. Select where your injury occurred.',
                    'state_label' => 'State of Injury',
                    'state_placeholder' => '- Select State -',
                    'state_search_placeholder' => 'Search state...',
                    'state_select_default' => 'Select State',
                    'date_label' => 'Date of Injury',
                    'date_placeholder' => 'MM/DD/YYYY',
                    'step2_title' => 'Average Weekly Wage',
                    'step2_desc' => 'Your settlement depends partially on your earnings before the injury.',
                    'wage_label' => 'Average Weekly Wage (Gross)',
                    'wage_prefix' => '$',
                    'wage_help' => 'Enter your gross weekly wages before taxes. If your pay varies, average your last 14 weeks.',
                    'wage_help_text' => 'Average gross wages over the 14 weeks prior to injury',
                    'step3_title' => 'Your Injuries',
                    'step3_desc' => 'Add all body parts affected by the work accident and the percentage of impairment (if known).',
                    'physical_injuries_heading' => 'Physical Injuries',
                    'injury_label' => 'Body Part',
                    'body_part_label' => 'Body Part',
                    'injury_placeholder' => 'e.g., Left Knee, Lower Back',
                    'body_part_placeholder' => 'Search body part...',
                    'impairment_label' => 'Impairment %',
                    'impairment_percentage_label' => 'Impairment %',
                    'impairment_help' => 'Leave at 0 if unknown',
                    'add_injury_btn' => '+ Add Another Body Part',
                    'add_body_part_button' => 'Add Another Body Part',
                    'psych_claim_label' => 'Are you claiming a psychological condition related to this injury?',
                    'psychological_claim_toggle' => 'Is there a Psychological Disability?',
                    'psych_impairment_label' => 'Psychiatric Impairment % (if known)',
                    'psychological_impairment_label' => 'Psychological Impairment %',
                    'occ_disease_label' => 'Is this an occupational disease claim (e.g., hearing loss from constant noise, repetitive stress)?',
                    'occupational_disease_toggle' => 'Is an Occupational Disease',
                    'occ_impairment_label' => 'Occupational Impairment % (if known)',
                    'occupational_impairment_label' => 'Disease Impairment %',
                    'occupational_description_label' => 'Description (Optional)',
                    'occupational_description_placeholder' => 'Describe the condition in detail...',
                    'step4_title' => 'Almost there!',
                    'step4_desc' => 'Enter your details below to instantly view your case valuation range.',
                    'fname_label' => 'First Name',
                    'lname_label' => 'Last Name',
                    'email_label' => 'Email Address',
                    'phone_label' => 'Phone Number',
                    'consent_label' => 'I agree to the Terms of Service and Privacy Policy and consent to receive communications regarding my inquiry.',
                    'phone_help' => 'For SMS updates or a consultation.',
                    'step2_heading' => 'Where should we send your estimate?',
                    'step2_subheading' => 'Enter your details below to receive your personalized estimate instantly.',
                    'full_name_label' => 'Full Name',
                    'full_name_placeholder' => 'John Doe',
                    'email_placeholder' => 'john@example.com',
                    'phone_label_short' => 'Phone',
                    'consent_text' => 'I consent to being contacted by Pinder Plotkin about my workers\' compensation claim analysis',
                    'back_button' => 'Back',
                    'submit_button' => 'Calculate Value',
                    'loading_text' => 'Analyzing case data...',
                ],
                'js' => [
                    'req_state' => 'Please select your state.',
                    'req_date' => 'Please enter a valid date of injury.',
                    'req_wage' => 'Please enter a valid weekly wage.',
                    'req_fname' => 'First name is required.',
                    'req_lname' => 'Last name is required.',
                    'req_email' => 'Please enter a valid email address.',
                    'req_phone' => 'Please enter a valid 10-digit phone number.',
                    'req_consent' => 'You must agree to the terms to proceed.',
                    'req_field' => 'This field is required.',
                    'req_impairment' => 'Impairment must be between 0 and 100.',
                    'req_date_format' => 'Please enter a valid date in MM/DD/YYYY format.',
                    'req_date_future' => 'The date of injury cannot be in the future.',
                    'add_injury' => 'Please fill out the body part before adding another.',
                    'remove_part_aria' => 'Remove body part',
                    'body_part_label' => 'Body Part',
                    'search_body_part_placeholder' => 'Search body part...',
                    'impairment_label' => 'Impairment %',
                    'select_state_placeholder' => 'Select State...',
                    'calc_title' => 'Your Settlement Estimate',
                    'results_title' => 'Estimated Settlement Range',
                    'results_label' => 'Potential Compensation Value',
                    'calc_label' => 'Estimated Valuation Range:',
                    'cta_title' => 'Get a Free Legal Review',
                    'cta_button' => 'Review My Case for Free',
                    'cta_subtext' => 'Talk to a lawyer to maximize your claim.',
                    'cta_desc' => 'Settlements can be complicated. Our team can help you get the maximum value for your injuries. Would you like a free consultation?',
                    'btn_call' => 'Call Us Now',
                    'btn_text' => 'Text Us',
                    'evaluating' => 'Evaluating...',
                    'error_prefix' => 'Error: ',
                    'error_generic' => 'An unexpected error occurred.',
                    'error_network' => 'A network error occurred. Please try again or contact us for assistance.',
                    'err_network' => 'A network error occurred. Please try again or contact us for assistance.',
                    'parts' => [
                        'Head', 'Neck (Cervical Spine)', 'Upper Back (Thoracic Spine)', 'Lower Back (Lumbar Spine)',
                        'Left Shoulder', 'Right Shoulder', 'Left Arm', 'Right Arm', 'Left Elbow', 'Right Elbow',
                        'Left Wrist', 'Right Wrist', 'Left Hand', 'Right Hand', 'Left Thumb', 'Right Thumb',
                        'Left Fingers', 'Right Fingers', 'Left Hip', 'Right Hip', 'Left Leg', 'Right Leg',
                        'Left Knee', 'Right Knee', 'Left Ankle', 'Right Ankle', 'Left Foot', 'Right Foot',
                        'Left Great Toe', 'Right Great Toe', 'Left Other Toes', 'Right Other Toes',
                        'Vision (Left Eye)', 'Vision (Right Eye)', 'Hearing (Left Ear)', 'Hearing (Right Ear)',
                        'Lungs/Respiratory', 'Heart', 'Abdomen', 'Pelvis', 'Disfigurement/Scarring'
                    ]
                ],
                'seo' => [
                    'faq_q1' => 'How much is my workers comp case worth in {state}?',
                    'faq_a1' => 'Settlement values in {state} depend on your wage, injury type, and impairment rating (PPD). Our AI-powered calculator analyzes {state} guidelines to provide an estimated range.',
                    'faq_q2' => 'Is this estimate free and accurate?',
                    'faq_a2' => 'Yes, the calculator is 100% free. While it provides an estimate based on statutory guidelines, it does not replace professional legal advice.',
                    'software_app_name' => 'Workers Comp Calculator - {state}',
                    'software_app_desc' => 'Estimate the workers comp settlement value in {state} with our advanced AI calculator.',
                    'author_name' => 'Pinder Plotkin Legal Team',
                    'page_title' => '{state} Workers Comp Calculator | Pinder Plotkin'
                ],
                'cpt' => [
                    'name' => 'State Calculators',
                    'singular_name' => 'State Calculator',
                    'menu_name' => 'State Calculators',
                    'name_admin_bar' => 'State Calculator',
                    'archives' => 'Calculator Archives',
                    'attributes' => 'Calculator Attributes',
                    'parent_item_colon' => 'Parent Calculator:',
                    'all_items' => 'All Calculators',
                    'add_new_item' => 'Add New Calculator',
                    'add_new' => 'Add New',
                    'new_item' => 'New Calculator',
                    'edit_item' => 'Edit Calculator',
                    'update_item' => 'Update Calculator',
                    'view_item' => 'View Calculator',
                    'view_items' => 'View Calculators',
                    'search_items' => 'Search Calculator',
                    'not_found' => 'Not found',
                    'not_found_in_trash' => 'Not found in Trash',
                    'featured_image' => 'Featured Image',
                    'set_featured_image' => 'Set featured image',
                    'remove_featured_image' => 'Remove featured image',
                    'use_featured_image' => 'Use as featured image',
                    'insert_into_item' => 'Insert into calculator',
                    'uploaded_to_this_item' => 'Uploaded to this calculator',
                    'items_list' => 'Calculators list',
                    'items_list_navigation' => 'Calculators list navigation',
                    'filter_items_list' => 'Filter calculators list',
                    'description' => 'State-specific calculator pages',
                    'meta_box_title' => 'State Configuration',
                    'meta_state_name_label' => 'Full State Name (e.g., Maryland)',
                    'meta_state_name_desc' => 'Used for dynamic page titles and meta tags.',
                    'meta_url_label' => 'Calculator URL:',
                    'default_post_title' => 'Main Maryland Calculator'
                ],
                'server' => [
                    'err_invalid' => 'Invalid request.',
                    'err_missing' => 'Missing required fields.',
                    'err_calculation' => 'Calculation error. Please verify your inputs.',
                    'err_ai' => 'Unable to generate analysis at this time.',
                    'note_title' => 'Workers Comp Calculator Submission',
                    'note_state' => 'State',
                    'note_date' => 'Date of Injury',
                    'note_wage' => 'Average Weekly Wage',
                    'note_injuries' => 'Physical Injuries',
                    'note_psych' => 'Psychological Claim',
                    'note_occ' => 'Occupational Disease',
                    'note_val' => 'Estimated Valuation',
                    'note_physical' => 'Physical',
                    'note_est_value' => 'Estimated Value',
                    'note_physical_injuries' => 'Physical Injuries',
                    'note_psychological' => 'Psychological',
                    'note_occupational_disease' => 'Occupational Disease',
                    'note_summary' => 'Summary',
                    'note_ai_analysis_start' => '--- AI Analysis ---',
                    'note_ai_analysis_end' => '--- End of Analysis ---',
                ],
                'disclaimer' => [
                    'maryland' => 'This estimate is based on the Maryland Workers\' Compensation Commission guidelines. The calculated value assumes a {logic_tier}. Actual settlement values may vary.',
                    'other' => 'This estimate is generated by AI based on {state} statutory guidelines. Actual values may vary.',
                ],
                'maryland' => [
                    'tier1' => 'Tier 1 (Minor Disability)',
                    'tier2' => 'Tier 2 (Regular Disability)',
                    'serious' => 'Serious Disability',
                ],
                'admin' => [
                    'menu_title' => 'Workers\' Comp',
                    'menu_calc' => 'Workers\' Comp Calculator',
                    'menu_settings' => 'Calculator Settings',
                    'menu_kb' => 'State Knowledge Bases',
                    'title_settings' => 'Workers\' Comp Calculator — Settings',
                    'title_kb' => 'Workers\' Comp Calculator — State Knowledge Bases',
                    'lang_label' => 'Calculator Language',
                    'lang_desc' => 'Select the primary language for the frontend calculator public forms.',
                    'logo_label' => 'Header Logo',
                    'logo_btn' => 'Select Logo',
                    'logo_remove' => 'Remove Logo',
                    'logo_desc' => 'Upload or select an image from the Media Library. If empty, the default icon is shown.',
                    'bg_label' => 'Logo Container Background',
                    'bg_desc' => 'Accepts any valid CSS color. Leave empty to use the default.',
                    'openai_label' => 'OpenAI API Key',
                    'openai_desc' => 'Enter your OpenAI API Key (sk-...).',
                    'model_label' => 'OpenAI Model',
                    'model_desc' => 'Select the AI model to use for calculations. "Mini" models are significantly cheaper.',
                    'ghl_api_label' => 'GoHighLevel (LeadConnector) API',
                    'ghl_api_desc' => 'Connect directly to the GHL Contacts API.',
                    'ghl_key' => 'API Key (Bearer Token)',
                    'ghl_loc' => 'Location ID',
                    'ghl_keys_title' => 'Custom Field Keys (Optional)',
                    'ghl_keys_desc' => 'To populate specific fields, enter their Key below. Note: Do not include "contact." prefix.',
                    'field_state' => 'State Calculator:',
                    'field_date' => 'Date of Injury:',
                    'field_wage' => 'Avg Weekly Wage:',
                    'field_phys' => 'Physical Injuries:',
                    'field_psych' => 'Psych Impairment:',
                    'field_occ' => 'Occ Disease:',
                    'field_sum' => 'Injuries Summary:',
                    'sys_prompt' => 'System Prompt Template',
                    'sys_prompt_desc' => 'Available variables: {state}, {injury_data}, {math_result}.',
                    'disc_md' => 'Disclaimer — Maryland',
                    'disc_md_desc' => 'Shown after Maryland calculations. Available variable: {logic_tier}.',
                    'disc_other' => 'Disclaimer — Other States',
                    'disc_other_desc' => 'Shown after non-Maryland calculations. Available variable: {state}.',
                    'kb_desc' => 'Enter state-specific laws, rules, and guidelines for the AI to use when estimating case values. Available variables: {state}, {injury_data}, {math_result}.',
                    'kb_filter' => 'Filter states...',
                    'kb_placeholder' => 'Enter statutes, rules, and guidelines for',
                    'btn_save' => 'Save Changes',
                    'btn_save_kb' => 'Save Knowledge Bases'
                ]
            ],
            'es' => [
                'general' => [
                    'yes' => 'Sí',
                    'no' => 'No',
                    'none' => 'Ninguno',
                    'next_step' => 'Siguiente Paso',
                    'back' => 'Atrás',
                    'submit' => 'Calcular Ahora',
                    'calculating' => 'Calculando...',
                    'see_analysis_below' => 'Ver análisis de IA abajo',
                    'ai_estimation' => 'Estimación por IA',
                    'seo_calc_title' => 'Calculadora de Liquidación de Compensación Laboral - Calcule Su Caso Gratis',
                    'seo_calc_desc' => 'Utilice nuestra calculadora gratuita de compensación laboral de {state} basada en IA para estimar el valor de liquidación de su lesión. Obtenga un análisis detallado al instante.',
                    'default_state_name' => 'Compensación Laboral'
                ],
                'progress' => [
                    'step1' => '1. Información de Lesión',
                    'step2' => '2. Tu Estimación'
                ],
                'form' => [
                    'title' => 'Calculadora de Compensación Laboral',
                    'subtitle_state' => 'Estima el valor de tu reclamo para',
                    'subtitle_generic' => 'Estima el valor de tu reclamo de compensación laboral',
                    'step1_title' => 'Selecciona Tu Estado',
                    'step1_desc' => 'Las leyes de compensación laboral varían según el estado. Selecciona dónde ocurrió la lesión.',
                    'state_label' => 'Estado de la Lesión',
                    'state_placeholder' => '- Seleccionar Estado -',
                    'state_search_placeholder' => 'Buscar estado...',
                    'state_select_default' => 'Seleccionar Estado',
                    'date_label' => 'Fecha de la Lesión',
                    'date_placeholder' => 'MM/DD/AAAA',
                    'step2_title' => 'Salario Semanal Promedio',
                    'step2_desc' => 'Tu cálculo depende en parte de tus ingresos antes de la lesión.',
                    'wage_label' => 'Salario Semanal Promedio (Bruto)',
                    'wage_prefix' => '$',
                    'wage_help' => 'Ingresa tu salario semanal bruto antes de impuestos. Si tu pago varía, promedia tus últimas 14 semanas.',
                    'wage_help_text' => 'Salario bruto promediado durante las 14 semanas previas a la lesión',
                    'step3_title' => 'Tus Lesiones',
                    'step3_desc' => 'Agrega todas las partes del cuerpo afectadas por el accidente de trabajo y el porcentaje de discapacidad (si lo conoces).',
                    'physical_injuries_heading' => 'Lesiones Físicas',
                    'injury_label' => 'Parte del Cuerpo',
                    'body_part_label' => 'Parte del Cuerpo',
                    'injury_placeholder' => 'ej., Rodilla Izquierda, Espalda Baja',
                    'body_part_placeholder' => 'Buscar parte del cuerpo...',
                    'impairment_label' => '% de Incapacidad',
                    'impairment_percentage_label' => '% de Incapacidad',
                    'impairment_help' => 'Déjalo en 0 si no lo sabes',
                    'add_injury_btn' => '+ Agregar Otra Parte del Cuerpo',
                    'add_body_part_button' => 'Agregar Otra Parte del Cuerpo',
                    'psych_claim_label' => '¿Reclamas alguna condición psicológica relacionada con esta lesión?',
                    'psychological_claim_toggle' => '¿Hay una Incapacidad Psicológica?',
                    'psych_impairment_label' => '% de Incapacidad Psiquiátrica (opcional)',
                    'psychological_impairment_label' => '% de Incapacidad Psicológica',
                    'occ_disease_label' => '¿Es un reclamo por enfermedad ocupacional (ej. pérdida de audición, estrés repetitivo)?',
                    'occupational_disease_toggle' => 'Es una Enfermedad Ocupacional',
                    'occ_impairment_label' => '% de Incapacidad Ocupacional (opcional)',
                    'occupational_impairment_label' => '% de Incapacidad por Enfermedad',
                    'occupational_description_label' => 'Descripción (Opcional)',
                    'occupational_description_placeholder' => 'Describe la condición en detalle...',
                    'step4_title' => '¡Casi terminamos!',
                    'step4_desc' => 'Ingresa tus datos a continuación para ver de inmediato tu rango de liquidación.',
                    'fname_label' => 'Nombre',
                    'lname_label' => 'Apellido',
                    'email_label' => 'Correo Electrónico',
                    'phone_label' => 'Número de Teléfono',
                    'consent_label' => 'Acepto los Términos de Servicio y la Política de Privacidad y acepto recibir comunicaciones sobre mi consulta.',
                    'phone_help' => 'Para actualizaciones por SMS o una consulta.',
                    'step2_heading' => '¿A dónde enviamos tu estimación?',
                    'step2_subheading' => 'Ingresa tus datos a continuación para recibir tu cálculo personalizado al instante.',
                    'full_name_label' => 'Nombre Completo',
                    'full_name_placeholder' => 'Juan Pérez',
                    'email_placeholder' => 'juan@ejemplo.com',
                    'phone_label_short' => 'Teléfono',
                    'consent_text' => 'Doy mi consentimiento para ser contactado por Pinder Plotkin sobre el análisis de mi reclamo de compensación laboral',
                    'back_button' => 'Volver',
                    'submit_button' => 'Calcular Valor',
                    'loading_text' => 'Analizando datos del caso...',
                ],
                'js' => [
                    'req_state' => 'Por favor selecciona tu estado.',
                    'req_date' => 'Por favor ingresa una fecha de lesión válida.',
                    'req_wage' => 'Por favor ingresa un salario semanal válido.',
                    'req_fname' => 'El nombre es obligatorio.',
                    'req_lname' => 'El apellido es obligatorio.',
                    'req_email' => 'Por favor ingresa un correo electrónico válido.',
                    'req_phone' => 'Por favor ingresa un número de teléfono válido de 10 dígitos.',
                    'req_consent' => 'Debes aceptar los términos para continuar.',
                    'req_field' => 'Este campo es obligatorio.',
                    'req_impairment' => 'La incapacidad debe estar entre 0 y 100.',
                    'req_date_format' => 'Por favor ingresa una fecha válida en formato MM/DD/AAAA.',
                    'req_date_future' => 'La fecha de lesión no puede ser en el futuro.',
                    'add_injury' => 'Por favor, completa la parte del cuerpo antes de agregar otra.',
                    'remove_part_aria' => 'Eliminar parte del cuerpo',
                    'body_part_label' => 'Parte del Cuerpo',
                    'search_body_part_placeholder' => 'Buscar parte del cuerpo...',
                    'impairment_label' => '% de Incapacidad',
                    'select_state_placeholder' => 'Seleccionar Estado...',
                    'calc_title' => 'Tu Estimación de Liquidación',
                    'results_title' => 'Rango de Liquidación Estimado',
                    'results_label' => 'Valor de Compensación Potencial',
                    'calc_label' => 'Rango de Liquidación Estimado:',
                    'cta_title' => 'Obtén una Revisión Legal Gratuita',
                    'cta_button' => 'Revisar Mi Caso sin Costo',
                    'cta_subtext' => 'Habla con un abogado para maximizar tu reclamo.',
                    'cta_desc' => 'Las liquidaciones pueden ser complicadas. Nuestro equipo puede ayudarte a obtener el valor máximo por tus lesiones. ¿Te gustaría una consulta gratuita?',
                    'btn_call' => 'Llámanos Ahora',
                    'btn_text' => 'Escríbenos por SMS',
                    'evaluating' => 'Evaluando...',
                    'error_prefix' => 'Error: ',
                    'error_generic' => 'Ocurrió un error inesperado.',
                    'error_network' => 'Ocurrió un error de red. Por favor intenta nuevamente o contáctanos para recibir ayuda.',
                    'err_network' => 'Ocurrió un error de red. Por favor intenta nuevamente o contáctanos para recibir ayuda.',
                    'parts' => [
                        'Cabeza', 'Cuello (Columna Cervical)', 'Espalda Alta (Columna Torácica)', 'Espalda Baja (Columna Lumbar)',
                        'Hombro Izquierdo', 'Hombro Derecho', 'Brazo Izquierdo', 'Brazo Derecho', 'Codo Izquierdo', 'Codo Derecho',
                        'Muñeca Izquierda', 'Muñeca Derecha', 'Mano Izquierda', 'Mano Derecha', 'Pulgar Izquierdo', 'Pulgar Derecho',
                        'Dedos (Mano Izquierda)', 'Dedos (Mano Derecha)', 'Cadera Izquierda', 'Cadera Derecha', 'Pierna Izquierda', 'Pierna Derecha',
                        'Rodilla Izquierda', 'Rodilla Derecha', 'Tobillo Izquierdo', 'Tobillo Derecho', 'Pie Izquierdo', 'Pie Derecho',
                        'Dedo Gordo (Pie Izquierdo)', 'Dedo Gordo (Pie Derecho)', 'Otros Dedos (Pie Izquierdo)', 'Otros Dedos (Pie Derecho)',
                        'Visión (Ojo Izquierdo)', 'Visión (Ojo Derecho)', 'Audición (Oído Izquierdo)', 'Audición (Oído Derecho)',
                        'Pulmones/Respiratorio', 'Corazón', 'Abdomen', 'Pelvis', 'Desfiguración/Cicatrices'
                    ]
                ],
                'seo' => [
                    'faq_q1' => '¿Cuánto vale mi caso de compensación laboral en {state}?',
                    'faq_a1' => 'Los valores de liquidación en {state} dependen de su salario, el tipo de lesión y el grado de incapacidad (PPD). Nuestra calculadora impulsada por IA analiza las pautas de {state} para proporcionar un rango estimado.',
                    'faq_q2' => '¿Es gratuita y precisa esta estimación?',
                    'faq_a2' => 'Sí, la calculadora es 100% gratuita. Si bien proporciona una estimación basada en datos confiables, no reemplaza el asesoramiento legal profesional.',
                    'software_app_name' => 'Calculadora de Compensación Laboral - {state}',
                    'software_app_desc' => 'Estima el valor de liquidación de compensación laboral en {state} con nuestra calculadora avanzada de IA.',
                    'author_name' => 'Equipo Legal de Pinder Plotkin',
                    'page_title' => 'Calculadora de Compensación Laboral de {state} | Pinder Plotkin'
                ],
                'server' => [
                    'err_invalid' => 'Solicitud inválida.',
                    'err_missing' => 'Faltan campos obligatorios.',
                    'err_calculation' => 'Error de cálculo. Por favor verifica tus datos.',
                    'err_ai' => 'No se puede generar el análisis en este momento.',
                    'note_title' => 'Evaluación - Calculadora Workers Comp',
                    'note_state' => 'Estado',
                    'note_date' => 'Fecha de Lesión',
                    'note_wage' => 'Salario Semanal Promedio',
                    'note_injuries' => 'Lesiones Físicas',
                    'note_psych' => 'Reclamo Psicológico',
                    'note_occ' => 'Enfermedad Ocupacional',
                    'note_val' => 'Liquidación Estimada',
                    'note_physical' => 'Físicas',
                    'note_est_value' => 'Valor Estimado',
                    'note_physical_injuries' => 'Lesiones Físicas',
                    'note_psychological' => 'Psicológico',
                    'note_occupational_disease' => 'Enfermedad Ocupacional',
                    'note_summary' => 'Resumen',
                    'note_ai_analysis_start' => '--- Análisis de IA ---',
                    'note_ai_analysis_end' => '--- Fin del Análisis ---',
                ],
                'disclaimer' => [
                    'maryland' => 'Esta estimación se basa en las pautas de la Comisión de Compensación Laboral de Maryland. El valor calculado asume un {logic_tier}. Los valores reales pueden variar.',
                    'other' => 'Esta estimación fue generada por IA basada en las pautas legales de {state}. Los valores reales pueden variar.',
                ],
                'maryland' => [
                    'tier1' => 'Nivel 1 (Incapacidad Menor)',
                    'tier2' => 'Nivel 2 (Incapacidad Ordinaria)',
                    'serious' => 'Incapacidad Grave',
                ],
                'admin' => [
                    'menu_title' => 'Workers\' Comp',
                    'menu_calc' => 'Workers\' Comp Calculator',
                    'menu_settings' => 'Ajustes de Calculadora',
                    'menu_kb' => 'Bases de Conocimiento',
                    'title_settings' => 'Calculadora Workers\' Comp — Ajustes',
                    'title_kb' => 'Calculadora Workers\' Comp — Bases de Conocimiento',
                    'lang_label' => 'Idioma de la Calculadora',
                    'lang_desc' => 'Selecciona el idioma principal para el formulario público.',
                    'logo_label' => 'Logo del Encabezado',
                    'logo_btn' => 'Seleccionar Logo',
                    'logo_remove' => 'Quitar Logo',
                    'logo_desc' => 'Sube o selecciona una imagen de la Biblioteca. Si está vacío, se muestra el icono por defecto.',
                    'bg_label' => 'Fondo del Icono/Logo',
                    'bg_desc' => 'Acepta cualquier color CSS válido. Deja vacío para usar el valor por defecto.',
                    'openai_label' => 'Clave API de OpenAI',
                    'openai_desc' => 'Ingresa tu clave de API de OpenAI (sk-...).',
                    'model_label' => 'Modelo de OpenAI',
                    'model_desc' => 'Selecciona el modelo de IA a usar. Los modelos "Mini" son considerablemente más económicos.',
                    'ghl_api_label' => 'API de GoHighLevel (LeadConnector)',
                    'ghl_api_desc' => 'Conecta directamente a la API de Contactos de GHL.',
                    'ghl_key' => 'Clave API (Token Bearer)',
                    'ghl_loc' => 'ID de Ubicación (Location ID)',
                    'ghl_keys_title' => 'Claves de Campos Personalizados (Opcional)',
                    'ghl_keys_desc' => 'Para enviar datos a campos específicos, ingresa la clave externa. Nota: No incluyas el prefijo "contact.".',
                    'field_state' => 'Calculadora de Estado:',
                    'field_date' => 'Fecha de Lesión:',
                    'field_wage' => 'Salario Promedio Semanal:',
                    'field_phys' => 'Lesiones Físicas:',
                    'field_psych' => 'Incapacidad Psicológica:',
                    'field_occ' => 'Enfermedad Ocupacional:',
                    'field_sum' => 'Resumen de Lesiones:',
                    'sys_prompt' => 'Plantilla del Prompt del Sistema (IA)',
                    'sys_prompt_desc' => 'Variables disponibles: {state}, {injury_data}, {math_result}.',
                    'disc_md' => 'Descargo de Responsabilidad — Maryland',
                    'disc_md_desc' => 'Se muestra tras los cálculos de Maryland. Variable disponible: {logic_tier}.',
                    'disc_other' => 'Descargo de Responsabilidad — Otros Estados',
                    'disc_other_desc' => 'Se muestra tras los cálculos generados por IA. Variable disponible: {state}.',
                    'kb_desc' => 'Ingresa leyes, estatutos y pautas específicas de cada estado para que la IA las use al estimar casos. Variables disponibles: {state}, {injury_data}, {math_result}.',
                    'kb_filter' => 'Filtrar estados...',
                    'kb_placeholder' => 'Ingresa estatutos o reglas para',
                    'btn_save' => 'Guardar Cambios',
                    'btn_save_kb' => 'Guardar Bases de Conocimiento'
                ]
            ]
        ];
    }
}
