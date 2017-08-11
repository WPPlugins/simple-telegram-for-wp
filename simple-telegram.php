<?php
/*
Plugin Name: Simple Telegram for WP
Version: 0.9.3
Plugin URI: https://tchgdns.de/simple-telegram-for-wordpress/
Description: Postet neue WordPress-Artikel automatisch in einem Telegram-Kanal.
Author: Marcel Schmilgeit
Author URI: https://tchgdns.de
Text Domain: simple-telegram
Domain Path: /lang
*/


/*
Copyright (C)  2012-2015 Marcel Schmilgeit

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/




if ( !function_exists( 'add_action' ) ) {
	echo( 'Hi there!  I\'m just a plugin, not much I can do when called directly.' );
	exit;
}



/* PHP-Fehlerausgabe deaktivieren */
//error_reporting(0);



/**
* Ein paar Variablen
*/
load_plugin_textdomain( 'simple-telegram', false, basename( dirname( __FILE__ ) ) . '/lang/' );
define( 'SIMPLETELEGRAM_PLUGIN_NAME', 'Simple Telegram' ); 
define( 'SIMPLETELEGRAM_PLUGIN_VERSION', '0.9.3' );


/**
* Includierung benötigter Scripte und Dateien
*/
if ( is_admin() ) {
	include_once( 'settings-page.php' );
}
require_once( 'notifcaster.class.php' );



/**
* Registrierung des Plugins
*
* @since   1.0.0
* @change  n/a
*
* @param   array  $plugin_array     Plugin-Array [WordPress]
* @return  array  $plugin_array     Plugin-Array [WordPress]
*/

function simpleTelegram_register( $plugin_array ) {
}



/**
* "Einstellungen"-Link zur Plugin-Liste hinzufügen
*
* @since   1.0.0
* @change  n/a
*
* @param   array   $links  Array der eingetragenen Links [WordPress]
* @param   string  $file   Aufgerufene Datei [WordPress]
* @return  array   $links  Rückgabe der überarbeiteten Links [WordPress]
*/

function simpleTelegram_addSettings( $links, $file ) {
	static $this_plugin;
	if ( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=simple-telegram">' . esc_html__('Settings', 'simple-telegram') . '</a>';
		$links = array_merge( array( $settings_link ), $links );
	}
	return( $links );
}


/**
* Weitere Links zur Plugin-Beschreibung in der Liste hinzufügen
*
* @since   1.0.0
* @change  n/a
*
* @param   array   $links  Array der eingetragenen Links [WordPress]
* @param   string  $file   Aufgerufene Datei [WordPress]
* @return  array   $links  Rückgabe der überarbeiteten Links [WordPress]
*/

function simpleTelegram_addLinks( $links, $file ) {
	static $this_plugin;
	if ( !$this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}
	if ( $file == $this_plugin ) {
		$links = array();
		$links[] = 'Version ' . SIMPLETELEGRAM_PLUGIN_VERSION;
		$links[] = '<a target="_blank" href="https://twitter.com/Marcelismus">' . esc_html__('Follow me on Twitter', 'simple-telegram') . '</a>';
		$links[] = '<a target="_blank" href="https://tchgdns.de/simple-telegram-for-wordpress/">' . esc_html__('Plugin page', 'simple-telegram') . '</a>';
		$links[] = '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/simple-telegram">' . esc_html__('Rate the plugin', 'simple-telegram') . '</a>';
		$links[] = '<a target="_blank" href="http://www.amazon.de/gp/registry/wishlist/1FC2DA2J8SZW7">' . esc_html__('My Amazon Wishlist', 'simple-telegram') . '</a>';
		$links[] = '<a target="_blank" href="https://www.paypal.me/marcelismus">' . esc_html__('PayPal-Donation', 'simple-telegram') . '</a>';
	}
	return( $links );
}


/**
* Ausgabe von Fehlermeldungen
*
* @since   1.0.0
* @change  n/a
*
* @param   string  $message  Fehlermeldung
*/

function simpleTelegram_triggerError( $message ) {
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'error_scrape' ) {
        echo( "<strong>$message</strong>" );
        exit;
    } else {
    	trigger_error( $message, E_USER_ERROR );
    }
}


/**
* Aktivierung des Plugins
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_activatePlugin( $network_wide ) {
	if ( version_compare(phpversion(), '5.0') == -1 ) simpleTelegram_triggerError( esc_html__('To use this plugin requires at least PHP version 5.0 is required.', 'simple-telegram') );
	if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
		global $wpdb;
		$current_blog = $wpdb->blogid;
		$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog );
			simpleTelegram_activateActions();
		}
		switch_to_blog( $current_blog );
	} else {
		simpleTelegram_setOptions();
	}
}


/**
* Prüfen ob Versionsnummer älter oder neuer
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_checkOlderVersion( $this_ver = '', $comp_ver = '' ) {
	if ( $this_ver == '' ) $this_ver = SIMPLETELEGRAM_PLUGIN_VERSION;
	if ( $comp_ver == '' ) $comp_ver = get_option( "simpleTelegram_pluginVersion" );
	$this_ver = str_replace( ".", "", $this_ver );
	$comp_ver = str_replace( ".", "", $comp_ver );
	if ( $this_ver > $comp_ver ) {
		return( true );
	}
}


/**
* Benötigte Update-Funktionen durchführen
*
* @since   1.0.0
* @change  n/a
*/

if ( is_admin() ) simpleTelegram_UpdateAction();

function simpleTelegram_updateAction() {
	/* Wenn vorherige Version älter als 0.9.1 */ 
	if ( simpleTelegram_checkOlderVersion( '0.9.1' ) ) {
		simpleTelegram_setOptions();
		delete_option( 'simpleTelegram_webPreview' );
		delete_option( 'simpleTelegram_featuredImage' );
	}
	/* Grundsätzlich nach Update zu prüfen */ 
	if ( get_option('simpleTelegram_pluginVersion') != SIMPLETELEGRAM_PLUGIN_VERSION ) {
		simpleTelegram_setOptions();
	}
	/* Neue Versionsnummer in die Datenbank schreiben */ 
	update_option( 'simpleTelegram_pluginVersion', SIMPLETELEGRAM_PLUGIN_VERSION );
}


/**
* Standardeinstellungen vornehmen
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_setOptions() {
	if ( get_option( 'simpleTelegram_botToken' ) === false ) {
		update_option( 'simpleTelegram_botToken', '', 'no' );
	}
	if ( get_option( 'simpleTelegram_channelName' ) === false ) {
		update_option( 'simpleTelegram_channelName', '', 'no' );
	}
	if ( get_option( 'simpleTelegram_autoPost' ) === false ) {
		update_option( 'simpleTelegram_autoPost', true, 'no' );
	}
	if ( get_option( 'simpleTelegram_messageStyle' ) === false ) {
		update_option( 'simpleTelegram_messageStyle', 'webpreview', 'no' );
	}
	if ( get_option( 'simpleTelegram_markUpdate' ) === false ) {
		update_option( 'simpleTelegram_markUpdate', true, 'no' );
	}
	if ( get_option( 'simpleTelegram_messageTemplate' ) === false ) {
		update_option( 'simpleTelegram_messageTemplate', '{TITLE}
		
{FULLURL}
		
{TAGS}', 'no' );
	}
	update_option( 'simpleTelegram_pluginVersion', SIMPLETELEGRAM_PLUGIN_VERSION );
}


/**
* Deinstallation des Plugins
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_uninstallPlugin() {
    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
        global $wpdb;
        $current_blog = $wpdb->blogid;
        $blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blogs as $blog ) {
        	switch_to_blog( $blog );
        	simpleTelegram_uninstallActions();
        }
        switch_to_blog( $current_blog );
    } else {
    	simpleTelegram_uninstallActions();
    }
}


/**
* Deinstallation-Actions des Plugins
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_uninstallActions() {
	global $wpdb;
	$wpdb->query( "DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE 'simpleTelegram_%';" );
	delete_post_meta_by_key( '_simpleTelegram_wasSent' );
	delete_post_meta_by_key( '_simpleTelegram_postTelegram' );
}


/**
* Prüft ob Bot und Kanal aktiv sind
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_isActive() {
	if ( '' == get_option('simpleTelegram_botToken') ) {
		return( '1' );
	}
	else if ( '' == get_option('simpleTelegram_channelName') ) {
		return( '2' );
	}
	else if ( '' != get_option('simpleTelegram_botToken') ) {
		$nt = new Notifcaster_Class();
		$nt->_telegram( get_option('simpleTelegram_botToken') );
		$result = $nt->get_bot();
		if ( '1' != $result['ok'] ) {
			return( '3' ); 
		}
	}
	return( true );
}


/**
* Aktivierung für neue Multisite-Blogs nach Plugin-Aktivierung
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_activateBlogMultisite( $blogID ) {
    global $wpdb;
    if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		switch_to_blog( $blogID );
		my_plugin_activate();
		restore_current_blog();
    }
}
add_action( 'wpmu_new_blog', 'simpleTelegram_activateBlogMultisite' );


/**
* Checkbox in die Postingbox auf der Artikelseite einbauen
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_postIt() {
	global $post;
	if ( simpleTelegram_isActive() === true ) {
		$toChannel = false;
		if ( get_option('simpleTelegram_autoPost' ) ) {
			$toChannel = true;
		}
		$wasSent = get_post_meta( $post->ID, '_simpleTelegram_wasSent', true );
		if ( true == $wasSent ) {
			$toChannel = false;
		}
		if ( 'publish' == get_post_status ( $post->ID ) ) {
			$toChannel = false;
		} else {
			if ( 'auto-draft' != get_post_status ( $post->ID ) ) {
				switch ( get_post_meta( $post->ID, '_simpleTelegram_postTelegram', true ) ) {
					case 0:
						$toChannel = false;
						break;
					case 1:
						$toChannel = true;
						break;
				}
			}
		}
		wp_nonce_field( plugin_basename(__FILE__), 'simpleTelegram_toChannel_nonce' );
		?>
			<div class="misc-pub-section simple-telegram">
				<label for="simpleTelegram_toChannel">
					<input type="checkbox" name="simpleTelegram_toChannel" id="simpleTelegram_toChannel" value="1" <?php checked( $toChannel ); ?>/>
					<?php if ( true == $wasSent ): ?>
						<?php esc_html_e('Re-post to Telegram channel', 'simple-telegram'); ?>
					<?php else: ?>
						<?php esc_html_e('Post to Telegram channel', 'simple-telegram'); ?>
					<?php endif; ?>
				</label>
			</div>
		<?php
	}
}
add_action( 'post_submitbox_misc_actions', 'simpleTelegram_postIt' );



/**
* Speichert die Checkbox in der Postingbox auf der Artikelseite
*
* @since   1.0.0
* @change  n/a
*
* @param   string  $post_id   ID des Posts
*/

function simpleTelegram_saveIt( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return( false );
    if ( empty( $post_id ) ) return( false );
    if ( false !== wp_is_post_revision( $post_id ) ) return( false );
    if ( !wp_verify_nonce( $_POST['simpleTelegram_toChannel_nonce'], plugin_basename(__FILE__) ) ) return( $post_id );
    if ( simpleTelegram_isActive() !== true ) return( false );
    
    if ( 'publish' != get_post_status( $post_id ) ) {
    	if ( $_POST['simpleTelegram_toChannel'] ) {
    		update_post_meta( $post_id, '_simpleTelegram_postTelegram', 1 );
    	}
    	else {
    		update_post_meta( $post_id, '_simpleTelegram_postTelegram', 0 );
    	}
    }
    
}
add_action( 'save_post', 'simpleTelegram_saveIt' );



/**
* Telegram-Freigabe für sofort veröffentlichte Artikel
*
* @since   0.9.3
* @change  n/a
*
* @param   string  $post_id   ID des Posts
*/

function simpleTelegram_publishPost( $post_id ) {   

	if ( is_admin() ) {
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return( false );
	    if ( empty( $post_id ) ) return( false );
	    if ( false !== wp_is_post_revision( $post_id ) ) return( false );
	    if ( !wp_verify_nonce( $_POST['simpleTelegram_toChannel_nonce'], plugin_basename(__FILE__) ) ) return( $post_id );
	    if ( !isset( $_POST['simpleTelegram_toChannel'] ) ) return( $post_id );
	    if ( true != $_POST['simpleTelegram_toChannel'] ) return( false );
	    if ( simpleTelegram_isActive() !== true ) return( false );
	}
	
	if ( !defined( 'DOING_CRON' ) && !DOING_CRON ) {
		update_post_meta( $post_id, '_simpleTelegram_postTelegram', $_POST['simpleTelegram_toChannel'] );
	}
    
    $botToken = get_option('simpleTelegram_botToken');
    $channelName = get_option('simpleTelegram_channelName');
    
    switch ( get_option('simpleTelegram_messageStyle') ) {
	    case 'plaintext':
	    	$disableWebPreview = true;
	    	$featuredImage = false;
	    	break;
    	case 'featuredimage':
    		$disableWebPreview = true;
    		$featuredImage = true;
    		break;
    	case 'webpreview':
    	default:
    		$disableWebPreview = false;
    		$featuredImage = false;
    		break;
    }
    
    $theMessage = get_option('simpleTelegram_messageTemplate');
    if ( '' == $theMessage ) {
    	$theMessage = '{TITLE} {FULLURL}';
    }
    
    $theMessage = str_replace( '{TITLE}', get_the_title( $post_id ), $theMessage );
    $theMessage = str_replace( '{FULLURL}', get_permalink( $post_id ), $theMessage );
    $theMessage = str_replace( '{SHORTURL}', wp_get_shortlink( $post_id ), $theMessage );
    $theMessage = str_replace( '{EXCERPT}', wp_trim_words( get_post_field( 'post_content', $post_id ), 60, '...' ), $theMessage );
    
    if ( strpos( $theMessage, '{TAGS}' ) !== false ) {
    	$postTags = wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
    	foreach ($postTags as $tag) {
    		$tagList .= ' #' . str_replace( ' ', '', $tag );
    	}
    	$theMessage = str_replace( '{TAGS}', substr( $tagList, 1 ), $theMessage );
    }
    
    if ( strpos( $theMessage, '{CATEGORIES}' ) !== false ) {
    	$postCategories = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );
    	foreach ($postCategories as $category) {
    		$categoriesList .= $category . ', ';
    	}
    	$theMessage = str_replace( '{CATEGORIES}', substr( $categoriesList, 0, -2 ), $theMessage );
    }
    
    if ( get_option('simpleTelegram_markUpdate') && get_post_meta( $post_id, '_simpleTelegram_wasSent', true ) ) {
    	$theMessage = '*[' . strtoupper( esc_html('Update', 'simple-telegram') ) . ']* ' . $theMessage;
    }
    
    if ( $featuredImage && !has_post_thumbnail( $post_id ) ) {
    	$featuredImage = false;
    } else if ( $featuredImage ) {
    	$theFeaturedImage = get_attached_file( get_post_thumbnail_id( $post_id ) );
    }
    
    $nt = new Notifcaster_Class();
    $nt->_telegram( $botToken, 'markdown', $disableWebPreview );
	
	if ( $featuredImage ) {
		$sentResult = $nt->channel_photo( $channelName, $theMessage, $theFeaturedImage );
	} else {
		$sentResult = $nt->channel_text( $channelName, $theMessage);
	}
	
	if ( true == $sentResult["ok"] ) {
		add_post_meta( $post_id, '_simpleTelegram_wasSent', true );
		delete_post_meta( $post_id, '_simpleTelegram_postTelegram' );
	}
}
add_action( 'publish_post', 'simpleTelegram_publishPost', 10, 2 );



/* Diverse Filter, Aktionen und Hooks registrieren */
add_filter( 'plugin_action_links', 'simpleTelegram_addSettings', 10, 2 );
add_filter( 'plugin_row_meta', 'simpleTelegram_addLinks', 10, 2 );
add_action( 'plugins_loaded', 'simpleTelegram_updateAction' );
add_action( 'admin_menu', 'simpleTelegram_pageInit' );
register_activation_hook( __FILE__, 'simpleTelegram_activatePlugin' );
register_uninstall_hook( __FILE__, 'simpleTelegram_uninstallPlugin' );


?>