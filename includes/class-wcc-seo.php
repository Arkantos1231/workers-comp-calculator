<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCC_SEO
{

    public static function init()
    {
        add_action('wp_head', array(__CLASS__, 'output_schema_markup'));
        add_filter('pre_get_document_title', array(__CLASS__, 'dynamic_page_title'), 20);
        add_action('wp_head', array(__CLASS__, 'output_meta_description'), 1);
    }

    /**
     * Dynamic Page Title
     */
    public static function dynamic_page_title($title)
    {
        if (is_singular('wcc_calculator')) {
            global $post;
            $state_name = get_post_meta($post->ID, '_wcc_state_name', true);
            if ($state_name) {
                return str_replace('{state}', $state_name, WCC_i18n::get('seo.page_title'));
            }
        }
        return $title;
    }

    /**
     * Output Meta Description
     */
    public static function output_meta_description()
    {
        if (is_singular('wcc_calculator')) {
            global $post;
            $state_name = get_post_meta($post->ID, '_wcc_state_name', true);
            if ($state_name) {
                $desc = str_replace('{state}', $state_name, WCC_i18n::get('general.seo_calc_desc'));
                echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
            }
        }
    }

    /**
     * Output JSON-LD Schema
     */
    public static function output_schema_markup()
    {
        if (!is_singular('wcc_calculator')) {
            return;
        }

        global $post;
        $state_name = get_post_meta($post->ID, '_wcc_state_name', true) ?: WCC_i18n::get('general.default_state_name');

        // SoftwareApplication Schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => str_replace('{state}', $state_name, WCC_i18n::get('seo.software_app_name')),
            'applicationCategory' => 'FinanceApplication',
            'operatingSystem' => 'Web',
            'offers' => array(
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD'
            ),
            'description' => str_replace('{state}', $state_name, WCC_i18n::get('seo.software_app_desc')),
            'author' => array(
                '@type' => 'Organization',
                'name' => WCC_i18n::get('seo.author_name'),
                'url' => 'https://pinderplotkin.com'
            )
        );

        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";

        // FAQ Schema (Static for now, could be dynamic per state later)
        $faq_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array(
                array(
                    '@type' => 'Question',
                    'name' => str_replace('{state}', $state_name, WCC_i18n::get('seo.faq_q1')),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => str_replace('{state}', $state_name, WCC_i18n::get('seo.faq_a1'))
                    )
                ),
                array(
                    '@type' => 'Question',
                    'name' => WCC_i18n::get('seo.faq_q2'),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => WCC_i18n::get('seo.faq_a2')
                    )
                )
            )
        );

        echo '<script type="application/ld+json">' . json_encode($faq_schema) . '</script>' . "\n";
    }
}
