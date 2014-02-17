<?php
/**
 * ICE API: options option class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package ICE-components
 * @subpackage options
 * @since 1.0
 */

ICE_Loader::load( 'base/component', 'schemes' );

/**
 * Make an option easy
 *
 * @package ICE-components
 * @subpackage options
 */
abstract class ICE_Option extends ICE_Component
{
	/**
	 * If true, a POST value will override the real option value
	 *
	 * @var boolean
	 */
	private $__post_override__ = false;

	/**
	 * Local style property object
	 *
	 * @var ICE_Style_Property
	 */
	private $__style_property__ = false;

	/**
	 * Default value of the option
	 *
	 * @var mixed
	 */
	protected $default_value;

	/**
	 * An optional deprecated component name to check in situations
	 * where the name has changed after it has been used to store data.
	 *
	 * @var string
	 */
	private $name_deprecated;
	
	/**
	 * The feature for which this option was created (slug)
	 *
	 * @var string
	 */
	protected $feature;

	/**
	 * The feature option name (not prefixed)
	 *
	 * @var string
	 */
	protected $feature_option;

	/**
	 * The CSS class to apply to the option's input field
	 *
	 * @var string
	 */
	protected $field_class;

	/**
	 * The CSS id to apply to the option's input field
	 *
	 * @var string
	 */
	protected $field_id;

	/**
	 * An array of field options
	 *
	 * @var array
	 */
	protected $field_options;

	/**
	 * The section to which this options is assigned (slug)
	 *
	 * @var string
	 */
	protected $section;

	/**
	 * Used by options whose value is the value of an element style property
	 *
	 * @var string
	 */
	protected $style_property;

	/**
	 * Used by options which target css selectors
	 *
	 * @var string
	 */
	protected $style_selector;

	/**
	 * Used by options whose value is a unit of measure for an element style property
	 *
	 * @var string
	 */
	protected $style_unit;

	/**
	 */
	protected function get_property( $name )
	{
		switch ( $name ) {
			case 'default_value':
			case 'feature':
			case 'feature_option':
			case 'field_class':
			case 'field_id':
			case 'field_options':
			case 'name_deprecated':
			case 'section':
			case 'style_property':
			case 'style_selector':
			case 'style_unit':
				return $this->$name;
			default:
				return parent::get_property( $name );
		}
	}
	
	/**
	 */
	protected function init()
	{
		parent::init();

		// user must be allowed to manage options
		$this->add_capabilities( 'manage_options' );
	}

	/**
	 */
	public function init_styles()
	{
		parent::init_styles();
		$this->refresh_style_property();
		$this->generate_style_property();
	}

	/**
	 */
	public function configure()
	{
		// RUN PARENT FIRST!
		parent::configure();

		// empty section?
		if ( empty( $this->section ) ) {
			// yes, use default
			$this->section = 'default';
		}

		// setup style property object
		$this->refresh_style_property();

		// field options
		
		// skip loading field options outside of dashboard
		if ( !is_admin() ) {
			return;
		}
		
		// init temp field options array
		$field_options = array();
		
		// @todo this grew too big, move to private method
		if ( isset( $this->field_options ) ) {

			// is configured field options already an array?
			if ( is_array( $this->field_options ) ) {
				
				// yep, done!
				return;

			// is it a string?
			} elseif ( is_string( $this->field_options ) ) {

				// possibly a function
				$callback = $this->field_options;

				// check if the function exists
				if ( function_exists( $callback ) ) {
					// call it
					$field_options = $callback();
					// make sure we got an array
					if ( false === is_array( $field_options ) ) {
						throw new Exception( sprintf( 'The field options callback function "%s" did not return an array', $callback ) );
					}
				} else {
					throw new Exception( sprintf( 'The field options callback function "%s" does not exist', $callback ) );
				}

			} else {
				throw new Exception( sprintf( 'The field options for the "%s" option is not configured correctly', $this->get_property( 'name' ) ) );
			}
		
		} elseif ( $this instanceof ICE_Option_Auto_Field ) {

			// auto field can't overwrite existing options
			if ( null === $this->field_options ) {
				// call template method to load options
				$field_options = $this->load_field_options();
			}

		} elseif ( $this->__style_property__ ) {

			// check type
			if (
				true === $this instanceof ICE_Ext_Option_Select ||
				true === $this instanceof ICE_Ext_Option_Radio
			) {
				$field_options = $this->__style_property__->get_list_values();
			}

		}

		// make sure we ended up with some options
		if ( is_array( $field_options ) && count( $field_options ) >= 1 ) {
			// finally set them for the option
			$this->field_options = $field_options;
		}
	}

	/**
	 * Render this option AND its required siblings
	 *
	 * @param boolean $output Whether to output or return result
	 * @return string|void
	 */
	public function render( $output = true )
	{
		// render myself first
		$html = parent::render( $output );

		// render options that require this one
		foreach ( $this->policy()->registry()->get_siblings($this) as $sibling_option ) {
			$html .= $sibling_option->render( $output );
		}

		// return result
		return ( $output ) ? true : $html;
	}

	/**
	 * Toggle post override ON
	 *
	 * If enabled, post override will force the option to return it's value as set in POST
	 *
	 * @see disable_post_override
	 */
	public function enable_post_override()
	{
		$this->__post_override__ = true;
	}

	/**
	 * Toggle post override OFF
	 *
	 * @see enable_post_override
	 */
	public function disable_post_override()
	{
		$this->__post_override__ = false;
	}

	/**
	 * Get (read) the value of this option
	 *
	 * @see enable_post_override
	 * @return mixed
	 */
	public function get()
	{
		if ( $this->__post_override__ === true && isset( $_POST[$this->property( 'name' )] ) ) {
			return $_POST[$this->property( 'name' )];
		} else {
			return $this->get_option();
		}
	}

	/**
	 * Get (read) the value of this option from the database
	 *
	 * @return mixed
	 */
	protected function get_option()
	{
		// get option value from database
		$result = get_option( $this->get_api_name(), $this->default_value );

		// is the result empty?
		if (
			null === $result ||
			is_string( $result ) && '' === $result ||
			is_bool( $result ) && false === $result ||
			is_array( $result ) && 0 === count( $result )
		) {
			// no result, maybe check deprecated name
			$name_deprecated = $this->property( 'name_deprecated' );

			// if a deprecated name is set, try to get its value
			if ( $name_deprecated ) {
				// get the atypical name
				$aname = $this->format_aname( $name_deprecated );
				// get the hash name
				$hname = $this->format_hname( $aname );
				// try to get value of deprectated option name from the database
				$result = get_option( $this->get_api_name( $hname ), $this->default_value );
			}
		}

		return $result;
	}

	/**
	 * Returns true if a row for the option exists in the database
	 *
	 * @param boolean $ignore_default Set to true to ignore any default value that might be set
	 * @return boolean
	 */
	public function is_set( $ignore_default = false )
	{
		global $wpdb;

		// any default value?
		if ( false === $ignore_default && null !== $this->default_value ) {
			// a default value is set, no need to look in the database
			return true;
		}

		// check if the option exists in the database
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
				$this->get_api_name()
			)
		);

		// return result of count as boolean
		return (boolean) $count;
	}

	/**
	 * Update the value of this option
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function update( $value )
	{
		if ( $this->check_caps() ) {
			// force numeric values to floats since it could be an int or a float
			if ( is_numeric( $value ) ) {
				$value = floatval( $value );
			}
			// is the value null, an empty string, or equal to the default value?
			if ( $value === null || $value === '' || $value === $this->default_value ) {
				// its pointless to store this option
				// try to delete it in case it already exists
				return $this->delete();
			} else {
				// create or update it
				if ( $this->update_option( $value ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Update the real option in the database
	 *
	 * @param mixed $value New option value
	 * @return boolean
	 */
	protected function update_option( $value )
	{
		return update_option( $this->get_api_name(), $value );
	}

	/**
	 * Delete this option completely from the database
	 *
	 * @return boolean
	 */
	public function delete()
	{
		if ( $this->check_caps() ) {
			if ( $this->delete_option() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete the option from the database
	 *
	 * @return boolean
	 */
	protected function delete_option()
	{
		return delete_option( $this->get_api_name() );
	}

	/**
	 * Get the full name for API option
	 *
	 * @return string
	 */
	private function get_api_name( $hname = null )
	{
		// handle empty hname
		if ( null === $hname ) {
			// use current property
			$hname = $this->get_property( 'hname' );
		}

		// return formatted api name
		return implode(
			self::API_DELIM,
			array(
				self::API_PREFIX,
				$hname,
				ICE_ACTIVE_THEME
			)
		);
	}

	/**
	 * @todo need to get rid of this mess once fields component is working
	 */
	private function refresh_style_property()
	{
		// set to null in case it should not exist anymore
		$this->__style_property__ = null;

		// must have selector and property
		if ( $this->style_selector && $this->style_property ) {
			// setup property object
			$this->__style_property__ =
				ICE_Style_Property_Factory::instance()->create( $this->style_property );
		}
	}

	/**
	 */
	private function generate_style_property()
	{
		if ( $this->__style_property__ ) {

			// determine value to set
			if ( $this instanceof ICE_Option_Attachment_Image ) {
				$value = $this->get_image_url( 'full' );
			} elseif ( $this instanceof ICE_Option_Static_Image ) {
				$value = $this->get_image_url();
			} else {
				$value = $this->get();
			}

			// try to set the value
			if ( null !== $value && $this->__style_property__->set_value( $value, $this->style_unit ) ) {

				// get the style value
				$style_value = $this->__style_property__->get_value();

				// add value to component styles if set
				if ( $style_value->has_value() ) {
					// new rule
					$rule = $this->style()->rule( 'cfg', $this->style_selector );
					// add declaration from style property
					$rule->add_declaration(
						$this->__style_property__->get_name(),
						$this->__style_property__->get_value()->format()
					);
				}
			}

		}
	}
}

/**
 * Interface to implement if the option defines its own field_options internally
 *
 * @package ICE-components
 * @subpackage options
 */
interface ICE_Option_Auto_Field
{
	/**
	 * Generate custom field options
	 *
	 * @return array of field options in [value] => [description] format
	 */
	public function load_field_options();
}

/**
 * Interface to implement if the option is referencing a static image
 *
 * @package ICE-components
 * @subpackage options
 */
interface ICE_Option_Static_Image
{
	/**
	 * Return the URL of a static image for this option
	 *
	 * @return string|false absolute URL to image file
	 */
	public function get_image_url();
}

/**
 * Interface to implement if the option is storing an image attachment id
 *
 * @package ICE-components
 * @subpackage options
 */
interface ICE_Option_Attachment_Image
{
	/**
	 * Get the attachment image source details
	 *
	 * Returns an array with attachment details
	 *
	 * <code>
	 * Array (
	 *   [0] => url
	 *   [1] => width
	 *   [2] => height
	 * )
	 * </code>
	 *
	 * @see wp_get_attachment_image_src()
	 * @link http://codex.wordpress.org/Function_Reference/wp_get_attachment_image_src
	 * @param string $size Either a string (`thumbnail`, `medium`, `large` or `full`), or a two item array representing width and height in pixels, e.g. array(32,32). The size of media icons are never affected.
	 * @param integer $attach_id The id of the attachment, defaults to option value.
	 * @return array|false Attachment meta data
	 */
	public function get_image_src( $size = 'thumbnail', $attach_id = null );

	/**
	 * Return the URL of an image attachment for this option
	 *
	 * This method is only useful if the option is storing the id of an attachment
	 *
	 * @param string $size Either a string (`thumbnail`, `medium`, `large` or `full`), or a two item array representing width and height in pixels, e.g. array(32,32). The size of media icons are never affected.
	 * @return string|false absolute URL to image file
	 */
	public function get_image_url( $size = 'thumbnail' );
}

/**
 * An option for storing an image (via WordPress attachment API)
 *
 * @package ICE-components
 * @subpackage options
 */
abstract class ICE_Option_Image
	extends ICE_Option
		implements ICE_Option_Attachment_Image
{
	/**
	 */
	public function get_image_src( $size = 'thumbnail', $attach_id = null )
	{
		// attach id was passed?
		if ( empty( $attach_id ) ) {
			$attach_id = $this->get();
		}

		// src is null by default
		$src = null;

		// did we get an attachment id?
		if ( is_numeric( $attach_id ) ) {
			// is attach id gte one?
			if ( $attach_id >= 1 ) {
				// try to get the attachment info
				$src = wp_get_attachment_image_src( $attach_id, $size );
			} else {
				// id of zero or less is impossible to lookup,
				// return false immediately to avoid costly query.
				return false;
			}
		} else {
			// try to get default url
			$default_url = $this->get_default_image_url();
			// get anything?
			if ( $default_url ) {
				// mimic the src array
				$src = array_fill( 0, 3, null );
				// zero index is the url
				$src[0] = $default_url;
			}
		}

		// did we find one?
		if ( is_array($src) ) {
			return $src;
		} else {
			return false;
		}
	}

	/**
	 */
	public function get_image_url( $size = 'thumbnail' )
	{
		// get the value
		$value = $this->get();

		// did we get a number?
		if ( is_numeric( $value ) && $value >= 1 ) {

			// get the details
			$src = $this->get_image_src( $size, $value );

			// try to return a url
			return ( $src ) ? $src[0] : false;

		} elseif ( is_string( $value ) && strlen( $value ) >= 1 ) {

			// has default config?
			if ( isset( $this->default_value ) ) {
				// yep, locate theme
				$theme = ICE_Scheme::instance()->locate_theme( $this->default_value );
				// get a theme?
				if ( $theme ) {
					// yep, return URL of file
					return ICE_Scheme::instance()->theme_file_url( $theme, $this->default_value );
				}
			}
		}

		return null;
	}

	/**
	 * Return absolute URL of the default image (if set)
	 *
	 * @return string|false
	 */
	public function get_default_image_url()
	{
		// was a default set?
		if ( strlen( $this->default_value ) ) {
			// try to get theme
			$theme = ICE_Scheme::instance()->locate_theme( $this->default_value );
			// get a theme?
			if ( $theme ) {
				// yep, return URL of file
				return ICE_Scheme::instance()->theme_file_url( $theme, $this->default_value );
			}
		}

		// no default set
		return false;
	}
}
