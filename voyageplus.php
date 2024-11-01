<?php
/*
 * Plugin Name: Voyage Plus
 * Plugin URI: http://www.rewindcreation.com/voyage-plus
 * Description: Voyage Plus is a companion to Voyage Theme. It provides theme-independent features such as shortcodes, ad units and simple action hooks .
 * Author: RewindCreation
 * Version: 1.0.6
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: voyageplus
 * Domain Path: /languages/
 
 * @package   VoyagePlus
 * @author    Stephen Cui
 * @copyright Copyright (c) 2013-2016, Stephen Cui
 */
class Voyage_Plus {

public static function init() {
	static $voyageplus = false;

	if ( !$voyageplus ) {
		$voyageplus = new Voyage_Plus;

		load_plugin_textdomain( 'voyageplus', false, $voyageplus->vp_plugin_url() . 'languages/');

	// Make plugin available for translation

	// Admin Option Menu
    if(is_admin()){
	   	add_action('admin_init', array($voyageplus, 'options_init'));
		add_action('admin_menu', array($voyageplus, 'options_menu'));
	}

	// Enable shortcodes in text widgets
	add_filter( 'widget_text', 'do_shortcode' );

	// Enable auto-formatting

	// Fix for large posts, http://core.trac.wordpress.org/ticket/8553
	@ini_set( 'pcre.backtrack_limit', 500000 );
	
	// Register scripts
	if ( !is_admin() ) { //Frontend

	}
	elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'voyageplus' ) {
	// backend options
		add_action('admin_enqueue_scripts', array($voyageplus,'voyageplus_admin_scripts'));		
	}
	elseif ( is_admin() ) {
	// backend edit
	}	

	}
	return $voyageplus;
}

public function options_init() {
    register_setting('voyageplus_options_group', 'voyageplus_options', array(&$this, 'voyageplus_options_validate') );
}

public function options_menu() {
	add_options_page('Voyage Plus Options', 'Voyage Plus', 'manage_options', 'voyageplus', array(&$this, 'voyageplus_options_display'));

}

public function voyageplus_admin_scripts() {
	wp_enqueue_style( 'voyage-grid', Voyage_Plus::vp_plugin_url() . 'css/grid.css', false, '1.0.0', 'all' );
	wp_enqueue_style( 'voyageplus', Voyage_Plus::vp_plugin_url() . 'css/vp-admin.css', false, '1.0.0', 'all' );
	wp_enqueue_script('voyageplus', Voyage_Plus::vp_plugin_url() . 'js/voyageplus.js', array('jquery'), '1.0.0');	
}

function voyageplus_options_display(){
?>
<div class="wrap">
	<?php screen_icon(); echo "<h2>". __('Voyage Plus Settings', 'voyageplus') . "</h2>"; ?>
	<form method="post" action="options.php">
<?php
		$option_array = Voyage_Plus::voyageplus_fields_array();
		$options = Voyage_Plus::voyageplus_get_options();
		settings_fields('voyageplus_options_group');
?>
	<div id="voyage-wrapper" class="container_12">	
		<div id="voyage-tabs">
			<a class="voyage-current"><?php _e('Welcome','voyageplus'); ?></a>
			<a><?php _e('WP Actions','voyageplus'); ?></a>
			<a><?php _e('Ad Units','voyageplus'); ?></a>
		</div>
		
		<div class="voyage-pane clearfix"><div class="grid_12">
			<h3><?php _e('Welcome to Voyage+','voyageplus'); ?></h3>
			<p>The documentation is available from <a href="<?php _e('http://www.rewindcreation.com/voyage-plus-documentation/','voyageplus'); ?>" target="_blank"><?php _e('Voyage Plus Documentation','voyageplus'); ?></a>.</p>
<?php		Voyage_Plus::voyageplus_display($option_array['shortcode_mode'], $options);
			Voyage_Plus::voyageplus_save_button(); ?>
		</div></div>
<?php
/*********************************************************
* Action Hooks
*********************************************************/
?>
		<div class="voyage-pane clearfix"><div class="grid_12">
			<p><?php _e('Header/Footer scripts are hooked into wp_head(), wp_footer() actions. They are used for add functions susch as Google Analytic, Favicon, etc.','voyageplus'); ?></p>
<?php		Voyage_Plus::voyageplus_display($option_array['headerscript'], $options);
			Voyage_Plus::voyageplus_display($option_array['footerscript'], $options);
			Voyage_Plus::voyageplus_save_button(); ?>
		</div></div>
<?php
/*********************************************************
* Ad Units
*********************************************************/
?>
		<div class="voyage-pane clearfix"><div class="grid_12">
			<p><?php _e('Enter the scripts provided by Ad Providers as per their terms and conditions. Please note that Voyage+ is GPL Licensed software which provides no warranty. You shall be provided with a copy of the license.','voyageplus'); ?></p>
<?php	Voyage_Plus::voyageplus_display($option_array['adunit1'], $options);
		Voyage_Plus::voyageplus_display($option_array['adunit2'], $options);
		Voyage_Plus::voyageplus_display($option_array['adunit3'], $options);
		Voyage_Plus::voyageplus_display($option_array['adunit4'], $options);
		Voyage_Plus::voyageplus_display($option_array['adunit5'], $options);
		Voyage_Plus::voyageplus_display($option_array['adunit6'], $options);

		Voyage_Plus::voyageplus_save_button(); ?>
		
		</div></div>

	</div>	
	</form>
</div>
<?php
}

public function voyageplus_options_validate($input) {
	foreach (Voyage_Plus::voyageplus_fields_array() as $option) {
		switch ($option['type']) {
			case 'checkbox':
				if (!isset($input[$option['name']]))
					$input[$option['name']] = null;
		    		$input[$option['name']] = ( $input[$option['name']] == 1 ? 1 : 0 );			
				break;
			case 'text':
			case 'textarea':
				$input[$option['name']] = wp_kses_stripslashes($input[$option['name']]);
				break;
			case 'number':	
				$input[$option['name']] = intval($input[$option['name']]);	
				break;				
			case 'url':	
				$input[$option['name']] = esc_url_raw($input[$option['name']]);
				break;	
		}
	}

	return $input;
}

public static function vp_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

function voyageplus_save_button() {
	printf( '<p><input type="submit" class="button-primary" value="%s" /></p>',
			__('Save Settings', 'voyageplus') ); 		
}

static function voyageplus_fields_array() {
	$options = array(
		'shortcode_mode'	=> array(
			'name'	=> 'shortcode_mode',
			'label'	=> __( 'Shortcode Mode', 'voyageplus' ),
			'type'	=> 'checkbox',
			'desc'  => __('Check to turn on compatibility mode. i.e. add <strong>vp_</strong> prefix.'),
			'helptext' => '',
			'default' => '',
		),		
//Action Hooks
		'headerscript'	=> array(
			'name'	=> 'headerscript',
			'label'	=> __( 'Header Scripts', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 20,
			'desc'  => '',
			'helptext' => __('Scripts are enclosed between &lt;script&gt; and &lt;/script&gt;', 'voyageplus'),	
			'default' => '',
		),		
		'footerscript'	=> array(
			'name'	=> 'footerscript',
			'label'	=> __( 'Footer Scripts', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 20,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
//Ad Units
		'adunit1'	=> array(
			'name'	=> 'adunit1',
			'label'	=> __( 'Ad Unit 1', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 5,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
		'adunit2'	=> array(
			'name'	=> 'adunit2',
			'label'	=> __( 'Ad Unit 2', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 5,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
		'adunit3'	=> array(
			'name'	=> 'adunit3',
			'label'	=> __( 'Ad Unit 3', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 5,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
		'adunit4'	=> array(
			'name'	=> 'adunit4',
			'label'	=> __( 'Ad Unit 4', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 5,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
		'adunit5'	=> array(
			'name'	=> 'adunit5',
			'label'	=> __( 'Ad Unit 5', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 5,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
		'adunit6'	=> array(
			'name'	=> 'adunit6',
			'label'	=> __( 'Ad Unit 6', 'voyageplus' ),
			'type'	=> 'textarea',
			'row'	=> 5,
			'desc'  => '',
			'helptext' => '',	
			'default' => '',
		),
	);
	return apply_filters( 'voyageplus_options_array', $options);
}

public static function voyageplus_get_options() {
	return wp_parse_args( get_option( 'voyageplus_options' ), Voyage_Plus::default_options());
}

static function default_options() {
	$defaults = array();
	$options = Voyage_Plus::voyageplus_fields_array();
	foreach ($options as $option) {
		$defaults[$option['name']] = $option['default'];
	}
	return apply_filters( 'voyageplus_default_options', $defaults );
}

static function voyageplus_display( $option_array, $options ) {
	echo '<div class="grid_3 alpha">';
	echo '<p><b>' . $option_array['label'] . '</b></p></div>';
	echo '<div class="grid_9"><p>';

	switch ($option_array['type']) {
		case 'radio':
			$values = $option_array['values'];
			foreach ($values as $value) {
				printf( '<input name="voyageplus_options[%s]" type="radio" value="%s" %s />',
					$option_array['name'],
				 	$value['key'],
				 	checked( $value['key'], $options[$option_array['name']], false ) );
				printf( '<label class="description">%s</label>', $value['label']);
			}
			break;
		case 'checkbox':
			printf( '<input name="voyageplus_options[%s]" type="checkbox" value="1" %s />',
					$option_array['name'],
				 	checked( '1', $options[$option_array['name']], false ) );
			if (!empty($option_array['desc']))
				printf( '<label class="description">%s</label>', $option_array['desc']);
			break;				
		case 'url':
		case 'text':
			printf( '<input id="voyageplus_options[%s]" name="voyageplus_options[%s]" type="text" value="%s" size="80" />',
					$option_array['name'],
					$option_array['name'],
				 	esc_attr($options[$option_array['name']]) );
			break;
		case 'textarea':
			printf( '<textarea name="voyageplus_options[%s]" cols="80" rows="%s">%s</textarea>',
					$option_array['name'],
					$option_array['row'], 
				 	esc_textarea($options[$option_array['name']]) );
			break;
		case 'number':
			printf( '<input name="voyageplus_options[%s]" type="text" value="%s" size="4" />',
					$option_array['name'],
				 	esc_attr($options[$option_array['name']]) );
			if (!empty($option_array['desc']))
				printf( '<label class="description">%s</label>', $option_array['desc']);
			break;
		case 'category':
			printf( '<select name="voyageplus_options[%s]" >',
				$option_array['name'] );

			$selected = '';
			$selected_category = $options[$option_array['name']];
			if 	($options[$option_array['name']] == 0)	
				$selected = 'selected="selected"';
			printf ('<option value="0" %1$s>%2$s</option>',
					$selected,
					__('All Categories','voyageplus') );

			$selected = '';
			foreach ( voyage_categories() as $option ) {
				if ( $selected_category == $option->term_id ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				} 
				printf ('<option value="%1$s" %2$s>%3$s</option>',
					$option->term_id,
					$selected,
					$option->name );
			}
			echo '</select>';
			break;
		default:
			echo 'Not Availavle Yet';			
	}
	echo '</p>';
	if (!empty($option_array['helptext']))
		printf( '<p><label class="helptext">%s</label></p>', $option_array['helptext']);
	echo '</div><div class="clear"></div>';	
}

}
add_action( 'init', array( 'Voyage_Plus', 'init' ) );

require_once 'inc/actionhooks.php';
require_once 'inc/shortcodes.php';

?>