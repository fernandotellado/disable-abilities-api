<?php
/**
 * Settings class - handles admin settings page
 *
 * @package Disable_Abilities_API
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AyudaWP_DAA_Settings
 * Handles the settings page in wp-admin
 */
class AyudaWP_DAA_Settings {

    /**
     * Option name
     *
     * @var string
     */
    private $option_name = 'ayudawp_daa_settings';

    /**
     * Initialize settings
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add settings page to menu
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Disable Abilities API', 'disable-abilities-api' ),
            __( 'Disable Abilities API', 'disable-abilities-api' ),
            'manage_options',
            'disable-abilities-api',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'ayudawp_daa_settings_group',
            $this->option_name,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
            )
        );
    }

    /**
     * Sanitize settings before save
     *
     * @param array $input Raw input data.
     * @return array Sanitized data.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        // Disable mode
        $valid_modes = array( 'none', 'complete', 'rest_only', 'selective' );
        $sanitized['disable_mode'] = isset( $input['disable_mode'] ) && in_array( $input['disable_mode'], $valid_modes, true )
            ? $input['disable_mode']
            : 'none';

        // REST endpoints checkbox
        $sanitized['disable_rest_endpoints'] = ! empty( $input['disable_rest_endpoints'] );

        // Disabled core abilities
        $sanitized['disabled_core'] = array();
        if ( isset( $input['disabled_core'] ) && is_array( $input['disabled_core'] ) ) {
            foreach ( $input['disabled_core'] as $ability ) {
                $sanitized['disabled_core'][] = sanitize_text_field( $ability );
            }
        }

        // Disabled plugin abilities
        $sanitized['disabled_plugins'] = array();
        if ( isset( $input['disabled_plugins'] ) && is_array( $input['disabled_plugins'] ) ) {
            foreach ( $input['disabled_plugins'] as $ability ) {
                $sanitized['disabled_plugins'][] = sanitize_text_field( $ability );
            }
        }

        return $sanitized;
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_disable-abilities-api' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'ayudawp-daa-admin',
            AYUDAWP_DAA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            AYUDAWP_DAA_VERSION
        );

        wp_enqueue_script(
            'ayudawp-daa-admin',
            AYUDAWP_DAA_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            AYUDAWP_DAA_VERSION,
            true
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = get_option( $this->option_name, array() );
        $grouped_abilities = AyudaWP_DAA_Disabler::get_all_abilities_grouped();

        ?>
        <div class="wrap ayudawp-daa-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <!-- Promotional notice -->
            <div class="ayudawp-daa-promo">
                <div class="ayudawp-daa-promo-content">
                    <h3><?php esc_html_e( 'WordPress Maintenance Services', 'disable-abilities-api' ); ?></h3>
                    <p><?php esc_html_e( 'Keep your WordPress site secure, fast, and always up to date with our professional maintenance services.', 'disable-abilities-api' ); ?></p>
                    <ul>
                        <li><?php esc_html_e( 'Security monitoring and malware protection', 'disable-abilities-api' ); ?></li>
                        <li><?php esc_html_e( 'Regular backups and updates', 'disable-abilities-api' ); ?></li>
                        <li><?php esc_html_e( 'Performance optimization', 'disable-abilities-api' ); ?></li>
                        <li><?php esc_html_e( 'Priority technical support', 'disable-abilities-api' ); ?></li>
                    </ul>
                    <a href="https://mantenimiento.ayudawp.com" class="button button-primary" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e( 'Learn more', 'disable-abilities-api' ); ?>
                    </a>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'ayudawp_daa_settings_group' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Disable Mode', 'disable-abilities-api' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[disable_mode]" value="none" <?php checked( isset( $settings['disable_mode'] ) ? $settings['disable_mode'] : 'none', 'none' ); ?>>
                                    <?php esc_html_e( 'None - Abilities API works normally', 'disable-abilities-api' ); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[disable_mode]" value="rest_only" <?php checked( isset( $settings['disable_mode'] ) ? $settings['disable_mode'] : '', 'rest_only' ); ?>>
                                    <?php esc_html_e( 'REST endpoints only - Abilities work in PHP but not via REST API', 'disable-abilities-api' ); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[disable_mode]" value="selective" <?php checked( isset( $settings['disable_mode'] ) ? $settings['disable_mode'] : '', 'selective' ); ?>>
                                    <?php esc_html_e( 'Selective - Choose which abilities to disable', 'disable-abilities-api' ); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[disable_mode]" value="complete" <?php checked( isset( $settings['disable_mode'] ) ? $settings['disable_mode'] : '', 'complete' ); ?>>
                                    <strong><?php esc_html_e( 'Complete - Disable Abilities API entirely', 'disable-abilities-api' ); ?></strong>
                                </label>
                            </fieldset>
                            <p class="description">
                                <?php esc_html_e( 'Select how you want to disable the Abilities API. Complete mode unregisters all abilities and removes REST endpoints.', 'disable-abilities-api' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr class="ayudawp-daa-selective-options" style="display: none;">
                        <th scope="row"><?php esc_html_e( 'Also disable REST endpoints', 'disable-abilities-api' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[disable_rest_endpoints]" value="1" <?php checked( ! empty( $settings['disable_rest_endpoints'] ) ); ?>>
                                <?php esc_html_e( 'Remove /wp-abilities/v1/ REST endpoints', 'disable-abilities-api' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'This prevents external access to abilities via REST API while keeping them available for internal PHP use.', 'disable-abilities-api' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Selective options section -->
                <div class="ayudawp-daa-selective-section" style="display: none;">
                    <h2><?php esc_html_e( 'Select Abilities to Disable', 'disable-abilities-api' ); ?></h2>

                    <?php if ( empty( $grouped_abilities ) ) : ?>
                        <p class="description">
                            <?php esc_html_e( 'No abilities are currently registered. This list will populate after abilities are registered by WordPress core and plugins.', 'disable-abilities-api' ); ?>
                        </p>
                    <?php else : ?>
                        <?php foreach ( $grouped_abilities as $namespace => $abilities ) : ?>
                            <div class="ayudawp-daa-namespace-group">
                                <h3>
                                    <?php
                                    if ( 'core' === $namespace ) {
                                        esc_html_e( 'WordPress Core', 'disable-abilities-api' );
                                    } else {
                                        echo esc_html( ucfirst( $namespace ) );
                                    }
                                    ?>
                                    <span class="count">(<?php echo count( $abilities ); ?>)</span>
                                </h3>
                                <table class="widefat striped">
                                    <thead>
                                        <tr>
                                            <th class="check-column"></th>
                                            <th><?php esc_html_e( 'Ability', 'disable-abilities-api' ); ?></th>
                                            <th><?php esc_html_e( 'Description', 'disable-abilities-api' ); ?></th>
                                            <th><?php esc_html_e( 'REST', 'disable-abilities-api' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $abilities as $name => $data ) : ?>
                                            <?php
                                            $is_core      = ( 'core' === $namespace );
                                            $field_name   = $is_core ? 'disabled_core' : 'disabled_plugins';
                                            $disabled_arr = isset( $settings[ $field_name ] ) ? $settings[ $field_name ] : array();
                                            $is_checked   = in_array( $name, $disabled_arr, true );
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                           name="<?php echo esc_attr( $this->option_name . '[' . $field_name . '][]' ); ?>"
                                                           value="<?php echo esc_attr( $name ); ?>"
                                                           <?php checked( $is_checked ); ?>>
                                                </td>
                                                <td>
                                                    <code><?php echo esc_html( $name ); ?></code>
                                                    <br>
                                                    <small><?php echo esc_html( $data['label'] ); ?></small>
                                                </td>
                                                <td><?php echo esc_html( $data['description'] ); ?></td>
                                                <td>
                                                    <?php if ( $data['show_in_rest'] ) : ?>
                                                        <span class="dashicons dashicons-yes" title="<?php esc_attr_e( 'Exposed in REST API', 'disable-abilities-api' ); ?>"></span>
                                                    <?php else : ?>
                                                        <span class="dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Not exposed in REST API', 'disable-abilities-api' ); ?>"></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php submit_button(); ?>
            </form>

            <div class="ayudawp-daa-info">
                <h2><?php esc_html_e( 'Core Abilities Reference', 'disable-abilities-api' ); ?></h2>
                <p><?php esc_html_e( 'WordPress 6.9 registers these abilities by default:', 'disable-abilities-api' ); ?></p>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Ability', 'disable-abilities-api' ); ?></th>
                            <th><?php esc_html_e( 'Permission', 'disable-abilities-api' ); ?></th>
                            <th><?php esc_html_e( 'REST', 'disable-abilities-api' ); ?></th>
                            <th><?php esc_html_e( 'Data Exposed', 'disable-abilities-api' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>core/get-site-info</code></td>
                            <td>manage_options</td>
                            <td><span class="dashicons dashicons-yes"></span></td>
                            <td><?php esc_html_e( 'Site title, tagline, URL, admin email, charset, language, WP version', 'disable-abilities-api' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>core/get-user-info</code></td>
                            <td>is_user_logged_in</td>
                            <td><span class="dashicons dashicons-no-alt"></span></td>
                            <td><?php esc_html_e( 'User ID, display name, login, roles, locale', 'disable-abilities-api' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>core/get-environment-info</code></td>
                            <td>manage_options</td>
                            <td><span class="dashicons dashicons-yes"></span></td>
                            <td><?php esc_html_e( 'Environment type, PHP version, database server info, WP version', 'disable-abilities-api' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
