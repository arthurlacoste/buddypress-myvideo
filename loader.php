<?php 
/*
Plugin Name: AddMore BuddyPress MyVideo
Plugin URI: http://irz.fr/
Description: Pear your video profile on your Network !
Version: 0.0.5
Author: Arthur Lacoste
Author URI: http://irz.fr
*/  

function bpmv_init() {
	require dirname( __FILE__ ) . '/buddypress-myvideo.php' ;
}
add_action( 'bp_include', 'bpmv_init' );

?>