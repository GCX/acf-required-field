=== Advanced Custom Fields - Required Field add-on ===
Contributors: Omicron7
Tags: acf, acf add-on, required, custom field, required field
Requires at least: 3.0
Tested up to: 3.3.2
Stable tag: 1.0.1

Deprecated. Mark fields as Required in Advanced Custom Fields. A post can not be published with empty required fields.

== Description ==

= This Add-on has been Deprecated =
The functionality provided by this add-on is now included in [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/).
This plugin will no longer be maintained and it is recommended that everyone discontinue its use.

This is an add-on for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/)
WordPress plugin and will not provide any functionality to WordPress unless Advanced Custom Fields is installed
and activated.

The required field provides the ability to create fields that must be filled out before a post can be published.
Required field is a wrapper for other field types, meaning that you can use this field with any other field in
Advanced Custom Fields to make it required. Some complex field types may not function properly if marked as
required (required field may think it has content when it really does not).

= Source Repository on GitHub =
https://github.com/GCX/acf-required-field

= Bugs or Suggestions =
https://github.com/GCX/acf-required-field/issues

== Installation ==

The Required Field plugin can be used as WordPress plugin or included in other plugins or themes.
There is no need to call the Advanced Custom Fields `register_field()` method for this field.

* WordPress plugin
	1. Download the plugin and extract it to `/wp-content/plugins/` directory.
	2. Activate the plugin through the `Plugins` menu in WordPress.
* Added to Theme or Plugin
	1. Download the plugin and extract it to your theme or plugin directory.
	2. Include the `required-field.php` file in you theme's `functions.php` or plugin file.  
	   `include_once( rtrim( dirname( __FILE__ ), '/' ) . '/acf-required-field/required-field.php' );`

== Frequently Asked Questions ==

= I've activated the plugin, but nothing happens! =

Make sure you have [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) installed and
activated. This is not a standalone plugin for WordPress, it only adds additional functionality to Advanced Custom Fields.

== Screenshots ==

1. Required field shown making a Text field required.
2. Required field in use on the Add Page admin screen.
3. New Page published with a required field missing.

== Changelog ==

= 1.0 =
* Initial Release