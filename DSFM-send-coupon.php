<?php

/*
	Plugin Name: Send Coupon
	Plugin URI: https://github.com/fmarzocca/DSFM-send-coupon
	Description: Send coupon file to users
	Version: 0.f
	Author: Fabio Marzocca
	Author URI: http://www.marzocca.net
	Text Domain:   DSFM-send-coupon
  	Domain Path:   /languages/
	License: GPL2
	
	Copyright 2015  by Fabio Marzocca  (email : marzoccafabio@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


defined('ABSPATH') or die("No script kiddies please!");

global $wpdb;
global $DSFM_db_version;
$DSFM_db_version = '1.0';

global $coupon_table_name;
$coupon_table_name = $wpdb->prefix . 'coupon_log';


function showAdminMessages()
{
  return 'To use <i>DSFM Send Coupon</i> you need to have both <a href="'.admin_url().'plugin-install.php?tab=search&s=easy+fancybox">Easy Fancybox</a> and <a href="'.admin_url().'plugin-install.php?tab=search&s=contact+form+7">Contact Form 7</a> plugins installed and active for this plugin to work.<br/> Plugin would be inactive! After you make sure you have installed and activated all the required plugins you can reactivate it.';
}

function coupon_plugin_init() {
	if ( !(is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) && (is_plugin_active( 'easy-fancybox/easy-fancybox.php' ) ))) {
		die(showAdminMessages());
	} 
	
	DSFM_db_install();
}
register_activation_hook( __FILE__, 'coupon_plugin_init' );

/* Installa la tabella nel DB */
function DSFM_db_install() {
	global $wpdb;
	global $DSFM_db_version;
	global $coupon_table_name;
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $coupon_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		nome tinytext NOT NULL,
		cognome tinytext NOT NULL,
		coupon tinytext NOT NULL,
		email tinytext NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'DFSM_db_version', $DSFM_db_version );
}



function fm_requestcoupon ($atts) {
	$atts = shortcode_atts(array(
			'cf7'			=>	null,
			'filename'		=>	null,
			'titolo'		=>	"Richiedi coupon"
			 ), $atts);

	$contact_form = do_shortcode("[contact-form-7 id=".$atts['cf7']."]");
	$hf = '<input type="hidden" name="_FM_coupon" value="' . $atts["filename"]. '" /></form>';
	$contact_form = str_replace("</form>", $hf, $contact_form);
	$return='<a class="fancybox" href="#coupon" target="_blank" rel="nofollow"><span class="coupon-button">'.$atts["titolo"].'</span></a><div class="fancybox-hidden" style="display: none;"><div id="coupon" class="hentry">'.$contact_form.'</div></div>';

	return do_shortcode($return);;

}
add_shortcode ("richiedi-coupon","fm_requestcoupon");


add_action( 'wpcf7_before_send_mail', 'create_unique_coupon_and_send_it' );
function create_unique_coupon_and_send_it( $cf7 ) {
	
	//check if this is the right form
	if(isset( $_POST['_FM_coupon'] )){
 		
		$uploads = wp_upload_dir();
		if ($cf7->mail['use_html']==true)
			$nl="<br/>";
		else
			$nl="\n";

		//set filenames
		$master_copy=$uploads['basedir']."/".$_POST['_FM_coupon'];
		$copy_to_send=$uploads['basedir']."/attachment_".$_POST['_FM_coupon'];
		
		//make a copy of the master file and attach it
		if ( copy( $master_copy, $copy_to_send ) ){
			$submission = WPCF7_Submission::get_instance();
			$submission->add_uploaded_file('coupon', $copy_to_send);
		}
		
	}
}


/* Add CSS */	
function DSFM_send_coupon_css(){
		wp_register_style( 'DSFM_send_coupon_css', plugins_url( 'DSFM-send-coupon.css' , __FILE__ ) );
		wp_enqueue_style( 'DSFM_send_coupon_css' );
	} // function
add_action( 'wp_enqueue_scripts', 'DSFM_send_coupon_css' );