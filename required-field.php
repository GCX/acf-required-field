<?php
/*
* Plugin Name: Advanced Custom Fields - Required Field add-on
* Plugin URI:  https://github.com/GCX/acf-required-field
* Description: This plugin is an add-on for Advanced Custom Fields. It allows you to mark a field as required and if any required fields are empty, the post will not be published, but saved as draft. This plugin requires the Advanced Custom Fields plugin to installed and activated.
* Author:      Brian Zoetewey
* Author URI:  https://github.com/GCX
* Version:     1.0
* Text Domain: acf-required-field
* Domain Path: /languages/
* License:     Modified BSD
*/
?>
<?php
/*
 * Copyright (c) 2012, CAMPUS CRUSADE FOR CHRIST
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     Redistributions of source code must retain the above copyright notice, this
 *         list of conditions and the following disclaimer.
 *     Redistributions in binary form must reproduce the above copyright notice,
 *         this list of conditions and the following disclaimer in the documentation
 *         and/or other materials provided with the distribution.
 *     Neither the name of CAMPUS CRUSADE FOR CHRIST nor the names of its
 *         contributors may be used to endorse or promote products derived from this
 *         software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */
?>
<?php

if( !class_exists( 'ACF_Required_Field' ) && class_exists( 'acf_Field' ) ) :

/**
 * Advanced Custom Fields - Required Field add-on
 * 
 * @author Brian Zoetewey <brian.zoetewey@ccci.org>
 * @version 1.0
 */
class ACF_Required_Field extends acf_Field {
	/**
	 * Base directory
	 * @var string
	 */
	private $base_dir;
	
	/**
	 * Relative Uri from the WordPress ABSPATH constant
	 * @var string
	 */
	private $base_uri_rel;
	
	/**
	 * Absolute Uri
	 * 
	 * This is used to create urls to CSS and JavaScript files.
	 * @var string
	 */
	private $base_uri_abs;

	/**
	* WordPress Localization Text Domain
	*
	* The textdomain for the field is controlled by the helper class.
	* @var string
	*/
	private $l10n_domain;
	
	/**
	 * Class Constructor - Instantiates a new Field
	 * @param Acf $parent Parent Acf class
	 */
	public function __construct( $parent ) {
		parent::__construct( $parent );
		
		//Get the textdomain from the Helper class
		$this->l10n_domain = ACF_Required_Field_Helper::L10N_DOMAIN;
		
		$this->base_dir = rtrim( dirname( realpath( __FILE__ ) ), '/' );
		
		//Build the base relative uri by searching backwards until we encounter the wordpress ABSPATH
		$root = array_pop( explode( '/', rtrim( ABSPATH, '/' ) ) );
		$path_parts = explode( '/', $this->base_dir );
		$parts = array();
		while( $part = array_pop( $path_parts ) ) {
			if( $part == $root )
				break;
			array_unshift( $parts, $part );
		}
		$this->base_uri_rel = '/' . implode( '/', $parts );
		$this->base_uri_abs = get_site_url( null, $this->base_uri_rel );
		
		$this->name  = 'required-field';
		$this->title = __( 'Required Field', $this->l10n_domain );
		
		add_action( 'admin_print_scripts', array( &$this, 'admin_print_scripts' ), 12, 0 );
		add_action( 'admin_print_styles', array( &$this, 'admin_print_styles' ), 12, 0 );
	}
	
	/**
	 * Registers and enqueues necessary CSS
	 * 
	 * This method is called by ACF when rendering a post add or edit screen.
	 * We also call this method on the Acf Field Options screen as well in order
	 * to style our Field options
	 * 
	 * @see acf_Field::admin_print_styles()
	 */
	public function admin_print_styles() {
		global $pagenow;
		wp_register_style( 'acf-required-field', $this->base_uri_abs . '/required-field.css' );
		
		if( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			wp_enqueue_style( 'acf-required-field' );
		}
	}
	
	/**
	 * Registers and enqueues necessary JavaScript
	 * 
	 * This method is called by ACF when rendering a post add or edit screen.
	 * We also call this method on the Acf Field Options screen as well in order
	 * to add the necessary JavaScript for the field.
	 * 
	 * @see acf_Field::admin_print_scripts()
	 */
	public function admin_print_scripts() {
		global $pagenow;
		wp_register_script( 'acf-required-field', $this->base_uri_abs . '/required-field.js', array( 'jquery-ui-sortable' ) );
		wp_localize_script( 'acf-required-field', 'acf_required_field_l10n', array(
			'asterisk' => __( '*', $this->l10n_domain ),
			'required' => __( '* required', $this->l10n_domain ),
		) );
		
		if( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			wp_enqueue_script( 'acf-required-field' );
		}
	}
	
	/**
	 * Returns the global Acf class
	 * 
	 * @return Acf|NULL
	 */
	private function get_acf() {
		global $acf;
		if( class_exists( 'Acf' ) && isset( $acf ) )
			return $acf;
		return null;
	}
	
	/**
	* Populates the fields array with defaults for this field type
	*
	* @param array $field
	* @return array
	*/
	private function set_field_defaults( &$field ) {
		$acf = $this->get_acf();
		
		$field[ 'required_type' ] = ( array_key_exists( 'required_type', $field ) && isset( $field[ 'required_type' ] ) && array_key_exists( $field[ 'required_type' ], $acf->fields ) ) ?
			$field[ 'required_type' ] : 'text';
		
		$field[ 'class' ] = 'required-field ' . $field[ 'required_type' ];
		
		return $field;
	}
	
	/**
	 * Creates the field for inside post metaboxes
	 * 
	 * @see acf_Field::create_field()
	 */
	public function create_field( $field ) {
		$this->set_field_defaults( $field );
		
		if( $acf = $this->get_acf() ) {
			if( array_key_exists( 'required_fields', $_REQUEST ) && intval( $_REQUEST[ 'required_fields' ] ) === 1 && empty( $field[ 'value' ] ) ) {
				$field[ 'class' ] .= ' required-field-error';
			}
			$acf->fields[ $field[ 'required_type' ] ]->create_field( $field );
		}
	}
	
	/**
	 * Builds the field options
	 * 
	 * @see acf_Field::create_options()
	 * @param string $key
	 * @param array $field
	 */
	public function create_options( $key, $field ) {
		$this->set_field_defaults( $field );
		$acf = $this->get_acf();
		
		$fields = array();
		foreach( $acf->fields as $name => $class ) {
			if($name == $this->name)
				continue;
			$fields[ $name ] = $class->title;
		}

		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e( 'Required Type' , $this->l10n_domain ); ?></label>
				<p class="description"><?php _e( 'The field type that should be required.', $this->l10n_domain ); ?></p>
			</td>
			<td>
				<?php 
					$this->parent->create_field( array(
						'type'    => 'select',
						'name'    => "fields[{$key}][required_type]",
						'value'   => $field[ 'required_type' ],
						'class'   => 'required-field-type',
						'choices' => $fields,
					) );
				?>
			</td>
		</tr>
		<?php
	}
	
	/**
	 * @see acf_Field::update_value()
	 */
	public function update_value( $post_id, $field, $value ) {
		$this->set_field_defaults( $field );
		if( $acf = $this->get_acf() ) {
			$acf->fields[ $field[ 'required_type' ] ]->update_value( $post_id, $field, $value );
		}
	}
	
	/**
	 * @see acf_Field::pre_save_field()
	 */
	public function pre_save_field( $field ) {
		$this->set_field_defaults( $field );
		if( $acf = $this->get_acf() ) {
			return $acf->fields[ $field[ 'required_type' ] ]->pre_save_field( $field );
		}
		return parent::pre_save_field( $field );
	}
	
	/**
	 * Returns the values of the field
	 * 
	 * @see acf_Field::get_value()
	 * @param int $post_id
	 * @param array $field
	 * @return array  
	 */
	public function get_value( $post_id, $field ) {
		$this->set_field_defaults( $field );
		if( $acf = $this->get_acf() ) {
			return $acf->fields[ $field[ 'required_type' ] ]->get_value( $post_id, $field );
		}
		return parent::get_value( $post_id, $field );
	}
	
	/**
	 * Returns the value of the field for the advanced custom fields API
	 * 
	 * @see acf_Field::get_value_for_api()
	 * @param int $post_id
	 * @param array $field
	 */
	public function get_value_for_api( $post_id, $field ) {
		$this->set_field_defaults( $field );
		if( $acf = $this->get_acf() ) {
			return $acf->fields[ $field[ 'required_type' ] ]->get_value_for_api( $post_id, $field );
		}
		return parent::get_value_for_api( $post_id, $field );
	}
}

endif; //class_exists 'ACF_Required_Field'

if( !class_exists( 'ACF_Required_Field_Helper' ) ) :

/**
 * Advanced Custom Fields - Required Field Helper
 * 
 * This class is a Helper for the ACF_Required_Field class.
 * 
 * It provides:
 * Localization support and registering the textdomain with WordPress.
 * Registering the field with Advanced Custom Fields. There is no need in your plugin or theme
 * to manually call the register_field() method, just include this file.
 * <code> include_once( rtrim( dirname( __FILE__ ), '/' ) . '/acf-required-field/required-field.php' ); </code>
 * 
 * @author Brian Zoetewey <brian.zoetewey@ccci.org>
 */
class ACF_Required_Field_Helper {
	/**
	* WordPress Localization Text Domain
	*
	* Used in wordpress localization and translation methods.
	* @var string
	*/
	const L10N_DOMAIN = 'acf-required-field';
	
	/**
	 * Singleton instance
	 * @var ACF_Required_Field_Helper
	 */
	private static $instance;
	
	/**
	 * Returns the ACF_Required_Field_Helper singleton
	 * 
	 * <code>$obj = ACF_Required_Field_Helper::singleton();</code>
	 * @return ACF_Required_Field_Helper
	 */
	public static function singleton() {
		if( !isset( self::$instance ) ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	
	/**
	 * Prevent cloning of the ACF_Required_Field_Helper object
	 * @internal
	 */
	private function __clone() {
	}
	
	/**
	 * Language directory path
	 * 
	 * Used to build the path for WordPress localization files.
	 * @var string
	 */
	private $lang_dir;
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->lang_dir = rtrim( dirname( realpath( __FILE__ ) ), '/' ) . '/languages';
		
		add_action( 'init', array( &$this, 'register_field' ),  5, 0 );
		add_action( 'init', array( &$this, 'load_textdomain' ), 2, 0 );
	}
	
	/**
	 * Registers the Field with Advanced Custom Fields
	 */
	public function register_field() {
		if( function_exists( 'register_field' ) ) {
			register_field( 'ACF_Required_Field', __FILE__ );
			
			add_action( 'wp_insert_post_data', array( &$this, 'enforce_required_fields' ), 100, 2 );
			add_action( 'admin_notices',       array( &$this, 'admin_notice' ),            10,  0 );
		}
	}
	
	/**
	* Loads the textdomain for the current locale if it exists
	*/
	public function load_textdomain() {
		$locale = get_locale();
		$mofile = $this->lang_dir . '/' . self::L10N_DOMAIN . '-' . $locale . '.mo';
		load_textdomain( self::L10N_DOMAIN, $mofile );
	}
	
	/**
	* Returns the global Acf class
	*
	* @return Acf|NULL
	*/
	private function get_acf() {
		global $acf;
		if( class_exists( 'Acf' ) && isset( $acf ) )
			return $acf;
		return null;
	}
	
	/**
	 * Determine if any required fields on the post are empty
	 * 
	 * If a required field is empty, the post_status is set to draft and the post_date
	 * is removed.
	 * 
	 * @param array $data parsed post data
	 * @param array $postarr raw post data
	 * @return array
	 */
	public function enforce_required_fields( $data, $postarr ) {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $data;
		
		if( in_array( $data[ 'post_status' ], array('auto-draft', 'draft', 'trash') ) )
			return $data;
		
		$post_id = array_key_exists( 'ID', $postarr ) ? intval( $postarr[ 'ID' ] ) : 0;
		if( $post_id === 0 )
			return $data;
		
		$acf = $this->get_acf();
		$has_empty_field = false;
		
		foreach( $this->get_required_fields( $post_id ) as $field ) {
			//Update the value for the required field. ACF does this later in the save process. We do it here so we can override as necessary. 
			$acf->update_value(
				$post_id,
				$field,
				( array_key_exists( 'fields', $_POST ) && array_key_exists( $field[ 'key' ], $_POST[ 'fields' ] ) ) ? stripslashes_deep( $_POST[ 'fields' ][ $field[ 'key' ] ] ) : ''
			 );
			
			$value = $acf->get_value( $post_id, $field );
			if( empty( $value ) )
				$has_empty_field = true;
		}
		
		if( $has_empty_field ) {
			$data[ 'post_status'   ] = 'draft';
			$data[ 'post_date'     ] = current_time('mysql');
			$data[ 'post_date_gmt' ] = '0000-00-00 00:00:00';
			
			add_action( 'redirect_post_location', array(&$this, 'update_redirect_location'), 10, 2 );
		}

		return $data;
	}

	/**
	 * Modify the post redirect URL if any required fields are empty
	 * 
	 * @param string $location url
	 * @param int $post_id
	 * @return string
	 */
	public function update_redirect_location( $location, $post_id ) {
		remove_action( 'redirect_post_location', array(&$this, 'update_redirect_location') );
		
		return add_query_arg( array(
			'message'         => false, //delete the message param
			'required_fields' => '1', //Add the required_fields param
		), $location);
	}
	
	/**
	 * Displays the Required Fields missing admin notice
	 */
	public function admin_notice() {
		if( array_key_exists( 'required_fields', $_REQUEST ) && intval( $_REQUEST[ 'required_fields' ] ) === 1 ) {
			echo sprintf(
				'<div id="notice" class="error"><p>%1$s</p></div>',
				__( 'One or more Required Fields are missing.', self::L10N_DOMAIN )
			);
		}
	}
	
	/**
	 * Build a list of the required-field types attached to a post
	 * 
	 * @param int $post_id
	 * @return array
	 */
	private function get_required_fields( $post_id ) {
		$acf = $this->get_acf();
		
		$required_fields = array();
		
		$metabox_ids = $acf->get_input_metabox_ids( array( 'post_id' => $post_id ), false );
		$acf_query = new WP_Query(array(
			'post_type'        => 'acf',
			'posts_per_page'   => 	-1,
			'sort_column'      => 'menu_order',
			'order'            => 'ASC',
			'suppress_filters' => true,
		));
		foreach( $acf_query->posts as $acf_post ) {
			if( in_array( $acf_post->ID, $metabox_ids ) ) {
				$acf_fields = $acf->get_acf_fields( $acf_post->ID );
				foreach( $acf_fields as $acf_field ) {
					if( $acf_field[ 'type' ] == 'required-field' )
						$required_fields[] = $acf_field;
				}
			}
		}
		
		return $required_fields;
	}
}
endif; //class_exists 'ACF_Required_Field_Helper'

//Instantiate the Addon Helper class
ACF_Required_Field_Helper::singleton();