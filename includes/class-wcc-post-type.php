<?php
/**
 * Custom Post Type Class
 * Registers the "State Calculator" custom post type
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WCC_Post_Type
{

    /**
     * Post type slug
     */
    const POST_TYPE = 'wcc_calculator';

    /**
     * Initialize custom post type
     */
    public static function init()
    {
        add_action('init', array(__CLASS__, 'register_post_type'));
        add_filter('post_type_link', array(__CLASS__, 'custom_post_type_link'), 10, 2);
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_meta_box'));
        add_action('init', array(__CLASS__, 'add_custom_rewrite_rules'), 20);
        add_filter('template_include', array(__CLASS__, 'load_single_template'));
    }

    /**
     * Load custom single template for calculator
     */
    public static function load_single_template($template)
    {
        if (is_singular(self::POST_TYPE)) {
            $plugin_template = WCC_PLUGIN_DIR . 'templates/single-wcc_calculator.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    /**
     * Add custom rewrite rules for the permalink structure
     */
    public static function add_custom_rewrite_rules()
    {
        // Add rewrite rule for our custom permalink structure
        // Pattern: tools/workers_comp_calculator/{state}-workers-comp-calculator/
        add_rewrite_rule(
            '^tools/workers_comp_calculator/([^/]+)-workers-comp-calculator/?$',
            'index.php?post_type=' . self::POST_TYPE . '&wcc_state_slug=$matches[1]',
            'top'
        );

        // Add query var for state slug
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));

        // Parse the query to find post by state
        add_action('parse_query', array(__CLASS__, 'parse_state_query'));
    }

    /**
     * Add query vars
     */
    public static function add_query_vars($vars)
    {
        $vars[] = 'wcc_state_slug';
        return $vars;
    }

    /**
     * Parse query to find post by state slug
     * This handles the custom URL structure
     */
    public static function parse_state_query($query)
    {
        if (!isset($query->query_vars['wcc_state_slug']) || empty($query->query_vars['wcc_state_slug'])) {
            return;
        }

        $state_slug = $query->query_vars['wcc_state_slug'];

        // The post slug should be: {state}-workers-comp-calculator
        // So we can directly query by post_name
        $post_slug = $state_slug . '-workers-comp-calculator';

        $args = array(
            'post_type' => self::POST_TYPE,
            'name' => $post_slug,
            'posts_per_page' => 1,
            'post_status' => 'publish'
        );

        $posts = get_posts($args);

        if (!empty($posts)) {
            // Found the post, set it in query
            $query->query_vars['p'] = $posts[0]->ID;
            $query->query_vars['name'] = $post_slug;
            $query->is_single = true;
            $query->is_singular = true;
            $query->is_page = false;
        }

        // Clear the custom query var to prevent conflicts
        unset($query->query_vars['wcc_state_slug']);
    }

    /**
     * Register custom post type
     */
    public static function register_post_type()
    {
        $labels = array(
            'name' => 'State Calculators',
            'singular_name' => 'State Calculator',
            'menu_name' => 'Workers\' Comp Calculators',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New State Calculator',
            'edit_item' => 'Edit State Calculator',
            'new_item' => 'New State Calculator',
            'view_item' => 'View State Calculator',
            'search_items' => 'Search Calculators',
            'not_found' => 'No calculators found',
            'not_found_in_trash' => 'No calculators found in trash',
            'all_items' => 'All Calculators',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-calculator',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'tools/workers_comp_calculator',
                'with_front' => false,
                'pages' => false,
                'feeds' => false,
            ),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 20,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest' => true, // Enable Gutenberg editor
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Custom post type permalink structure
     * Format: /tools/workers_comp_calculator/{state}-workers-comp-calculator/
     */
    public static function custom_post_type_link($post_link, $post)
    {
        if ($post->post_type === self::POST_TYPE) {
            // Get state name from meta field first
            $state_name = get_post_meta($post->ID, '_wcc_state_name', true);

            // If no meta, try to extract from title
            if (!$state_name) {
                $state_name = self::extract_state_from_title($post->post_title);
            }

            // Convert state name to slug (lowercase, spaces to hyphens)
            if ($state_name) {
                $state_slug = sanitize_title($state_name);
                // Build the URL: /tools/workers_comp_calculator/{state}-workers-comp-calculator/
                $post_link = home_url('tools/workers_comp_calculator/' . $state_slug . '-workers-comp-calculator/');
            } else {
                // Fallback: use post slug (WordPress generated) and append suffix
                // Get the post slug
                $post_slug = $post->post_name;
                // Remove "workers-comp-calculator" if already in slug to avoid duplication
                $post_slug = preg_replace('/-workers-comp-calculator$/', '', $post_slug);
                $post_link = home_url('tools/workers_comp_calculator/' . $post_slug . '-workers-comp-calculator/');
            }
        }

        return $post_link;
    }

    /**
     * Extract state name from post title
     */
    private static function extract_state_from_title($title)
    {
        // Get list of all states
        $states_list = WCC_Routing::get_states_list();

        // Try to find a state name in the title
        foreach ($states_list as $state) {
            // Check if state name appears in title (case insensitive)
            if (stripos($title, $state) !== false) {
                return $state;
            }
        }

        return '';
    }

    /**
     * Add meta boxes for state calculator
     */
    public static function add_meta_boxes()
    {
        add_meta_box(
            'wcc_state_info',
            'State Information',
            array(__CLASS__, 'render_state_meta_box'),
            self::POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Render state information meta box
     */
    public static function render_state_meta_box($post)
    {
        // Add nonce for security
        wp_nonce_field('wcc_save_meta_box', 'wcc_meta_box_nonce');

        // Get current values
        $state_name = get_post_meta($post->ID, '_wcc_state_name', true);
        $state_code = get_post_meta($post->ID, '_wcc_state_code', true);

        // Get states list
        $states_list = WCC_Routing::get_states_list();

        ?>
        <div style="margin-bottom: 15px;">
            <label for="wcc_state_name" style="display: block; margin-bottom: 5px; font-weight: 600;">
                State Name:
            </label>
            <select name="wcc_state_name" id="wcc_state_name" style="width: 100%;">
                <option value="">Select State</option>
                <?php foreach ($states_list as $state): ?>
                    <option value="<?php echo esc_attr($state); ?>" <?php selected($state_name, $state); ?>>
                        <?php echo esc_html($state); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description" style="margin-top: 5px; font-size: 12px; color: #666;">
                Select the state for this calculator. The URL will be:
                <code>/tools/workers_comp_calculator/{state}-workers-comp-calculator/</code>
            </p>
        </div>

        <p class="description" style="margin-top: 5px; font-size: 12px; color: #666;">
            Two-letter state code (optional, for reference).
        </p>
        </div>

        <div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
            <strong>URL Preview:</strong><br>
            <code style="font-size: 11px; color: #666;">
                                        <?php
                                        // Get state name for URL preview
                                        $preview_state = $state_name;
                                        if (!$preview_state && $post->post_status !== 'auto-draft') {
                                            $preview_state = self::extract_state_from_title($post->post_title);
                                        }

                                        if ($preview_state) {
                                            $state_slug = sanitize_title($preview_state);
                                            echo esc_html(home_url('tools/workers_comp_calculator/' . $state_slug . '-workers-comp-calculator/'));
                                        } else {
                                            echo esc_html(home_url('tools/workers_comp_calculator/[title]-workers-comp-calculator/'));
                                        }
                                        ?>
                                    </code>
            <p class="description" style="margin-top: 5px; font-size: 11px; color: #666;">
                URL format: <code>/tools/workers_comp_calculator/{state}-workers-comp-calculator/</code>
            </p>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public static function save_meta_box($post_id)
    {
        // Check if nonce is set
        if (!isset($_POST['wcc_meta_box_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['wcc_meta_box_nonce'], 'wcc_save_meta_box')) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check post type
        if (get_post_type($post_id) !== self::POST_TYPE) {
            return;
        }

        // Save state name
        if (isset($_POST['wcc_state_name'])) {
            $state_name = sanitize_text_field($_POST['wcc_state_name']);
            update_post_meta($post_id, '_wcc_state_name', $state_name);

            // Update post slug to match state slug for proper URL routing
            if ($state_name) {
                $state_slug = sanitize_title($state_name);
                $new_slug = $state_slug . '-workers-comp-calculator';

                // Only update if slug is different
                $current_post = get_post($post_id);
                if ($current_post && $current_post->post_name !== $new_slug) {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_name' => $new_slug
                    ));
                }
            }
        }

        // Save state code
        if (isset($_POST['wcc_state_code'])) {
            update_post_meta($post_id, '_wcc_state_code', strtoupper(sanitize_text_field($_POST['wcc_state_code'])));
        }

    }

    /**
     * Get state name from post
     */
    public static function get_state_from_post($post_id = null)
    {
        if (!$post_id) {
            global $post;
            if (!$post || $post->post_type !== self::POST_TYPE) {
                return '';
            }
            $post_id = $post->ID;
        }

        // First try meta field
        $state_name = get_post_meta($post_id, '_wcc_state_name', true);

        // If no meta, try to extract from title
        if (!$state_name) {
            $post_obj = get_post($post_id);
            if ($post_obj) {
                $state_name = self::extract_state_from_title($post_obj->post_title);
            }
        }

        return $state_name;
    }
}

// Initialize custom post type
WCC_Post_Type::init();

