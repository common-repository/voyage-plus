<?php
/* Action Hooks for wp_head() and wp_footer()
 * @package   VoyagePlus
 * @author    VoyageTheme
 * @copyright Copyright (c) 2013, VoyageTheme
 */
class Voyage_Plus_Actions {

var $header_script;
var $footer_script;

public function __construct() {
	// Admin Option Menu
    if(!is_admin()){
		$options = Voyage_Plus::voyageplus_get_options();
		$this->header_script = $options['headerscript'];
		$this->footer_script = $options['footerscript'];
		if (!empty($this->header_script))
			add_action('wp_head', array($this,'voyageplus_wp_head'));
		if (!empty($this->footer_script))
			add_action('wp_footer', array($this,'voyageplus_wp_footer'));
	}
}

public function voyageplus_wp_head() {
	echo stripslashes($this->header_script) . "\n";		
}

public function voyageplus_wp_footer() {
	echo stripslashes($this->footer_script) . "\n";		
}

}

new Voyage_Plus_Actions();

?>