<?php

/*
	Plugin Name: Send Coupon
	Plugin URI: https://github.com/fmarzocca/DSFM-send-coupon
	Description: Send coupon file to users
	Version: 1.0
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
  return 'To use <i>Send Coupon</i> you need to have both <a href="'.admin_url().'plugin-install.php?tab=search&s=easy+fancybox">Easy Fancybox</a> and <a href="'.admin_url().'plugin-install.php?tab=search&s=contact+form+7">Contact Form 7</a> plugins installed and active for this plugin to work.<br/> Plugin would be inactive! After you make sure you have installed and activated all the required plugins you can reactivate it.';
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
			'tasto'		=>	"Stampa il Coupon",
			'titolo'		=>	"Scegli l'offerta",
			'testo'		=>	"Duis autem vel eum iriure dolor in hendrerit in vulputate.",
			'oldprice'		=>	"0€",
			'offerprice'		=>	"0€"
			
			 ), $atts);

	$randdiv = rand();
	$contact_form = do_shortcode("[contact-form-7 id=".$atts['cf7']."]");
	$hf = '<input type="hidden" name="_FM_the_title" value="' . get_the_title(). '" /><input type="hidden" name="_FM_coupon" value="' . $atts["filename"]. '" /></form>';
	$contact_form = str_replace("</form>", $hf, $contact_form);
	$return='<!-- coupon box -->
<div id="cp-offersdiv">
<div class="cp-offerstitle">'.$atts["titolo"].'</div>
<div class="pc-offerdiv" style="background-color:#efefef;">
<div class="cp-offerstext">'.$atts["testo"].'</div>
<div class="cp-offerpriceline"><div class="cp-offerpricelineprice">
<span class="cp-offeroldprice">'.$atts["oldprice"].'</span>&nbsp;<span class="cp-offerprice">'.$atts["offerprice"].'</span>
</div><div class="cp-offerpricelinebutton">&nbsp;&nbsp;<a class="fancybox" href="#'.$randdiv.'" target="_blank" rel="nofollow"><span class="coupon-button">'.$atts["tasto"].'</span></a><div class="fancybox-hidden" style="display: none;"><div id="'.$randdiv.'" class="hentry">'.$contact_form.'</div></div></div>
</div>
</div>
</div>';

	return $return;
}
add_shortcode ("richiedi-coupon","fm_requestcoupon");


add_action( 'wpcf7_before_send_mail', 'create_unique_coupon_and_send_it' );
function create_unique_coupon_and_send_it( $cf7 ) {
	
	global $wpdb;
	global $coupon_table_name;
	
	//check if this is the right form
	if(isset( $_POST['_FM_coupon'] )){
 		
		$uploads = wp_upload_dir();
		if ($cf7->mail['use_html']==true)
			$nl="<br/>";
		else
			$nl="\n";

		$submission = WPCF7_Submission::get_instance();
		if ( $submission ) {
			/* file contents are to be copied locally */
			$copy_to_send = $uploads['basedir']."/coupon_".rand().".pdf";
			$content = file_get_contents($_POST['_FM_coupon']);
			if ( file_put_contents($copy_to_send, $content ) !== false ){
			//Let'go to the file attachment!
				$submission->add_uploaded_file('coupon', $copy_to_send);
			}

		$mail2 = $cf7->prop( 'mail_2' );
		$body = $mail2['body'];
		$body = str_replace("[coupon-title]", $_POST['_FM_the_title'],$body);
		$mail2['body'] = $body; 
		$cf7->set_properties( array( 'mail_2' => $mail2 ) );
		
   		$posted_data = $submission->get_posted_data();
   	 	$nome = $posted_data["your-name"];
   		$cognome = $posted_data["your-surname"];
   		$email = $posted_data["your-email"];
   		$wpdb->insert(
   			 $coupon_table_name,
   			 array(
   			 	'time'		=>	current_time('mysql'),
   			 	'nome'		=>	$nome,
   				'cognome'	=>	$cognome,
   		 		'email'		=>	$email,
   		 		'coupon'	=>	 $_POST['_FM_the_title'],
   		 		)
   		 	);
		}
	}
}


/* Add CSS */	
function DSFM_send_coupon_css(){
		wp_register_style( 'DSFM_send_coupon_css', plugins_url( 'DSFM-send-coupon.css' , __FILE__ ) );
		wp_enqueue_style( 'DSFM_send_coupon_css' );
	} // function
add_action( 'wp_enqueue_scripts', 'DSFM_send_coupon_css' );