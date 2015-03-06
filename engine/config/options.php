<?php
/**
 * Infinity Options Configuration.
 *
 * !!! DO NOT EDIT THIS FILE !!!
 * Only edit files in a child theme.
 *
 * @package Infinity
 * @subpackage config
 */

// See options.examples.php for more advanced configuration examples

//ice_register_option(
//	array(
//		'name' => 'example',
//		'type' => 'text',
//		'section' => 'general',
//		'title' => 'Short description',
//		'description' => 'Long Description',
//		'default_value' => 'Hello World',
//	)
//);

//
// Thumbs group, for post-thumbnails support
//
ice_register_group( 'thumbs' );

	ice_register_option(
		array(
			// TODO import old name format as commented out for compat
			// 'name' => 'cbox-thumb-height',
			'name' => 'image-height',
			'group' => 'thumbs',
			'section' => 'global',
			'title' => 'Post Thumbnail Height',
			'description' => 'The height in pixels of the featured images.',
			'type' => 'ui/slider',
			'min' => 100,
			'max' => 1500,
			'step' => 10,
			'label' => 'Height in pixels:',
			'default_value' => 240
		)
	);

	ice_register_option(
		array(
			// TODO import old name format as commented out for compat
			// 'name' => 'cbox-thumb-width',
			'name' => 'image-width',
			'group' => 'thumbs',
			'section' => 'global',
			'title' => 'Post Thumbnail Width',
			'description' => 'The width in pixels of the featured images.',
			'type' => 'ui/slider',
			'min' => 100,
			'max' => 1500,
			'step' => 10,
			'label' => 'Width in pixels:',
			'default_value' => 865
		)
	);
