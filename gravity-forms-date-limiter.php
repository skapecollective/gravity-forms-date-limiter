<?php

/**
 * Plugin Name: Gravity Forms Date Limiter
 * Description: Limit Gravity Forms date fields.
 * Version: 1.0.0
 * Plugin URI: https://skape.co/
 * Author: Skape Collective
 * Author URI: https://skape.co/
 * Text Domain: skape
 * Network: false
 * Requires at least: 5.0.0
 * Requires PHP: 8.0
 */

require_once plugin_dir_path( __FILE__ ) . 'core/Autoload.php';
$autoload = new GravityFormsDateLimiter\Autoload( plugin_dir_path( __FILE__ ) );

$autoload->loadArray( [
	'GravityFormsDateLimiter\\' => 'core'
], 'psr-4' );

// Register global constants
GravityFormsDateLimiter\Utilities\Constants::set( 'DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG );
GravityFormsDateLimiter\Utilities\Constants::set( 'VERSION', '1.0.0' );
GravityFormsDateLimiter\Utilities\Constants::set( 'PATH', plugin_dir_path( __FILE__ ) );
GravityFormsDateLimiter\Utilities\Constants::set( 'URL', plugin_dir_url( __FILE__ ) );
GravityFormsDateLimiter\Utilities\Constants::set( 'BASENAME', plugin_basename( __FILE__ ) );
GravityFormsDateLimiter\Utilities\Constants::set( 'FILENAME', pathinfo( __FILE__, PATHINFO_FILENAME ) );

// Bail early if requirements are not met.
if ( !method_exists( 'GFForms', 'include_addon_framework' ) ) {
    return;
}

// Include Gravity Forms "GFAddOn" class
GFForms::include_addon_framework();

/**
 * Get our current instance of the Gravity Forms add-on.
 *
 * @return \GravityFormsDateLimiter\AddOn
 */
function gravity_forms_date_limiter() {
    global $_gravity_forms_date_limiter;

    if ( !$_gravity_forms_date_limiter ) {
        $_gravity_forms_date_limiter = new \GravityFormsDateLimiter\AddOn;
    }

    return $_gravity_forms_date_limiter;
}

// Register the add-on.
GFAddOn::register( GravityFormsDateLimiter\AddOn::class );

// Initiate the add-on.
gravity_forms_date_limiter();
