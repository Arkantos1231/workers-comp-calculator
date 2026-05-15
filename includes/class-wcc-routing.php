<?php
/**
 * URL Routing Class
 * Handles state-specific URL routing for calculator pages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WCC_Routing {
    
    /**
     * Initialize routing
     */
    public static function init() {
        // Add rewrite rules
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        
        // Add query vars
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
        
        // Template redirect
        add_action('template_redirect', array(__CLASS__, 'template_redirect'));
    }
    
    /**
     * Add custom rewrite rules
     * Note: These are backup rules. The main routing is handled by the custom post type.
     */
    public static function add_rewrite_rules() {
        // These rules are kept for backward compatibility
        // The main routing is now handled by the custom post type rewrite rules
    }
    
    /**
     * Add custom query variables
     */
    public static function add_query_vars($vars) {
        $vars[] = 'wcc_state';
        $vars[] = 'wcc_calculator';
        return $vars;
    }
    
    /**
     * Handle template redirect
     */
    public static function template_redirect() {
        $state = get_query_var('wcc_state');
        $is_calculator = get_query_var('wcc_calculator');
        
        if ($is_calculator && $state) {
            // This is a calculator page - we'll render it via shortcode
            // The shortcode will detect the state from URL
            // Note: With pages created, this may not be needed, but kept for rewrite rule compatibility
        }
    }
    
    /**
     * Get state from current page (if it's a state calculator page)
     */
    public static function get_state_from_page() {
        global $post;
        
        if (!$post || $post->post_type !== 'page') {
            return '';
        }
        
        // Check if this is a state calculator page
        $state_slug = get_post_meta($post->ID, '_wcc_state_slug', true);
        if ($state_slug) {
            $state_name = get_post_meta($post->ID, '_wcc_state', true);
            return $state_name;
        }
        
        // Try to extract from page slug
        $page_slug = $post->post_name;
        if (strpos($page_slug, '_workers_comp_calculator') !== false) {
            $state_slug = str_replace('_workers_comp_calculator', '', $page_slug);
            $state_name = str_replace('_', ' ', $state_slug);
            $state_name = ucwords($state_name);
            return $state_name;
        }
        
        return '';
    }
    
    /**
     * Get state from URL
     * Converts URL slug to proper state name
     */
    public static function get_state_from_url() {
        $state_slug = get_query_var('wcc_state');
        
        if (!$state_slug) {
            return '';
        }
        
        // Convert slug to proper state name
        // Example: "maryland" -> "Maryland", "pennsylvania" -> "Pennsylvania"
        $state_name = str_replace('_', ' ', $state_slug);
        $state_name = ucwords($state_name);
        
        return $state_name;
    }
    
    /**
     * Get state slug from state name
     */
    public static function get_state_slug($state_name) {
        $slug = strtolower($state_name);
        $slug = str_replace(' ', '_', $slug);
        return $slug;
    }
    
    /**
     * Get list of all US states
     */
    public static function get_states_list() {
        return array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
            'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
            'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
            'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
            'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
            'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
            'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
            'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
            'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        );
    }
}

// Initialize routing
WCC_Routing::init();

