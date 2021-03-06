<?php
/**
 * Infinity Global Configuration.
 *
 * !!! DO NOT EDIT THIS FILE !!!
 * Only edit files in a child theme.
 *
 * @package Infinity
 * @subpackage config
 */

// The parent theme.
// Your child theme should set this to either 'infinity-engine', or an ancestor of infinity.
ice_update_setting( INFINITY_NAME, 'parent_theme', null );

// ---------------------------------------------------------------------
// Asset files cascading preferences
// These settings are related to the various asset cascading functions
// and DO NOT have anything to do with automatic enqueueing.
// ---------------------------------------------------------------------

// The root image directory, relative to your theme root.
// If your image directory is: wp-content/themes/my-theme/content/img
// Then your image root would be: 'content/img'
ice_update_setting( INFINITY_NAME, 'image_root',  'assets/images' );

// The root style directory, relative to your theme root.
// If your style directory is: wp-content/themes/my-theme/content/css
// Then your style root would be: 'content/css'
ice_update_setting( INFINITY_NAME, 'style_root',  'assets/css' );

// The root script directory, relative to your theme root.
// If your script directory is: wp-content/themes/my-theme/content/js
// Then your script root would be: script_root = 'content/js'
ice_update_setting( INFINITY_NAME, 'script_root',  'assets/js' );

// Load component config files
require_once INFINITY_CONFIG_PATH . '/features.php';
require_once INFINITY_CONFIG_PATH . '/options.php';
require_once INFINITY_CONFIG_PATH . '/screens.php';
require_once INFINITY_CONFIG_PATH . '/sections.php';
require_once INFINITY_CONFIG_PATH . '/widgets.php';

// Tell everyone that all configs have been loaded
do_action( 'infinity_config_loaded' );