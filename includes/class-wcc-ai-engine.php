<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCC_AI_Engine
{

    private $api_key;
    private $model;

    public function __construct()
    {
        $this->api_key = get_option('wcc_openai_api_key');
        $this->model = get_option('wcc_openai_model', 'gpt-4o');
    }

    /**
     * Generate valuation analysis using OpenAI
     *
     * @param array $inputs The form input data
     * @param array $math_result The result from the PHP calculator (val, tiers, etc)
     * @param string $state State name (default Maryland)
     * @param string|null $range_str Pre-calculated range string (optional)
     * @return string|false The AI analysis text or false on failure
     */
    public function generate_analysis($inputs, $math_result, $state = 'Maryland', $range_str = null)
    {
        if (empty($this->api_key)) {
            return false;
        }

        $is_maryland = (empty($state) || stripos($state, 'Maryland') !== false);

        // 1. Get Knowledge Base (System Prompt) from Settings
        $db_system_prompt = get_option('wcc_kb_' . sanitize_title($state), '');

        // 2. Define System Prompt based on State
        $system_prompt = "";

        if ($is_maryland) {
            // MARYLAND: Use strict formatting to wrapper the Math Result
            $system_prompt = "You are a Case Value Estimator.
Context:
statutory_value: {math_result}
injury_data: {injury_data}

Rules:
1. OUTPUT ONLY THE RANGE.
2. DO NOT output headers (###).
3. Result must match the statutory value provided nearby.
4. DO NOT show math steps.
5. Provide the output completely in [TARGET_LANGUAGE].

Required Output Format:
\"[TARGET_FORMAT]\"";
        } else {
            // OTHER STATES: Rigorous Constraint-Based Calculation
            if (empty($db_system_prompt)) {
                $db_system_prompt = "You are a Workers' Compensation Expert for {$state}. Please estimate the value based on general guidelines.";
            }

            $system_prompt = "You are a Case Value Estimator for {$state}.

KNOWLEDGE BASE (STATUTES & RULES):
{$db_system_prompt}

INJURY DATA:
{injury_data}

TASK:
You MUST perform a statutory PPD calculation under {$state} law.

MANDATORY CALCULATION ORDER (DO NOT SKIP):
1. Determine the correct weekly PPD rate using 2/3 × AWW and the injury-year cap for {$state}.
2. If there is a scheduled AND an unscheduled injury:
   - Convert ALL impairments to \"Body as a Whole\" if required by {$state} law.
3. Combine impairments by ADDITION (do not average) unless {$state} law explicitly requires the Combined Values Chart.
4. Calculate total PPD weeks.
5. Calculate the exact PPD value (weeks × weekly rate).
6. ONLY AFTER calculation, apply a ±15% variance.

STRICT RULES:
- DO NOT estimate, average, discount, or approximate.
- DO NOT choose only one injury.
- DO NOT invent settlement heuristics.
- FOLLOW statutes exactly.
- Output MUST reflect the calculation.
- Output MUST be completely in [TARGET_LANGUAGE].

OUTPUT FORMAT:
\"[TARGET_FORMAT]\"";
        }

        // Format data for the prompt
        $injury_str = $this->format_injury_data($inputs);

        // Detailed Math Context (Only relevant for MD)
        $math_details = "";
        if ($is_maryland && isset($math_result['total_value'])) {
            $math_str = "$" . number_format($math_result['total_value'], 2);
            $math_details = "Calculated Breakdown:\n" .
                "- Year of Rates: " . $math_result['year'] . "\n" .
                "- Total Weeks Awarded: " . number_format($math_result['weeks_awarded'], 2) . "\n" .
                "- Weekly Rate: $" . number_format($math_result['weekly_rate'], 2) . "\n" .
                "- Total Value: " . $math_str;

            if ($range_str) {
                $math_details .= "\n- Statutory Range: " . $range_str;
            }
        } else {
            $math_details = "No statutory calculation available. Use Knowledge Base.";
        }

        // Get dynamic formatting string based on language
        $lang_format = WCC_i18n::get_lang() === 'es'
            ? ($is_maryland 
                ? 'Basado en los datos de la lesión, el rango de liquidación estimado es de [Rango]. [Razón en una oración en español].'
                : 'Basado en las pautas de ' . $state . ', el rango de liquidación estimado es de $[Bajo] - $[Alto]. Esto refleja los beneficios calculados por PPD con la varianza estándar de liquidación.')
            : ($is_maryland 
                ? 'Based on the injury data, the estimated settlement range is [Range]. [One sentence reason].'
                : 'Based on ' . $state . ' guidelines, the estimated settlement range is $[Low] - $[High]. This reflects calculated PPD benefits with standard settlement variance.');

        $target_lang = WCC_i18n::get_lang() === 'es' ? 'Spanish' : 'English';

        // Replace variables in system prompt
        $final_prompt = str_replace(
            array('{state}', '{injury_data}', '{math_result}', '[TARGET_LANGUAGE]', '[TARGET_FORMAT]'),
            array($state, $injury_str, $math_details, $target_lang, $lang_format),
            $system_prompt
        );

        $prompt_inst = WCC_i18n::get_lang() === 'es'
            ? "Analiza estos datos del caso directamente. No me pidas información porque la acabo de proporcionar a continuación:\n\n"
            : "Analyze this case data directly. Do not ask me for information because I just provided it below:\n\n";

        $prompt_q = WCC_i18n::get_lang() === 'es'
            ? "Basado en las reglas indicadas en el contexto del sistema, ¿cuál es el rango realista de liquidación? (Responde en español)"
            : "Based on the rules provided in the system context, what is the realistic settlement range?";

        // Force data into User Message as well to prevent "Asking Questions" loop
        $user_content = $prompt_inst .
            "Injuries Data: " . $injury_str . "\n" .
            $math_details . "\n\n" .
            $prompt_q;

        $body = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $final_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $user_content
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 1500
        );

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('WCC OpenAI Error: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('WCC OpenAI Error Code: ' . $response_code . ' Body: ' . wp_remote_retrieve_body($response));
            return false;
        }

        $body_content = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body_content['choices'][0]['message']['content'])) {
            return trim($body_content['choices'][0]['message']['content']);
        }

        return false;
    }

    private function format_injury_data($inputs)
    {
        $str = WCC_i18n::get_lang() === 'es' ? "Lesiones: " : "Injuries: ";
        if (!empty($inputs['body_parts'])) {
            foreach ($inputs['body_parts'] as $part) {
                $str .= "{$part['part']} ({$part['impairment']}%), ";
            }
        }
        $str .= (WCC_i18n::get_lang() === 'es' ? "Salario: $" : "Wage: $") . ($inputs['wage'] ?? '0') . "\n";
        $str .= (WCC_i18n::get_lang() === 'es' ? "Fecha de Lesión: " : "Date of Injury: ") . ($inputs['date_injury'] ?? 'Unknown');
        if (!empty($inputs['psychological_claim']) && $inputs['psychological_claim'] == 1) {
            $str .= (WCC_i18n::get_lang() === 'es' ? ", Incapacidad Psicológica: " : ", Psychological Impairment: ") . ($inputs['psych_impairment'] ?? '0') . "%";
        }
        if (!empty($inputs['occupational_disease']) && $inputs['occupational_disease'] == 1) {
            $str .= (WCC_i18n::get_lang() === 'es' ? ", Enfermedad Ocupacional: " : ", Occupational Disease: ") . ($inputs['occupational_impairment'] ?? '0') . "%";
        }
        return $str;
    }
}
