<?php
/**
 * Plugin Name:       Disable Abilities API
 * Plugin URI:        https://servicios.ayudawp.com
 * Description:       Allows you to disable WordPress Abilities API completely or selectively. Control which abilities are exposed to AI agents and automation tools.
 * Version:           1.0.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Author:            AyudaWP
 * Author URI:        https://ayudawp.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       disable-abilities-api
 * Domain Path:       /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'AYUDAWP_DAA_VERSION', '1.0.0' );
define( 'AYUDAWP_DAA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AYUDAWP_DAA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AYUDAWP_DAA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check WordPress version compatibility
 */
function ayudawp_daa_check_requirements() {
    if ( version_compare( get_bloginfo( 'version' ), '6.9', '<' ) ) {
        add_action( 'admin_notices', 'ayudawp_daa_version_notice' );
        return false;
    }
    return true;
}

/**
 * Display version notice
 */
function ayudawp_daa_version_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                /* translators: %s: Required WordPress version */
                esc_html__( 'Disable Abilities API requires WordPress %s or higher. The Abilities API was introduced in WordPress 6.9.', 'disable-abilities-api' ),
                '6.9'
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Initialize plugin
 */
function ayudawp_daa_init() {
    if ( ! ayudawp_daa_check_requirements() ) {
        return;
    }

    // Load includes
    require_once AYUDAWP_DAA_PLUGIN_DIR . 'includes/class-disabler.php';
    require_once AYUDAWP_DAA_PLUGIN_DIR . 'includes/class-settings.php';

    // Initialize classes
    $disabler = new AyudaWP_DAA_Disabler();
    $disabler->init();

    if ( is_admin() ) {
        $settings = new AyudaWP_DAA_Settings();
        $settings->init();
    }
}
add_action( 'plugins_loaded', 'ayudawp_daa_init' );

/**
 * Activation hook
 */
function ayudawp_daa_activate() {
    // Set default options
    $defaults = array(
        'disable_mode'           => 'none',
        'disable_rest_endpoints' => false,
        'disabled_core'          => array(),
        'disabled_plugins'       => array(),
    );

    if ( ! get_option( 'ayudawp_daa_settings' ) ) {
        add_option( 'ayudawp_daa_settings', $defaults );
    }
}
register_activation_hook( __FILE__, 'ayudawp_daa_activate' );

/**
 * Uninstall hook - clean up options
 */
function ayudawp_daa_uninstall() {
    delete_option( 'ayudawp_daa_settings' );
}
register_uninstall_hook( __FILE__, 'ayudawp_daa_uninstall' );

/**
 * Add settings link on plugins page
 *
 * @param array $links Plugin action links.
 * @return array Modified links.
 */
function ayudawp_daa_settings_link( $links ) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url( 'options-general.php?page=disable-abilities-api' ),
        esc_html__( 'Settings', 'disable-abilities-api' )
    );
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . AYUDAWP_DAA_PLUGIN_BASENAME, 'ayudawp_daa_settings_link' );
