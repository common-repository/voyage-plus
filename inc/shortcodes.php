<?php
/* Shortcodes
 * @package   VoyagePlus
 * @author    VoyageTheme
 * @copyright Copyright (c) 2013, Stephen Cui
 *
 */
class Voyage_Shortcodes {

public $col = 0;

public $allowed_grids = array( 2, 3, 4, 5, 6, 12 );

public function __construct() {
    if(!is_admin()){
		$options = Voyage_Plus::voyageplus_get_options();
		
		add_action('wp_enqueue_scripts', array($this,'voyageplus_shortcode_scripts') ); 
//		remove_filter( 'the_content', 'wpautop' );
//		add_filter( 'the_content', 'wpautop' , 99);
		add_filter( 'the_content', 'shortcode_unautop', 200 );
				
		if ($options['shortcode_mode'] == 1)
			$shortcode_prefix = 'vp_';
		else
			$shortcode_prefix = '';			
		$shortcodes = Voyage_Shortcodes::voyageplus_shortcode_array();
		foreach ($shortcodes as $shortcode) {
			add_shortcode($shortcode_prefix . $shortcode['name'],
				array($this, $shortcode['func']) );
		}
	}
}

public function voyageplus_shortcode_scripts() {
	wp_enqueue_style( 'voyageplus-shortcode', Voyage_Plus::vp_plugin_url() . 'css/vp-shortcodes.css', false, '1.0.0', 'all' );
}

function voyageplus_shortcode_array() {
	$codes = array(
		'ad'		=> array( 'name' => 'ad',
						  	  'func' => 'voyageplus_adunit' ),		
		'column'	=> array( 'name'	=> 'column',
							  'func'	=> 'voyageplus_column' ),	
		'one-third'	=> array( 'name'	=> 'one-third',
							  'func'	=> 'voyageplus_one_third' ),	
		'two-third'	=> array( 'name'	=> 'two-third',
							  'func'	=> 'voyageplus_two_third' ),
		'one-half'	=> array( 'name'	=> 'one-half',
							  'func'	=> 'voyageplus_one_half' ),
		'one-quarter' 	=> array( 'name'	=> 'one-quarter',
							  'func'	=> 'voyageplus_one_quarter' ),
		'three-quarter'	=> array( 'name'	=> 'three-quarter',
							  'func'	=> 'voyageplus_three_quarter' ),
		'hscroll'	=> array( 'name'	=> 'hscroll',
							  'func'	=> 'voyage_hscroll' ),
		'post-content' => array( 'name'	=> 'post-content',
							  'func'	=> 'vp_post_content' ),
	);
	return apply_filters( 'voyageplus_shortcodes', $codes);
}

public function voyageplus_adunit($atts) {
	extract(shortcode_atts(array(
	'unit' => '1',
	'align' => 'center',
	), $atts));
	
	if(is_user_logged_in() && current_user_can('administrator'))
		return;
	$options = Voyage_Plus::voyageplus_get_options();
	$adunit = 'adunit' . $unit;
	
	if (isset($options[$adunit]) && !empty($options[$adunit]) ) {
		ob_start();
		
		$class = 'vp-align' . $align;
		echo '<div class="' . $class . '">';			
		echo stripslashes($options[$adunit]);
		echo '</div>';
		$list = ob_get_clean();
		return $list;
	}
}
/*
* The [column] shortcode is inpired by Justin's idea
* http://wordpress.org/extend/plugins/grid-columns/
* but fundamentally different. It follows strickly
* to 960.gs with prefix and suffix parameters
*/
public function voyageplus_column($atts, $content = null) {
	extract(shortcode_atts(array(
	'span' => '1',
	'grid' => '4',
	'prefix' => '0',
	'suffix' => '0',
	), $atts));
	
	if (! in_array( $grid, $this->allowed_grids ) )
		$grid = 4; //Default
	if ($span < 1 || $span > $grid)
		$span = 1;
	if ($prefix < 0 || $prefix >= $grid)
		$prefix = 0;
	if ($suffix < 0 || $suffix >= $grid)
		$suffix = 0;
	$span = (int)$span * 60 / $grid; //Normalize span
	$prefix = (int)$prefix * 60 / $grid; //Normalize prefix]
	$suffix = (int)$suffix * 60 / $grid; //Normalize suffix]
	
	$alpha_omega = '';
	if ($this->col == 0)
		$alpha_omega = "alpha";	
	 
	$this->col = $this->col + $span + $prefix + $suffix;
	if ($this->col >= 60) {
		$alpha_omega = "omega";
		$this->col = 0;
	}

	ob_start();
	echo '<div class="vp-column-' . $span . ' vp-column ';
	if ($prefix > 0) {
		echo 'vp-prefix-' . $prefix . ' ';
	}
	if ($suffix > 0) {
		echo 'vp-suffix-' . $suffix . ' ';
	}	
	echo $alpha_omega . '">';
	echo do_shortcode($content);
	echo '</div>';
	if ($alpha_omega == "omega") {
		echo '<div class="clear"></div>';
	}	
	$list = ob_get_clean();
	return $list;
}
/* Takecare old column shortcodes such as one-third, etc*/
public function voyageplus_one_third($atts, $content = null) {
	$args = array(
		'span' => '1',
		'grid' => '3' );
	return $this->voyageplus_column( $args, $content);
}

public function voyageplus_two_third($atts, $content = null) {
	$args = array(
		'span' => '2',
		'grid' => '3' );
	return $this->voyageplus_column( $args, $content);
}

public function voyageplus_one_half($atts, $content = null) {
	$args = array(
		'span' => '1',
		'grid' => '2' );
	return $this->voyageplus_column( $args, $content);
}

public function voyageplus_one_quarter($atts, $content = null) {
	$args = array(
		'span' => '1',
		'grid' => '4' );
	return $this->voyageplus_column( $args, $content);
}

public function voyageplus_three_quarter($atts, $content = null) {
	$args = array(
		'span' => '3',
		'grid' => '4' );
	return $this->voyageplus_column( $args, $content);
}

function voyage_hscroll( $atts, $content = null ) {
	ob_start();
	echo '<div style="overflow: auto;">';
	echo do_shortcode($content);
	echo '</div>';
	
	$list = ob_get_clean();
	return $list;
}

function vp_post_content( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'id' => '0',
		'type' => 'page',
		'class' => '',
	), $atts ) );

	ob_start();
	if ( $id > 0 ) {
		if ( ! empty( $class ) )
			echo '<div class="' . $class . '">';
		$args = array(
			'post_status' => 'publish',
			'post_type' => $type,
			'post__in' => array( $id ) );
		$results = new WP_Query( $args );
		while ( $results->have_posts() ) {
			$results->the_post();
			the_content();
		}
		if ( ! empty( $class ) )
			echo '</div>';
		wp_reset_postdata();
	}
	$list = ob_get_clean();
	return $list;
}

}

new Voyage_Shortcodes();

?>