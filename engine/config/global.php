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

// The parent theme. Either 'infinity', or an ancestor of infinity
$global['parent_theme'] = '';

// ---------------------------------------------------------------------
// Asset files cascading preferences
// These settings are related to the various asset cascading functions
// and DO NOT have anything to do with automatic enqueueing.
// ---------------------------------------------------------------------

// The root image directory, relative to your theme root
// If your image directory is: wp-content/themes/my-theme/content/img
// Then your image root would be: 'image_root' => 'content/img'
$global['image_root'] = 'assets/images';

// The root style directory, relative to your theme root
// If your style directory is: wp-content/themes/my-theme/content/css
// Then your style root would be: 'style_root' => 'content/css'
$global['style_root'] = 'assets/css';

// The root script directory, relative to your theme root
// If your script directory is: wp-content/themes/my-theme/content/js
// Then your script root would be: script_root = "content/js"
$global['style_root'] = 'assets/js';

// ---------------------------------------------------------------------
// jQuery UI preferences
// These settings affect the initialization and behavior of jQuery UI
// ---------------------------------------------------------------------

// The default is to always show two save buttons.
// Set this to false if you only want to show the "Save All" button.
$global['options_save_single'] = true;

// return config array to caller
return $global;