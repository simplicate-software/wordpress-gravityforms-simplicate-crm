<?php

/*
Plugin Name: Gravity Forms Simplicate CRM Add-On
Plugin URI: http://www.simplicate.nl
Description: Integratie Gravity Forms met Simplicate, dit zorgt ervoor dat de inzendingen automatisch worden verzonden naar je Simplicate omgeving.
Version: 1.0
Author: Simplicate
Author URI: http://www.simplicate.nl
Text Domain: gravityformssimplicatecrm
Domain Path: /lang
*/

define( 'GF_SIMPLICATECRM_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_SimplicateCRM_Bootstrap', 'load' ), 5 );

class GF_SimplicateCRM_Bootstrap {

	public static function load(){
		require_once( 'class-gf-simplicatecrm.php' );
		GFAddOn::register( 'GFSimplicateCRM' );
	}

}

function gf_simplicatecrm() {
	return GFSimplicateCRM::get_instance();
}