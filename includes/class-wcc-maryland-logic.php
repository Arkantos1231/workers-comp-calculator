<?php
/**
 * Maryland Workers' Compensation Logic
 * Handles calculations based on MD WCC Knowledgebase
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WCC_Maryland_Logic
{

    /**
     * Historical SAWW Data (2015-2026)
     * Source: MD WCC Knowledgebase
     */
    private static $saww_table = array(
        2015 => 1005,
        2016 => 1027,
        2017 => 1052,
        2018 => 1094,
        2019 => 1116,
        2020 => 1080,
        2021 => 1050,
        2022 => 1338,
        2023 => 1402,
        2024 => 1456,
        2025 => 1493,
        2026 => 1537
    );

    /**
     * Scheduled Body Parts & Max Weeks
     * Source: MD WCC Knowledgebase
     */
    private static $body_parts = array(
        'arm' => 300,
        'leg' => 300,
        'hand' => 250,
        'foot' => 250,
        'eye' => 250,
        'hearing_both' => 250,
        'hearing_one' => 125,
        'thumb' => 100,
        'index_finger' => 40,
        'middle_finger' => 35,
        'ring_finger' => 30,
        'little_finger' => 25,
        'great_toe' => 40,
        'other_toes' => 10,
        'disfigurement' => 156,
        'other' => 500, // Unscheduled (back, neck, etc.)
        'back' => 500,
        'neck' => 500,
        'shoulder' => 500, // Often treated as "Other cases" / unscheduled
        'psychological' => 500
    );

    /**
     * Calculate Benefits
     *
     * @param string $date_injury YYYY-MM-DD
     * @param float $aww Average Weekly Wage
     * @param string $part_slug Body part identifier
     * @param float $impairment Percent impairment (0-100)
     * @return array Calculation results
     */
    /**
     * Calculate Benefits (Multi-Injury Stacking)
     *
     * @param string $date_injury YYYY-MM-DD
     * @param float $aww Average Weekly Wage
     * @param array $injuries Array of ['part' => string, 'impairment' => float]
     * @return array Calculation results
     */
    public static function calculate($date_injury, $aww, $injuries)
    {
        // 1. Determine Year
        $year = date('Y', strtotime($date_injury));
        if ($year < 2015)
            $year = 2015;
        if ($year > 2026)
            $year = 2026;

        // 2. Get SAWW and Rates
        $saww = isset(self::$saww_table[$year]) ? self::$saww_table[$year] : end(self::$saww_table);
        $max_tier1 = ceil($saww * 0.167);
        $max_tier2 = round($saww * 0.333);
        $max_serious = round($saww * 0.75);

        // 3. Process All Injuries & Stack Weeks
        $total_weeks_raw = 0;
        $breakdown = array();

        foreach ($injuries as $injury) {
            $part_slug = $injury['part'];
            $impairment = floatval($injury['impairment']);

            // Normalize Part Name
            $part = strtolower(str_replace(' ', '_', $part_slug));
            if (strpos($part, 'arm') !== false)
                $part = 'arm';
            elseif (strpos($part, 'leg') !== false)
                $part = 'leg';
            elseif (strpos($part, 'hand') !== false)
                $part = 'hand';
            elseif (strpos($part, 'foot') !== false)
                $part = 'foot';
            elseif (strpos($part, 'back') !== false)
                $part = 'back';
            elseif (strpos($part, 'neck') !== false)
                $part = 'neck';
            elseif (strpos($part, 'psych') !== false)
                $part = 'psychological';
            elseif (strpos($part, 'hearing') !== false)
                $part = 'hearing_both';
            elseif (strpos($part, 'occ') !== false)
                $part = 'other'; // Occ disease fallback

            $max_weeks = isset(self::$body_parts[$part]) ? self::$body_parts[$part] : 500;
            $awarded_weeks = $max_weeks * ($impairment / 100);

            $total_weeks_raw += $awarded_weeks;

            $breakdown[] = array(
                'part' => $part_slug,
                'impairment' => $impairment,
                'weeks' => $awarded_weeks
            );
        }

        $result = array(
            'year' => $year,
            'saww' => $saww,
            'aww' => $aww,
            'breakdown' => $breakdown,
            'raw_weeks' => $total_weeks_raw,
            'logic_tier' => '',
            'weekly_rate' => 0,
            'weeks_awarded' => 0, // Final weeks (possibly with serious adjustment)
            'total_value' => 0
        );

        // 4. Determine Tier & Calculate Value based on TOTAL Stacked Weeks

        // TIER 1: < 75 weeks
        if ($total_weeks_raw < 75) {
            $result['tier_name'] = WCC_i18n::get('maryland.tier1');
            $rate = min($aww / 3, $max_tier1);
            $result['weekly_rate'] = $rate;
            $result['weeks_awarded'] = $total_weeks_raw;
            $result['total_value'] = $rate * $total_weeks_raw;
        }
        // TIER 2: 75 - 249 weeks
        elseif ($total_weeks_raw < 250) {
            $result['tier_name'] = WCC_i18n::get('maryland.tier2');
            $rate = min(($aww * 2) / 3, $max_tier2);
            $result['weekly_rate'] = $rate;
            $result['weeks_awarded'] = $total_weeks_raw;
            $result['total_value'] = $rate * $total_weeks_raw;
        }
        // SERIOUS DISABILITY: 250+ weeks
        else {
            $result['tier_name'] = WCC_i18n::get('maryland.serious');
            $rate = min(($aww * 2) / 3, $max_serious);

            // Apply 1/3 Increase to TOTAL weeks
            $serious_weeks = $total_weeks_raw * 1.33333333;

            $result['weekly_rate'] = $rate;
            $result['weeks_awarded'] = $serious_weeks;
            $result['total_value'] = $rate * $serious_weeks;
            $result['is_serious'] = true;
        }

        return $result;
    }
}
