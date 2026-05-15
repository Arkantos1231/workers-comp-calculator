<?php
/**
 * Form Handler Class
 * Processes form submissions and handles AJAX requests
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WCC_Form_Handler
{

    /**
     * Initialize form handler
     */
    public static function init()
    {
        // Register AJAX handlers
        add_action('wp_ajax_wcc_calculate', array(__CLASS__, 'handle_calculation'));
        add_action('wp_ajax_nopriv_wcc_calculate', array(__CLASS__, 'handle_calculation'));
    }

    /**
     * Handle calculation AJAX request
     */
    public static function handle_calculation()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wcc_calculator_nonce')) {
            wp_send_json_error(['message' => WCC_i18n::get('server.err_missing')]);
            return;
        }

        // Sanitize and validate inputs
        $data = self::sanitize_form_data($_POST);

        if (!$data) {
            wp_send_json_error(['message' => WCC_i18n::get('server.err_invalid')]);
            return;
        }

        // Build Injuries Array
        $injuries = array();

        // 1. Physical Injuries
        if (isset($data['body_parts']) && is_array($data['body_parts'])) {
            foreach ($data['body_parts'] as $part) {
                if (!empty($part['part']) && isset($part['impairment'])) {
                    $injuries[] = array(
                        'part' => $part['part'],
                        'impairment' => $part['impairment']
                    );
                }
            }
        }

        // 2. Psychological
        if (!empty($data['psychological_claim']) && !empty($data['psych_impairment'])) {
            $injuries[] = array(
                'part' => 'Psychological',
                'impairment' => $data['psych_impairment']
            );
        }

        // 3. Occupational Disease
        if (!empty($data['occupational_disease']) && !empty($data['occupational_impairment'])) {
            $injuries[] = array(
                'part' => 'Occupational Disease',
                'impairment' => $data['occupational_impairment']
            );
        }

        // Calculate using Maryland Logic (Stacked) or Skip for AI-Only States
        $is_maryland = (empty($data['state']) || stripos($data['state'], 'Maryland') !== false);
        $result = array();
        $range_str = '';

        if ($is_maryland) {
            $result = WCC_Maryland_Logic::calculate(
                $data['date_injury'],
                $data['wage'],
                $injuries
            );

            // Calculate Settlement Range (90% to 105%)
            $val = $result['total_value'];
            $low_val = floor($val * 0.90);
            $high_val = ceil($val * 1.05);
            $range_str = '$' . number_format($low_val, 0) . ' – $' . number_format($high_val, 0);
        } else {
            // AI-Only Mode for other states
            $result = array(
                'total_value' => 0,
                'logic_tier' => WCC_i18n::get('general.ai_estimation'),
                'weakly_rate' => 0,
                'weeks_awarded' => 0,
                'ai_only' => true // Flag for frontend/AI
            );
            $range_str = WCC_i18n::get('general.calculating'); // Placeholder, AI will provide the real range
        }

        // AI Analysis (Hybrid)
        $ai_engine = new WCC_AI_Engine();
        // For non-Maryland, we rely entirely on AI for the number
        $ai_summary = $ai_engine->generate_analysis($data, $result, $data['state'], $is_maryland ? $range_str : null);

        // If not Maryland, try to extract the range from the AI response
        if (!$is_maryland && $ai_summary) {
            // Regex to find patterns like "$6,600 - $18,000" or "$35,622.96 - $35,622.96"
            if (preg_match('/\$[\d,]+(?:\.\d+)?\s*[\-–]\s*\$[\d,]+(?:\.\d+)?/', $ai_summary, $matches)) {
                $range_str = $matches[0];
            }
        }

        // Prepare response
        $response = array(
            'valuation_range' => ($is_maryland || $range_str !== WCC_i18n::get('general.calculating')) ? $range_str . (empty($ai_summary) ? '' : '*') : WCC_i18n::get('general.see_analysis_below'),
            'details' => $result,
            'ai_analysis' => $ai_summary, // Send to frontend to display
            'disclaimer' => $is_maryland
                ? str_replace('{logic_tier}', $result['logic_tier'], get_option('wcc_disclaimer_maryland', WCC_i18n::get('disclaimer.maryland')))
                : str_replace('{state}', $data['state'], get_option('wcc_disclaimer_other', WCC_i18n::get('disclaimer.other')))
        );

        // CRM Integration (Direct with LeadConnector / GoHighLevel API)
        $ghl_api_key = get_option('wcc_ghl_api_key');
        $ghl_location_id = get_option('wcc_ghl_location_id');

        if ($ghl_api_key && $ghl_location_id) {

            // Summarize Physical Injuries
            $physical_injuries_str = WCC_i18n::get('general.none');
            $injuries_parts = array();
            if (!empty($data['body_parts'])) {
                foreach ($data['body_parts'] as $bp) {
                    $injuries_parts[] = $bp['part'] . ' (' . $bp['impairment'] . '%)';
                }
                $physical_injuries_str = implode(', ', $injuries_parts);
            }

            // Psychological & Occ Disease Strings
            $psych_str = ($data['psych_impairment'] > 0) ? $data['psych_impairment'] . '%' : WCC_i18n::get('general.none');

            // Format Occupational Disease (Description + Percentage)
            $occ_str = WCC_i18n::get('general.none');
            if ($data['occupational_impairment'] > 0) {
                $occ_str = $data['occupational_impairment'] . '%';
                if (!empty($data['occupational_description'])) {
                    $occ_str = $data['occupational_description'] . ' (' . $occ_str . ')';
                }
            }

            // Master Summary
            $injuries_summary_text = WCC_i18n::get('server.note_physical') . ": " . $physical_injuries_str . ". " .
                WCC_i18n::get('server.note_psych') . ": " . $psych_str . ". " .
                WCC_i18n::get('server.note_occ') . ": " . $occ_str . ". " .
                WCC_i18n::get('server.note_est_value') . ": " . $response['valuation_range'];

            // Construct Note Content
            $note_content = WCC_i18n::get('server.note_title') . "\n" .
                WCC_i18n::get('server.note_state') . ": " . $data['state'] . "\n" .
                WCC_i18n::get('server.note_wage') . ": $" . $data['wage'] . "\n" .
                WCC_i18n::get('server.note_date') . ": " . $data['date_injury'] . "\n" .
                "\n" .
                WCC_i18n::get('server.note_physical_injuries') . ": " . $physical_injuries_str . "\n" .
                WCC_i18n::get('server.note_psychological') . ": " . $psych_str . "\n" .
                WCC_i18n::get('server.note_occupational_disease') . ": " . $occ_str . "\n" .
                "\n" .
                WCC_i18n::get('server.note_summary') . ": " . $injuries_summary_text . "\n" .
                "\n" .
                WCC_i18n::get('server.note_ai_analysis_start') . "\n" .
                $ai_summary . "\n" .
                WCC_i18n::get('server.note_ai_analysis_end');

            // Prepare Payload for Contacts API
            // Endpoint: POST https://services.leadconnectorhq.com/contacts/
            $payload = array(
                'locationId' => $ghl_location_id,
                'firstName' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'tags' => array('web-calculator-wcc-lead'),
                'source' => 'Workers Comp Calculator Plugin'
            );

            // Add Custom Fields if IDs are configured
            $custom_fields = array();

            $map = array(
                'wcc_ghl_field_state' => $data['state'],
                'wcc_ghl_field_date' => $data['date_injury'],
                'wcc_ghl_field_wage' => $data['wage'],
                'wcc_ghl_field_physical' => $physical_injuries_str,
                'wcc_ghl_field_psych' => $psych_str,
                'wcc_ghl_field_occ' => $occ_str,
                'wcc_ghl_field_summary' => $injuries_summary_text
            );

            foreach ($map as $option_name => $value) {
                $field_key = get_option($option_name);
                if (!empty($field_key)) {
                    $custom_fields[] = array(
                        'key' => trim($field_key),
                        'value' => $value
                    );
                }
            }

            if (!empty($custom_fields)) {
                $payload['customFields'] = $custom_fields;
            }

            // Split name if possible
            $name_parts = explode(' ', trim($data['name']), 2);
            if (count($name_parts) > 1) {
                $payload['firstName'] = $name_parts[0];
                $payload['lastName'] = $name_parts[1];
            }

            // 1. Create/Update Contact
            $api_url = 'https://services.leadconnectorhq.com/contacts/';

            // DEBUG LOGGING
            $log_file = plugin_dir_path(__FILE__) . '../ghl_debug.log';
            $log_entry = "---------------------------------\n";
            $log_entry .= "Time: " . date('Y-m-d H:i:s') . "\n";
            $log_entry .= "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);

            $ghl_response = wp_remote_post($api_url, array(
                'body' => json_encode($payload),
                'headers' => array(
                    'Authorization' => 'Bearer ' . $ghl_api_key,
                    'Version' => '2021-07-28',
                    'Content-Type' => 'application/json'
                ),
                'blocking' => true, // We need to wait to get the ID back for the note
                'timeout' => 15
            ));

            // Log Response
            $response_log = "Response Code: " . wp_remote_retrieve_response_code($ghl_response) . "\n";
            $response_log .= "Response Body: " . wp_remote_retrieve_body($ghl_response) . "\n";
            if (is_wp_error($ghl_response)) {
                $response_log .= "WP Error: " . $ghl_response->get_error_message() . "\n";
            }
            file_put_contents($log_file, $response_log, FILE_APPEND);

            // 2. Handle Response (Success or Duplicate)
            $response_code = wp_remote_retrieve_response_code($ghl_response);
            $body = json_decode(wp_remote_retrieve_body($ghl_response), true);
            $contact_id = null;

            // Case A: Success (Created)
            if (!is_wp_error($ghl_response) && $response_code < 300) {
                if (isset($body['contact']['id'])) {
                    $contact_id = $body['contact']['id'];
                }
            }
            // Case B: Duplicate (400) -> We must UPDATE existing contact
            elseif ($response_code === 400 && isset($body['meta']['contactId'])) {
                $contact_id = $body['meta']['contactId'];

                // Perform PUT request to update existing contact
                $update_url = $api_url . $contact_id;

                // Remove locationId for PUT request
                $put_payload = $payload;
                unset($put_payload['locationId']);

                $put_response = wp_remote_request($update_url, array(
                    'method' => 'PUT',
                    'body' => json_encode($put_payload),
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $ghl_api_key,
                        'Version' => '2021-07-28',
                        'Content-Type' => 'application/json'
                    ),
                    'blocking' => true,
                    'timeout' => 15
                ));

                // Log PUT Response
                $put_body = wp_remote_retrieve_body($put_response);
                $put_log = "PUT Update Response: " . wp_remote_retrieve_response_code($put_response) . "\n";
                $put_log .= "PUT Body: " . $put_body . "\n";

                if (is_wp_error($put_response)) {
                    $put_log .= "PUT Error: " . $put_response->get_error_message() . "\n";
                }
                file_put_contents($log_file, $put_log, FILE_APPEND);
            }

            // 3. Add Note (if we have a valid Contact ID)
            if ($contact_id) {
                // Add Note to Contact
                // Endpoint: POST https://services.leadconnectorhq.com/contacts/{id}/notes
                $note_url = "https://services.leadconnectorhq.com/contacts/{$contact_id}/notes";
                wp_remote_post($note_url, array(
                    'body' => json_encode(array(
                        'body' => $note_content
                    )),
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $ghl_api_key,
                        'Version' => '2021-07-28',
                        'Content-Type' => 'application/json'
                    ),
                    'blocking' => false
                ));
            }
        }

        wp_send_json_success($response);
    }

    /**
     * Sanitize form data
     */
    private static function sanitize_form_data($post_data)
    {
        $data = array();

        // Required fields
        $required_fields = array(
            'state',
            'date_injury',
            'date_injury',
            // 'body_part', // Now part of repeater
            // 'impairment', // Now part of repeater
            'wage',
            'name',
            'email',
            'phone'
        );

        foreach ($required_fields as $field) {
            if (!isset($post_data[$field]) || empty(trim($post_data[$field]))) {
                return false; // Missing required field
            }
        }

        // Sanitize each field
        // Sanitize each field
        $data['state'] = sanitize_text_field($post_data['state']);
        $data['date_injury'] = sanitize_text_field($post_data['date_injury']);

        // Sanitize Body Parts Repeater
        $data['body_parts'] = array();
        if (isset($post_data['body_parts']) && is_array($post_data['body_parts'])) {
            foreach ($post_data['body_parts'] as $bp) {
                if (isset($bp['part']) && isset($bp['impairment'])) {
                    $data['body_parts'][] = array(
                        'part' => sanitize_text_field($bp['part']),
                        'impairment' => floatval($bp['impairment'])
                    );
                }
            }
        }

        $data['wage'] = floatval($post_data['wage']);

        // Sanitize Psych
        $data['psychological_claim'] = isset($post_data['psychological_claim']) ? 1 : 0;
        $data['psych_impairment'] = ($data['psychological_claim'] === 1 && isset($post_data['psych_impairment']))
            ? floatval($post_data['psych_impairment'])
            : 0;

        // Sanitize Occ Disease
        $data['occupational_disease'] = isset($post_data['occupational_disease']) ? 1 : 0;
        $data['occupational_impairment'] = ($data['occupational_disease'] === 1 && isset($post_data['occupational_impairment']))
            ? floatval($post_data['occupational_impairment'])
            : 0;

        // Reset description if not active
        $data['occupational_description'] = ($data['occupational_disease'] === 1 && isset($post_data['occupational_description']))
            ? sanitize_textarea_field($post_data['occupational_description'])
            : '';

        $data['name'] = sanitize_text_field($post_data['name']);
        $data['email'] = sanitize_email($post_data['email']);
        $data['phone'] = sanitize_text_field($post_data['phone']);
        $data['consent'] = isset($post_data['consent']) ? 1 : 0;

        // Validate email
        if (!is_email($data['email'])) {
            return false;
        }



        // Validate wage (must be positive)
        if ($data['wage'] <= 0) {
            return false;
        }

        // Validate date
        $date = strtotime($data['date_injury']);
        if (!$date || $date > time()) {
            return false; // Invalid date or future date
        }

        return $data;
    }


}

// Initialize form handler
WCC_Form_Handler::init();

