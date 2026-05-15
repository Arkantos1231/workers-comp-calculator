<?php
/**
 * Plugin Name: Workers' Comp Calculator
 * Plugin URI: https://pinderplotkin.com
 * Description: Una sofisticada Calculadora de Valor de Liquidación de Compensación Laboral con enrutamiento específico por estado y cálculos basados en IA.
 * Version: 1.3.19.3
 * Author: Punit Advani and Julian Obando
 * Author URI: https://pinderplotkin.com
 * License: GPL v2 or later
 * Text Domain: workers-comp-calculator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WCC_VERSION', '1.3.19.3');
define('WCC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Auto-updates from GitHub via Plugin Update Checker
if ( file_exists( WCC_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once WCC_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
    $wcc_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/Arkantos1231/workers-comp-calculator',
        __FILE__,
        'workers-comp-calculator'
    );
    $wcc_update_checker->setBranch('main');
}

/**
 * Main plugin class
 */
class Workers_Comp_Calculator
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->load_dependencies();

        // Initialize standalone components
        WCC_SEO::init();

        // Init Settings (Admin only)
        if (is_admin()) {
            WCC_Settings::init();
        }

        // Register hooks
        add_action('init', array($this, 'register_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // Activation / deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    private function load_dependencies()
    {
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-i18n.php';
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-routing.php';
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-form-handler.php';
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-maryland-logic.php';
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-settings.php';
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-ai-engine.php';
        require_once WCC_PLUGIN_DIR . 'includes/class-wcc-seo.php';

    }

    public function register_shortcode()
    {
        add_shortcode('workers_comp_calculator', array($this, 'render_calculator'));
    }

    public function render_calculator($atts)
    {
        // Detect state from URL query var or page meta
        $state = WCC_Routing::get_state_from_url();

        if (!$state) {
            $state = WCC_Routing::get_state_from_page();
        }

        ob_start();
        include WCC_PLUGIN_DIR . 'templates/calculator-form.php';
        return ob_get_clean();
    }

    public function enqueue_assets()
    {
        global $post;

        if (!is_a($post, 'WP_Post')) {
            return;
        }

        if (!has_shortcode($post->post_content, 'workers_comp_calculator')) {
            return;
        }

        wp_enqueue_style(
            'wcc-calculator-css',
            WCC_PLUGIN_URL . 'assets/css/calculator.css',
            array(),
            WCC_VERSION
        );

        wp_enqueue_style(
            'jquery-ui-css',
            'https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css',
            array(),
            '1.13.2'
        );

        wp_enqueue_script(
            'wcc-calculator-js',
            WCC_PLUGIN_URL . 'assets/js/calculator.js',
            array('jquery', 'jquery-ui-datepicker'),
            WCC_VERSION,
            true
        );

        wp_localize_script('wcc-calculator-js', 'wccAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wcc_calculator_nonce')
        ));

        // Pass translations to frontend JS
        wp_localize_script('wcc-calculator-js', 'wccI18n', array(
            'lang' => WCC_i18n::get_lang(),
            'strings' => array(
                'req_state' => WCC_i18n::get('js.req_state'),
                'req_date' => WCC_i18n::get('js.req_date'),
                'req_wage' => WCC_i18n::get('js.req_wage'),
                'req_fname' => WCC_i18n::get('js.req_fname'),
                'req_lname' => WCC_i18n::get('js.req_lname'),
                'req_email' => WCC_i18n::get('js.req_email'),
                'req_phone' => WCC_i18n::get('js.req_phone'),
                'req_consent' => WCC_i18n::get('js.req_consent'),
                'req_field' => WCC_i18n::get('js.req_field'),
                'req_impairment' => WCC_i18n::get('js.req_impairment'),
                'req_date_format' => WCC_i18n::get('js.req_date_format'),
                'req_date_future' => WCC_i18n::get('js.req_date_future'),
                'add_injury' => WCC_i18n::get('js.add_injury'),
                'remove_part_aria' => WCC_i18n::get('js.remove_part_aria'),
                'body_part_label' => WCC_i18n::get('js.body_part_label'),
                'search_body_part_placeholder' => WCC_i18n::get('js.search_body_part_placeholder'),
                'impairment_label' => WCC_i18n::get('js.impairment_label'),
                'select_state_placeholder' => WCC_i18n::get('js.select_state_placeholder'),
                'calc_title' => WCC_i18n::get('js.calc_title'),
                'results_title' => WCC_i18n::get('js.results_title'),
                'results_label' => WCC_i18n::get('js.results_label'),
                'calc_label' => WCC_i18n::get('js.calc_label'),
                'cta_title' => WCC_i18n::get('js.cta_title'),
                'cta_button' => WCC_i18n::get('js.cta_button'),
                'cta_subtext' => WCC_i18n::get('js.cta_subtext'),
                'cta_desc' => WCC_i18n::get('js.cta_desc'),
                'btn_call' => WCC_i18n::get('js.btn_call'),
                'btn_text' => WCC_i18n::get('js.btn_text'),
                'evaluating' => WCC_i18n::get('js.evaluating'),
                'error_prefix' => WCC_i18n::get('js.error_prefix'),
                'error_generic' => WCC_i18n::get('js.error_generic'),
                'error_network' => WCC_i18n::get('js.error_network'),
                'err_network' => WCC_i18n::get('js.err_network'),
                'parts' => WCC_i18n::get('js.parts'),
                'disclaimer_md' => get_option('wcc_disclaimer_maryland', ''),
                'disclaimer_other' => get_option('wcc_disclaimer_other', '')
            )
        ));
    }

    public function activate()
    {
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        flush_rewrite_rules();
    }
}

// Initialize plugin
Workers_Comp_Calculator::get_instance();
