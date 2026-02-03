<?php
/**
 * Disabler class - handles all abilities disabling logic
 *
 * @package Disable_Abilities_API
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AyudaWP_DAA_Disabler
 * Handles disabling abilities based on settings
 */
class AyudaWP_DAA_Disabler {

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings;

    /**
     * Core abilities list
     *
     * @var array
     */
    private $core_abilities = array(
        'core/get-site-info',
        'core/get-user-info',
        'core/get-environment-info',
    );

    /**
     * Initialize the disabler
     */
    public function init() {
        $this->settings = get_option( 'ayudawp_daa_settings', array() );

        // Apply disabling based on mode
        $mode = isset( $this->settings['disable_mode'] ) ? $this->settings['disable_mode'] : 'none';

        switch ( $mode ) {
            case 'complete':
                $this->disable_completely();
                break;
            case 'rest_only':
                $this->disable_rest_endpoints();
                break;
            case 'selective':
                $this->disable_selective();
                break;
        }

        // Additional REST endpoint disabling if enabled
        if ( ! empty( $this->settings['disable_rest_endpoints'] ) && 'rest_only' !== $mode && 'complete' !== $mode ) {
            $this->disable_rest_endpoints();
        }
    }

    /**
     * Disable Abilities API completely
     * Removes all abilities after they are registered
     */
    private function disable_completely() {
        // Remove REST endpoints
        $this->disable_rest_endpoints();

        // Unregister all abilities after init
        add_action( 'wp_abilities_api_init', array( $this, 'unregister_all_abilities' ), 9999 );
    }

    /**
     * Unregister all abilities
     * Hooked late to catch all registered abilities
     */
    public function unregister_all_abilities() {
        if ( ! function_exists( 'wp_get_abilities' ) || ! function_exists( 'wp_unregister_ability' ) ) {
            return;
        }

        $abilities = wp_get_abilities();

        if ( empty( $abilities ) ) {
            return;
        }

        foreach ( $abilities as $name => $ability ) {
            wp_unregister_ability( $name );
        }
    }

    /**
     * Disable only REST API endpoints for abilities
     */
    private function disable_rest_endpoints() {
        add_filter( 'rest_endpoints', array( $this, 'remove_abilities_endpoints' ) );
    }

    /**
     * Remove abilities REST endpoints
     *
     * @param array $endpoints Registered REST endpoints.
     * @return array Filtered endpoints.
     */
    public function remove_abilities_endpoints( $endpoints ) {
        foreach ( $endpoints as $route => $data ) {
            // Remove all endpoints under wp-abilities namespace
            if ( strpos( $route, '/wp-abilities/' ) === 0 ) {
                unset( $endpoints[ $route ] );
            }
        }
        return $endpoints;
    }

    /**
     * Disable abilities selectively based on settings
     */
    private function disable_selective() {
        add_action( 'wp_abilities_api_init', array( $this, 'unregister_selected_abilities' ), 9999 );
    }

    /**
     * Unregister selected abilities
     */
    public function unregister_selected_abilities() {
        if ( ! function_exists( 'wp_unregister_ability' ) || ! function_exists( 'wp_has_ability' ) ) {
            return;
        }

        // Disable selected core abilities
        $disabled_core = isset( $this->settings['disabled_core'] ) ? $this->settings['disabled_core'] : array();

        if ( ! empty( $disabled_core ) && is_array( $disabled_core ) ) {
            foreach ( $disabled_core as $ability_name ) {
                if ( wp_has_ability( $ability_name ) ) {
                    wp_unregister_ability( $ability_name );
                }
            }
        }

        // Disable selected plugin abilities
        $disabled_plugins = isset( $this->settings['disabled_plugins'] ) ? $this->settings['disabled_plugins'] : array();

        if ( ! empty( $disabled_plugins ) && is_array( $disabled_plugins ) ) {
            foreach ( $disabled_plugins as $ability_name ) {
                if ( wp_has_ability( $ability_name ) ) {
                    wp_unregister_ability( $ability_name );
                }
            }
        }
    }

    /**
     * Get list of core abilities
     *
     * @return array Core abilities names.
     */
    public function get_core_abilities() {
        return $this->core_abilities;
    }

    /**
     * Get all registered abilities (for settings page)
     *
     * @return array Abilities grouped by namespace.
     */
    public static function get_all_abilities_grouped() {
        if ( ! function_exists( 'wp_get_abilities' ) ) {
            return array();
        }

        $abilities = wp_get_abilities();
        $grouped   = array();

        foreach ( $abilities as $name => $ability ) {
            $parts     = explode( '/', $name, 2 );
            $namespace = isset( $parts[0] ) ? $parts[0] : 'other';

            if ( ! isset( $grouped[ $namespace ] ) ) {
                $grouped[ $namespace ] = array();
            }

            $grouped[ $namespace ][ $name ] = array(
                'label'        => method_exists( $ability, 'get_label' ) ? $ability->get_label() : $name,
                'description'  => method_exists( $ability, 'get_description' ) ? $ability->get_description() : '',
                'show_in_rest' => false,
            );

            // Check if exposed in REST
            if ( method_exists( $ability, 'get_meta' ) ) {
                $meta = $ability->get_meta();
                if ( isset( $meta['show_in_rest'] ) ) {
                    $grouped[ $namespace ][ $name ]['show_in_rest'] = (bool) $meta['show_in_rest'];
                }
            }
        }

        return $grouped;
    }
}
