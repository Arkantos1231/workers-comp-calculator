<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCC_Settings
{

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
    }

    public static function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'wcc_settings') === false && strpos($hook, 'wcc_calculators') === false) {
            return;
        }
        wp_enqueue_media();
    }

    public static function add_admin_menu()
    {
        // Top-level menu
        add_menu_page(
            WCC_i18n::get('admin.menu_calc'),
            WCC_i18n::get('admin.menu_title'),
            'manage_options',
            'wcc_settings',
            array(__CLASS__, 'render_settings_page'),
            'dashicons-calculator',
            20
        );

        // First submenu entry renames the top-level item
        add_submenu_page(
            'wcc_settings',
            WCC_i18n::get('admin.menu_settings'),
            WCC_i18n::get('admin.menu_settings'),
            'manage_options',
            'wcc_settings',
            array(__CLASS__, 'render_settings_page')
        );

        // Calculators — State Knowledge Bases
        add_submenu_page(
            'wcc_settings',
            WCC_i18n::get('admin.menu_kb'),
            WCC_i18n::get('admin.menu_kb'),
            'manage_options',
            'wcc_calculators',
            array(__CLASS__, 'render_calculators_page')
        );
    }

    public static function register_settings()
    {
        // --- Settings page options ---
        register_setting('wcc_options_group', 'wcc_calculator_language');
        register_setting('wcc_options_group', 'wcc_openai_api_key');
        register_setting('wcc_options_group', 'wcc_openai_model');
        register_setting('wcc_options_group', 'wcc_ghl_api_key');
        register_setting('wcc_options_group', 'wcc_ghl_location_id');

        // Custom Field Map
        register_setting('wcc_options_group', 'wcc_ghl_field_state');
        register_setting('wcc_options_group', 'wcc_ghl_field_date');
        register_setting('wcc_options_group', 'wcc_ghl_field_wage');
        register_setting('wcc_options_group', 'wcc_ghl_field_physical');
        register_setting('wcc_options_group', 'wcc_ghl_field_psych');
        register_setting('wcc_options_group', 'wcc_ghl_field_occ');
        register_setting('wcc_options_group', 'wcc_ghl_field_summary');

        register_setting('wcc_options_group', 'wcc_system_prompt_template');
        register_setting('wcc_options_group', 'wcc_disclaimer_maryland');
        register_setting('wcc_options_group', 'wcc_disclaimer_other');
        register_setting('wcc_options_group', 'wcc_header_logo');
        register_setting('wcc_options_group', 'wcc_header_icon_bg');

        // --- Calculators page options (State Knowledge Bases) ---
        foreach (WCC_Routing::get_states_list() as $state) {
            register_setting('wcc_calculators_group', 'wcc_kb_' . sanitize_title($state));
        }
    }

    // -------------------------------------------------------------------------
    // Settings Page
    // -------------------------------------------------------------------------

    public static function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(WCC_i18n::get('admin.title_settings')); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('wcc_options_group'); ?>
                <?php do_settings_sections('wcc_options_group'); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.lang_label')); ?></th>
                        <td>
                            <select name="wcc_calculator_language">
                                <?php
                                $current_lang = get_option('wcc_calculator_language', 'en');
                                ?>
                                <option value="en" <?php selected($current_lang, 'en'); ?>>English</option>
                                <option value="es" <?php selected($current_lang, 'es'); ?>>Español</option>
                            </select>
                            <p class="description"><?php echo esc_html(WCC_i18n::get('admin.lang_desc')); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.logo_label')); ?></th>
                        <td>
                            <?php $logo_url = get_option('wcc_header_logo', ''); ?>
                            <div id="wcc-logo-preview" style="margin-bottom:10px;">
                                <?php if ($logo_url): ?>
                                    <img src="<?php echo esc_url($logo_url); ?>"
                                        style="max-height:80px; max-width:300px; display:block; border:1px solid #ddd; padding:4px; background:#fff;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="wcc_header_logo" id="wcc_header_logo"
                                value="<?php echo esc_url($logo_url); ?>">
                            <button type="button" class="button" id="wcc-upload-logo-btn"><?php echo esc_html(WCC_i18n::get('admin.logo_btn')); ?></button>
                            <button type="button" class="button" id="wcc-remove-logo-btn"
                                <?php echo $logo_url ? '' : 'style="display:none;"'; ?>><?php echo esc_html(WCC_i18n::get('admin.logo_remove')); ?></button>
                            <p class="description"><?php echo esc_html(WCC_i18n::get('admin.logo_desc')); ?></p>
                            <script>
                            jQuery(function ($) {
                                var mediaUploader;
                                $('#wcc-upload-logo-btn').on('click', function (e) {
                                    e.preventDefault();
                                    if (mediaUploader) { mediaUploader.open(); return; }
                                    mediaUploader = wp.media({
                                        title: '<?php echo esc_js(WCC_i18n::get('admin.logo_btn')); ?>',
                                        button: { text: 'Usar este logo' },
                                        multiple: false
                                    });
                                    mediaUploader.on('select', function () {
                                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                                        $('#wcc_header_logo').val(attachment.url);
                                        $('#wcc-logo-preview').html('<img src="' + attachment.url + '" style="max-height:80px; max-width:300px; display:block; border:1px solid #ddd; padding:4px; background:#fff;">');
                                        $('#wcc-remove-logo-btn').show();
                                    });
                                    mediaUploader.open();
                                });
                                $('#wcc-remove-logo-btn').on('click', function (e) {
                                    e.preventDefault();
                                    $('#wcc_header_logo').val('');
                                    $('#wcc-logo-preview').html('');
                                    $(this).hide();
                                });
                            });
                            </script>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.bg_label')); ?></th>
                        <td>
                            <?php $icon_bg = get_option('wcc_header_icon_bg', ''); ?>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="color" id="wcc_header_icon_bg_picker"
                                    value="<?php echo esc_attr($icon_bg && strpos($icon_bg, 'rgba') === false ? $icon_bg : '#ffffff'); ?>"
                                    style="width:44px; height:34px; padding:2px; border:1px solid #ddd; border-radius:4px; cursor:pointer;">
                                <input type="text" name="wcc_header_icon_bg" id="wcc_header_icon_bg"
                                    value="<?php echo esc_attr($icon_bg); ?>"
                                    class="regular-text" placeholder="e.g. #ffffff or rgba(255,255,255,0.1) or transparent">
                            </div>
                            <p class="description">
                                <?php echo esc_html(WCC_i18n::get('admin.bg_desc')); ?>
                            </p>
                            <script>
                            jQuery(function ($) {
                                $('#wcc_header_icon_bg_picker').on('input', function () {
                                    $('#wcc_header_icon_bg').val(this.value);
                                });
                                $('#wcc_header_icon_bg').on('input', function () {
                                    var val = this.value.trim();
                                    if (/^#[0-9a-fA-F]{3,6}$/.test(val)) {
                                        $('#wcc_header_icon_bg_picker').val(val);
                                    }
                                });
                            });
                            </script>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.openai_label')); ?></th>
                        <td>
                            <input type="password" name="wcc_openai_api_key"
                                value="<?php echo esc_attr(get_option('wcc_openai_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html(WCC_i18n::get('admin.openai_desc')); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.model_label')); ?></th>
                        <td>
                            <select name="wcc_openai_model">
                                <?php
                                $current_model = get_option('wcc_openai_model', 'gpt-4o');
                                $models = array(
                                    // GPT-5
                                    'gpt-5'        => 'GPT-5',
                                    'gpt-5-mini'   => 'GPT-5 Mini',
                                    'gpt-5-nano'   => 'GPT-5 Nano',
                                    // o-series (reasoning)
                                    'o3'           => 'o3',
                                    'o3-pro'       => 'o3 Pro',
                                    'o3-mini'      => 'o3 Mini',
                                    'o4-mini'      => 'o4 Mini',
                                    'o1'           => 'o1',
                                    'o1-pro'       => 'o1 Pro',
                                    // GPT-4.1
                                    'gpt-4.1'      => 'GPT-4.1',
                                    'gpt-4.1-mini' => 'GPT-4.1 Mini',
                                    'gpt-4.1-nano' => 'GPT-4.1 Nano',
                                    // GPT-4o
                                    'gpt-4o'       => 'GPT-4o',
                                    'gpt-4o-mini'  => 'GPT-4o Mini',
                                );
                                foreach ($models as $value => $label) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($value),
                                        selected($current_model, $value, false),
                                        esc_html($label)
                                    );
                                }
                                ?>
                            </select>
                            <p class="description"><?php echo esc_html(WCC_i18n::get('admin.model_desc')); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.ghl_api_label')); ?></th>
                        <td>
                            <p class="description" style="margin-bottom:10px;"><?php echo esc_html(WCC_i18n::get('admin.ghl_api_desc')); ?></p>

                            <label style="display:block; margin-bottom:5px;"><strong><?php echo esc_html(WCC_i18n::get('admin.ghl_key')); ?></strong></label>
                            <input type="password" name="wcc_ghl_api_key"
                                value="<?php echo esc_attr(get_option('wcc_ghl_api_key')); ?>" class="regular-text"
                                placeholder="pit-..." />
                            <br><br>

                            <label style="display:block; margin-bottom:5px;"><strong><?php echo esc_html(WCC_i18n::get('admin.ghl_loc')); ?></strong></label>
                            <input type="text" name="wcc_ghl_location_id"
                                value="<?php echo esc_attr(get_option('wcc_ghl_location_id')); ?>" class="regular-text"
                                placeholder="ZZu..." />

                            <hr style="margin:20px 0; border:0; border-top:1px solid #ccc;">
                            <p class="description"><strong><?php echo esc_html(WCC_i18n::get('admin.ghl_keys_title')); ?></strong><br>
                                <?php echo esc_html(WCC_i18n::get('admin.ghl_keys_desc')); ?>
                            </p>

                            <table class="form-table" style="margin-top:0;">
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_state')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_state"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_state')); ?>"
                                            class="regular-text" placeholder="e.g. state_calculator" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_date')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_date"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_date')); ?>"
                                            class="regular-text" placeholder="e.g. date_of_injury" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_wage')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_wage"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_wage')); ?>"
                                            class="regular-text" placeholder="e.g. average_weekly_wage" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_phys')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_physical"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_physical')); ?>"
                                            class="regular-text" placeholder="e.g. physical_injuries" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_psych')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_psych"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_psych')); ?>"
                                            class="regular-text" placeholder="e.g. psych_impairment" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_occ')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_occ"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_occ')); ?>"
                                            class="regular-text" placeholder="e.g. occupational_disease" /></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html(WCC_i18n::get('admin.field_sum')); ?></td>
                                    <td><input type="text" name="wcc_ghl_field_summary"
                                            value="<?php echo esc_attr(get_option('wcc_ghl_field_summary')); ?>"
                                            class="regular-text" placeholder="e.g. injuries_summary" /></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.sys_prompt')); ?></th>
                        <td>
                            <textarea name="wcc_system_prompt_template" rows="10" cols="50"
                                class="large-text code"><?php echo esc_textarea(get_option('wcc_system_prompt_template', self::get_default_prompt())); ?></textarea>
                            <p class="description">
                                <?php echo esc_html(WCC_i18n::get('admin.sys_prompt_desc')); ?>
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.disc_md')); ?></th>
                        <td>
                            <textarea name="wcc_disclaimer_maryland" rows="3" cols="50"
                                class="large-text"><?php echo esc_textarea(get_option('wcc_disclaimer_maryland', self::get_default_disclaimer_maryland())); ?></textarea>
                            <p class="description"><?php echo esc_html(WCC_i18n::get('admin.disc_md_desc')); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html(WCC_i18n::get('admin.disc_other')); ?></th>
                        <td>
                            <textarea name="wcc_disclaimer_other" rows="3" cols="50"
                                class="large-text"><?php echo esc_textarea(get_option('wcc_disclaimer_other', self::get_default_disclaimer_other())); ?></textarea>
                            <p class="description"><?php echo esc_html(WCC_i18n::get('admin.disc_other_desc')); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Calculators Page — State Knowledge Bases
    // -------------------------------------------------------------------------

    public static function render_calculators_page()
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(WCC_i18n::get('admin.title_kb')); ?></h1>
            <p class="description" style="margin-bottom:15px;">
                <?php echo esc_html(WCC_i18n::get('admin.kb_desc')); ?>
            </p>

            <input type="text" id="wcc_kb_filter" placeholder="<?php echo esc_attr(WCC_i18n::get('admin.kb_filter')); ?>"
                style="width:100%; max-width:400px; margin-bottom:15px; padding:6px 10px;" />

            <form method="post" action="options.php">
                <?php settings_fields('wcc_calculators_group'); ?>

                <table class="form-table" id="wcc_kb_table">
                    <?php foreach (WCC_Routing::get_states_list() as $state) : ?>
                        <?php $slug = sanitize_title($state); ?>
                        <tr valign="top" class="wcc-kb-row" data-state="<?php echo esc_attr(strtolower($state)); ?>">
                            <th scope="row"><?php echo esc_html($state); ?></th>
                            <td>
                                <textarea name="wcc_kb_<?php echo esc_attr($slug); ?>" rows="5" cols="50" class="large-text code"
                                    placeholder="<?php echo esc_attr(WCC_i18n::get('admin.kb_placeholder')); ?> <?php echo esc_attr($state); ?>..."
                                ><?php echo esc_textarea(get_option('wcc_kb_' . $slug, '')); ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button(WCC_i18n::get('admin.btn_save_kb')); ?>
            </form>
        </div>

        <script>
        document.getElementById('wcc_kb_filter').addEventListener('input', function () {
            var query = this.value.toLowerCase();
            document.querySelectorAll('.wcc-kb-row').forEach(function (row) {
                var state = row.getAttribute('data-state');
                row.style.display = state.indexOf(query) !== -1 ? '' : 'none';
            });
        });
        </script>
        <?php
    }

    private static function get_default_disclaimer_maryland()
    {
        return "This estimate is based on the Maryland Workers' Compensation Commission guidelines. The calculated value assumes a {logic_tier}. Actual settlement values may vary.";
    }

    private static function get_default_disclaimer_other()
    {
        return "This estimate is generated by AI based on {state} statutory guidelines. Actual values may vary.";
    }

    private static function get_default_prompt()
    {
        return "You are a Case Value Estimator.
Context:
statutory_value: {math_result}
injury_data: {injury_data}

Rules:
1. OUTPUT ONLY THE RANGE.
2. DO NOT output headers (###).
3. DO NOT output bullet points.
4. DO NOT show math steps.
5. DO NOT repeat the case details.

Required Output Format:
\"Based on the injury data, the estimated settlement range is [Range]. [One sentence reason].\"";
    }
}
