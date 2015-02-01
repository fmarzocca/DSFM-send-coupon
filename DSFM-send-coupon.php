<?php

/*
	Plugin Name: Send Coupon
	Plugin URI: https://github.com/fmarzocca/display-site-numbers
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
