<?php

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_admin() ) die;

define( 'SIMPLETELEGRAM_URL_PAYPAL', 'https://www.paypal.me/marcelismus' ); 
define( 'SIMPLETELEGRAM_URL_AMAZON', 'http://www.amazon.de/gp/registry/wishlist/1FC2DA2J8SZW7?tag=wpappbox-21' );


/**
* Registrierung der Einstellungen des Plugins
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_pageInit() {
	$settings_page = add_options_page( SIMPLETELEGRAM_PLUGIN_NAME . ' Einstellungen', SIMPLETELEGRAM_PLUGIN_NAME, 'manage_options', 'simple-telegram', 'simpletelegram_options_page' );
	add_action( "load-{$settings_page}", 'simpleTelegram_loadSettingsPage' );
}


/**
* Initalisierung der Adminseite [deprecated]
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_adminInit() {
}


/**
* Optionsseiten laden
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_loadSettingsPage() {
	if ( 'Y' == $_POST["simple-telegram-settings-submit"] ) {
		check_admin_referer( "simple-telegram-setting-page" );
		simpleTelegram_saveSettings();
		$url_parameters = isset( $_GET['tab'] ) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
		wp_redirect( admin_url( "options-general.php?page=simple-telegram&$url_parameters" ) );
		exit;
	}
}


/**
* Einstellungen in "wp_options" speichern
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_saveSettings() {			
	if ( '' != Trim( $_POST['simpleTelegram_channelName'] ) ) {
		$channelName = Trim( $_POST['simpleTelegram_channelName'] );
		if ( strpos( $channelName, '@' ) === false ) {
			$channelName = '@' . $channelName;
		}
	}
	update_option( 'simpleTelegram_botToken', Trim( $_POST['simpleTelegram_botToken'] ), 'no' );
	update_option( 'simpleTelegram_channelName', $channelName, 'no' );
	update_option( 'simpleTelegram_autoPost', $_POST['simpleTelegram_autoPost'], 'no' );
	update_option( 'simpleTelegram_messageStyle', $_POST['simpleTelegram_messageStyle'], 'no' );	   		
	update_option( 'simpleTelegram_markUpdate', $_POST['simpleTelegram_markUpdate'], 'no' );
	update_option( 'simpleTelegram_messageTemplate', wp_filter_kses( $_POST['simpleTelegram_messageTemplate'] ), 'no' );
	update_option( 'simpleTelegram_pluginVersion', SIMPLETELEGRAM_PLUGIN_VERSION, 'no' );
}


/**
* Hilfe-Link zurückgeben
*
* @since   1.0.0
* @change  n/a
*/

function simpleTelegram_helpLink() {			
	if ( ( 'de_DE' == get_locale() ) || ( 'de_CH' == get_locale() ) ) {
		$helpLink = 'https://tchgdns.de/simple-telegram-for-wordpress/';
	} else {
		$languageCode = substr( get_locale(), 0, 2 );
		$helpLink = 'https://translate.google.com/translate?hl=en&sl=de&tl=' . $languageCode . '&u=' . urldecode( 'https://tchgdns.de/simple-telegram-for-wordpress/' ) . '&sandbox=1';
	}
	return( $helpLink );
}


/**
* Erzeugung und Ausgabe der Optionsseiten
*
* @since   1.0.0
* @change  n/a
*
* @output  HTML-Ausgabe der Optionsseiten
*/

function simpleTelegram_options_page() {
	?>
	<div class="wrap">
		<style>
			hr {
				margin-top: 10px !important;
				margin-bottom: 30px !important;
			}
			.st-infobox {
				display: block;
				background: #fff;
				border-left: 4px solid #fff;
				-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
				margin: 20px 0 25px 0;
				padding: 10px 16px
			}
			.st-infobox.st-notice {
				border-left: 4px solid #ffba00;
			}
			.st-infobox.st-active {
				border-left: 4px solid #46b450;
			}
			.st-infobox.st-error {
				color: #b94a48;
				border-left-color: #dc3232
			}
			.st-infobox + h3 {
				padding-top: 8px;
			} 
			.st-infobox + .form-table {
				margin-top: -10px !important;
			} 
			.dashicons, .dashicons-before:before {
				line-height: 1.1 !important;
			}
		</style>
		<div id="icon-options-general" class="icon32">
			<br>
		</div>
		<h2><?php echo( SIMPLETELEGRAM_PLUGIN_NAME ); ?> (Version <?php echo( SIMPLETELEGRAM_PLUGIN_VERSION ); ?>)</h2>
		<div class="widget" style="margin:0; position: relative;">
			<p style="margin:0; margin-left: 0; margin-top: 16px; display: inline-block;">
				<a href="https://twitter.com/Marcelismus" target="_blank"><?php esc_html_e('Follow me on Twitter', 'simple-telegram'); ?></a> | <a href="https://tchgdns.de/simple-telegram-for-wordpress/" target="_blank"><?php esc_html_e('Visit the Plugin plage', 'simple-telegram'); ?></a> | <a href="http://wordpress.org/extend/plugins/simple-telegram-for-wp/" target="_blank"><?php esc_html_e('Plugin at WordPress Directory', 'simple-telegram'); ?></a> | <a href="http://wordpress.org/plugins/simple-telegram-for-wp/changelog/" target="_blank"><?php esc_html_e('Changelog', 'simple-telegram'); ?></a>
			</p>
			<div style="display: inline-block; position: absolute; right: 0;">
				<a href="<?php echo( SIMPLETELEGRAM_URL_PAYPAL ); ?>" class="button-primary" target="_blank" style="font-size: 12px; padding: 5px 6px; line-height: 13px !important; height: auto !important; margin-right: 6px; margin-top: 13px;"><?php esc_html_e('PayPal-Donation', 'simple-telegram'); ?></a>
				<a href="<?php echo( SIMPLETELEGRAM_URL_AMAZON ); ?>" class="button-primary" target="_blank" style="font-size: 12px; padding: 5px 6px; line-height: 13px !important; height: auto !important; margin-top: 13px;"><?php esc_html_e('Amazon Wishlist', 'simple-telegram'); ?></a>
			</div>
		</div>
		<form method="post" action="<?php admin_url( 'options-general.php?page=simple-telegram' ); ?>">
		<?php wp_nonce_field( "simple-telegram-setting-page" ); ?>
		<hr />
		
		<?php $statusMeldung = simpleTelegram_isActive(); ?>
		
		<?php if ( '1' === $statusMeldung ): ?>
			<div class="st-infobox st-notice">
				<p><?php esc_html_e('Simple Telegram isn\'t active. Please set a admin bot for your Telegram channel.', 'simple-telegram'); ?> :-)</p>
			</div>
		<?php elseif ( '2' === $statusMeldung ): ?>
			<div class="st-infobox st-notice">
				<p><?php esc_html_e('Simple Telegram isn\'t active. Please fill in the name of your Telegram channel.', 'simple-telegram'); ?> :-)</p>
			</div>
		<?php elseif ( '3' === $statusMeldung ): ?>
			<div class="st-infobox st-error">
				<p><?php esc_html_e('Simple Telegram isn\'t active. It seems that your bot token is not correct.', 'simple-telegram'); ?> ¯\_(ツ)_/¯</p>
			</div>
		<?php else: ?>
			<div class="st-infobox st-active">
				<?php
					$nt = new Notifcaster_Class();
					$nt->_telegram( get_option('simpleTelegram_botToken') );
					$result = $nt->get_bot();
				?>
				<p><?php esc_html_e('Simple Telegram is active.', 'simple-telegram'); ?> <strong>\o/</strong> <?php esc_html_e('Bot info', 'simple-telegram'); ?>: <?php echo( $result['result']['first_name'] ); ?> (@<?php echo( $result['result']['username'] ); ?>)</p>
			</div>
		<?php endif; ?>
		
		<table class="form-table">
		
			<tr valign="top">
				<th scope="row"><label for="simpleTelegram_botToken"><?php esc_html_e('Bot token', 'simple-telegram'); ?>:</label></th>
				<td>
					<input type="text" style="width: 400px;" name="simpleTelegram_botToken" id="simpleTelegram_botToken" value="<?php echo( get_option('simpleTelegram_botToken') ); ?>" /> <label for="simpleTelegram_botToken"><?php esc_html_e('Your admin bot for your telegram channel', 'simple-telegram'); ?> (<a target="_blank" href="<?php echo( simpleTelegram_helpLink() ); ?>"><?php esc_html_e('Need help?', 'simple-telegram'); ?></a>)</label>
				</td>
			</tr>
		
			<tr valign="top">
				<th scope="row"><label for="simpleTelegram_channelName"><?php esc_html_e('Channel Username', 'simple-telegram'); ?>:</label></th>
				<td>
					<input type="text" style="width: 200px;" name="simpleTelegram_channelName" id="simpleTelegram_channelName" value="<?php echo( get_option('simpleTelegram_channelName') ); ?>" /> <label for="simpleTelegram_channelName"><?php esc_html_e('The name of your Telegram channel', 'simple-telegram'); ?> <?php if ( '' != get_option('simpleTelegram_channelName') ): ?>(<a href="http://telegram.me/<?php echo( str_replace( '@', '', get_option('simpleTelegram_channelName') ) ); ?>"><?php echo( get_option('simpleTelegram_channelName') ); ?></a>)<?php endif; ?></label>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="simpleTelegram_autoPost"><?php esc_html_e('Default for new posts', 'simple-telegram'); ?>:</label></th>
				<td>
					<label for="simpleTelegram_autoPost">
						<input type="checkbox" name="simpleTelegram_autoPost" id="simpleTelegram_autoPost" value="1" <?php checked( get_option('simpleTelegram_autoPost') ); ?>/>
						<?php esc_html_e('Send new posts automatically to your Telegram channel (checkbox will be checked by default)', 'simple-telegram'); ?>
					</label>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="simpleTelegram_messageStyle"><?php esc_html_e('Message style', 'simple-telegram'); ?></label></th>
				<td colspan="7">
					<select name="simpleTelegram_messageStyle" id="simpleTelegram_messageStyle" class="postform" style="min-width:220px;">
					   <option class="level-0" value="plaintext" <?php selected( get_option('simpleTelegram_messageStyle'), 'plaintext' ); ?>><?php esc_html_e('Plain text only', 'simple-telegram'); ?></option> 
					   <option class="level-0" value="webpreview" <?php selected( get_option('simpleTelegram_messageStyle'), 'webpreview' ); ?>><?php esc_html_e('Show web preview', 'simple-telegram'); ?> (<?php esc_html_e('Default', 'simple-telegram'); ?>)</option>
					   <option class="level-0" value="featuredimage" <?php selected( get_option('simpleTelegram_messageStyle'), 'featuredimage' ); ?>><?php esc_html_e('Show featured image', 'simple-telegram'); ?></option>
					</select>
					<label for="simpleTelegram_messageStyle"><?php esc_html_e('Style options for the Telegram messages', 'simple-telegram'); ?></label>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="simpleTelegram_markUpdate"><?php esc_html_e('Mark updated posts', 'simple-telegram'); ?>:</label></th>
				<td>
					<label for="simpleTelegram_markUpdate">
						<input type="checkbox" name="simpleTelegram_markUpdate" id="simpleTelegram_markUpdate" value="1" <?php checked( get_option('simpleTelegram_markUpdate') ); ?>/>
						<?php esc_html_e('Mark re-sent posts with "UPDATE" (if you sent updated posts manually)', 'simple-telegram'); ?>
					</label>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="simpleTelegram_messageTemplate"><?php esc_html_e('Message template', 'simple-telegram'); ?>:</label></th>
				<td>
					<table>
						<tr>
							<td style="padding-top:0; padding-left: 0;">
								<textarea name="simpleTelegram_messageTemplate" id="simpleTelegram_messageTemplate" style="width:400px; height:210px;"><?php echo( get_option('simpleTelegram_messageTemplate') ); ?></textarea>
							</td>
							<td style="padding-top:0;">
								<p style="margin-top:0 !important;"><strong><?php esc_html_e('Supported variables', 'simple-telegram'); ?></strong></p>
								<p>{TITLE} <strong>=></strong> <i><?php esc_html_e('The title of the post', 'simple-telegram'); ?></i></p>
								<p>{FULLURL} <strong>=></strong> <i><?php esc_html_e('The full url of the post', 'simple-telegram'); ?></i></p>
								<p>{SHORTURL} <strong>=></strong> <i><?php esc_html_e('The short url of the post (wp.me)', 'simple-telegram'); ?></i></p>
								<p>{EXCERPT} <strong>=></strong> <i><?php esc_html_e('The excerpt of the post (60 words)', 'simple-telegram'); ?></i></p>
								<p>{TAGS} <strong>=></strong> <i><?php esc_html_e('Hashtagged list with the tags of the post', 'simple-telegram'); ?></i></p>
								<p>{CATEGORIES} <strong>=></strong> <i><?php esc_html_e('Comma-seperated list with the categories of the post', 'simple-telegram'); ?></i></p>
								<p>
									<strong><?php esc_html_e('Supported Markdown', 'simple-telegram'); ?>:</strong> <i>**<?php esc_html_e('italic', 'simple-telegram'); ?>**</i> | <u><?php esc_html_e('underline', 'simple-telegram'); ?></u> | <strong>*<?php esc_html_e('bold', 'simple-telegram'); ?>*</strong>
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
		</table>
		
		<p class="submit" style="clear: both;">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_html_e('Save changes', 'simple-telegram'); ?>" />
			<input type="hidden" name="simple-telegram-settings-submit" value="Y" />
		</p>
		
	</form>
	</div>
<?php } ?>