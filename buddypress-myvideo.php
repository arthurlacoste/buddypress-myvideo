<?php

// Vérification de la présence de BuddyPress sur l'installation Wordpress
if ( !function_exists( 'bp_core_install' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		require_once  WP_PLUGIN_DIR . '/buddypress/bp-loader.php' ;
	} else {
		add_action( 'admin_notices', 'bpmv_install_buddypress_notice' );
		return;
	}
}

// Alerte correspondante 
function bpmv_install_buddypress_notice() {
	echo '<div id="message" class="error fade bp-tweet-upgraded"><p style="line-height: 150%">';
	_e('<strong>BuddyPress MyVideo</strong></a> requires the BuddyPress plugin to work. Please <a href="http://buddypress.org/download">install BuddyPress</a> first, or <a href="plugins.php">deactivate BuddyPress MyVideo</a>.');
	echo '</p></div>';
}

/*
 *===================================================
 * Fonctions d'affichage / mise en forme de la vidéo
 *===================================================
 */

/** 
 * vérification de la présence de la vidéo et affichage de celle-ci
 * @param string lien vers une vidéo
 * @return string  
 * liens de type			http://www.youtube.com/watch?v=W69W0Fh8Vnw
 *							http://tivipro.tv/chaine_sshome.php?id=1807375
 *							http://www.dailymotion.com/video/xjfk1w_hugh 
 */
function bpmv_display_myvideo_embed($link) {

	// on ne traite pas la chaine si elle ne contient aucune données
	if (!$link=='') {
	
		// création d'une instance de la classe AutoEmbed
		require_once 'AutoEmbed/AutoEmbed.class.php';
		$videoEmbed = new AutoEmbed();

		// vérification de l'existance d'une correspondance
		if ($videoEmbed->parseUrl($link)) {
		
			// définition de la taille de la vidéo pour l'affichage dans la popup
			$videoEmbed->setWidth('576');
			$videoEmbed->setHeight('420');
			
			// envoi du code embed de la vidéo
			return $videoEmbed->getEmbedCode(); 
		}
	}
	return ;
}

/** 
 * @param string embed obtenu par bpmv_display_myvideo_embed($link)
 * @param string Nom de l'utilisateur
 * @param string identifiant unique (optionnel)
 * @return string retourne la popup
 */
function display_overlay($video, $name , $id='member') {
				return '
<div class="overlay" id="overlay-' . $id . '">
	<div class="overlay-title">' . __('Vidéo de profil de ') . $name . '</div>
	' . $video . '
</div>
			';
}

/*
 *=======
 * HOOKS
 *=======
 */
 
// affiche le lien vers la video sur les pages activity/ groups/ et members/
function bpmv_display_myvideo_activity() {

	// récupère le champ de profil correspondant à la vidéo
	$video = xprofile_get_field_data( 'Vidéo' ,bp_get_activity_user_id());
	$embed = bpmv_display_myvideo_embed($video);
	
	if($embed) {

		$type = bp_get_activity_type();
		$activity_id = bp_get_activity_id();
		$nom =  bp_core_get_user_displayname(bp_get_activity_user_id());
		
		// les entrée suivantes sont plus petites et doivent donc avoir une mise en forme différente
		switch ($type){
		
		//activitées petites
		case 'created_group';
		case 'friendship_created';
		case 'new_member';
		case 'joined_group';
			echo  '<span class="bp-myvideo-button-mini"><a rel="#overlay-' . $activity_id . '" href="' . $video . '"class=" buddypress-myvideo-button" title="' . __('Vidéo de profil de ') . $nom . '"><img src="' . get_bloginfo('wpurl'). '/wp-content/plugins/buddypress-myvideo/img/icone-video.gif" alt="' . _('vidéo'). '" /></a></span>';
			break;
			
		// activités moyennes
		case 'activity_comment' :
			echo '<span class="bp-myvideo-button-moyen"><a rel="#overlay-' . $activity_id . '" href="' . $video . '"class=" buddypress-myvideo-button" title="' . __('Vidéo de profil de ') . $nom . '"><img src="' . get_bloginfo('wpurl'). '/wp-content/plugins/buddypress-myvideo/img/icone-video.gif" alt="' . _('vidéo'). '" /> </a></span>';
			break;
			
		// activités massives
		default:
			echo  '<span class="bp-myvideo-button"><a rel="#overlay-' . $activity_id . '" href="' . $video . '"class="buddypress-myvideo-button" title="' . __('Vidéo de profil de ') . $nom . '">' . _('vidéo'). '</a></span>';
			break;
		}

		echo display_overlay($embed, $nom, $activity_id);
	}
}
add_action('bp_before_activity_entry', 'bpmv_display_myvideo_activity', 999);

// affiche le lien vers la video sur les page /members/
/*
function bpmv_display_myvideo_profil(){
	$video = xprofile_get_field_data( 'Vidéo' ,bp_get_activity_user_id());
	$embed = bpmv_display_myvideo_embed($video);
	$nom =  xprofile_get_field_data( 'Name' ,bp_get_activity_user_id());
	if($embed) {
		echo  '<div class="bp-myvideo-button-profile generic-button"><a rel="#overlay-member" href="' . $video . '"class="buddypress-myvideo-button" title="' . __('Vidéo de profil de ') . $nom . '">' . _('Vidéo'). '</a></div>';
		echo display_overlay($embed, $nom);
	}
}
add_action('bp_member_header_actions','bpmv_display_myvideo_profil');
*/

// affiche le lien vers la video sur les page /members/ (header)
function bpmv_display_myvideo_member($val) {
	global $bp;
	
	$video = xprofile_get_field_data( 'Vidéo' , $bp->displayed_user->id );
	$embed = bpmv_display_myvideo_embed($video);
	$nom =  $bp->displayed_user->fullname;
	if($embed) {
		return 	$val . '<a rel="#overlay-member" href="' . $video . '"class="bp-myvideo-button-profile" title="' . __('Vidéo de profil de ') . $nom . '">' . _('Voir la vidéo de profil') . '</a>' . display_overlay($embed, $nom) ;
	}
	return $val;
}
add_filter('bp_get_displayed_user_avatar','bpmv_display_myvideo_member');

// Members directory
function bpmv_display_myvideo_members_directory(){
	global $bp;
	$user_id = bp_get_member_user_id();
	$video = bp_get_member_profile_data('field=Vidéo');
	$embed = bpmv_display_myvideo_embed($video);
	$nom =  bp_get_member_name();
	if($embed) {
		echo '<div class="bp-myvideo-button-directory"><a rel="#overlay-' . $user_id . '" href="' . $video . '" title="' . __('Vidéo de profil de ') . $nom . '">' . _('Vidéo') . '</a></div>' . display_overlay($embed, $nom, $user_id) ;
	}
}
add_action('bp_directory_members_item','bpmv_display_myvideo_members_directory');

// Personnalisation du champ de profil
function bpmv_set_video_field($field_value) {
	$bp_this_field_name = bp_get_the_profile_field_name();
	if($bp_this_field_name=='Vidéo') {
		$field_value = strip_tags( $field_value );
		$field_value = '<a href="' . $field_value . '">'.__('Voir la vidéo').'</a>';	
	}
	return $field_value;
}
add_filter( 'bp_get_the_profile_field_value','bpmv_set_video_field');

/*
 *===================
 * Javascript & css.
 *===================
 */
function bpmv_scripts() {
	wp_enqueue_script( "buddypress-myvideo-jquery-tools", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/js/jquery.tools.min.js")); 
	wp_enqueue_script( "buddypress-myvideo-mootools", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/js/bp-myvideo.js"));
}
add_action('wp_print_scripts', 'bpmv_scripts');


function bpmv_button_insert_head() {
	?>
	<link href="<?php bloginfo('wpurl'); ?>/wp-content/plugins/buddypress-myvideo/style.css" media="screen" rel="stylesheet" type="text/css"/>
	<?php	
}
add_action('wp_head', 'bpmv_button_insert_head');

