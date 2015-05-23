<?php
/*
 * Exit if not loaded through WordPress
 */
if ( ! defined( 'ABSPATH' ) ) {	exit; } 

/**
 * Add the administrative settings page to the "Settings" menu.
 */
add_action( 'admin_menu', 'id24_settings' );
function id24_settings() {
	if ( function_exists( 'add_options_page' ) ) {
		$plugin_page = add_options_page( 'Accessible Social Sharing', 'Accessible Social Sharing', 'manage_options', __FILE__, 'id24_settings_page' );
		add_action( 'admin_head-'. $plugin_page, 'id24_admin_styles' );		
	}
}

function id24_admin_styles() {
	wp_enqueue_style( 'id24.admin', plugins_url( 'id24/css/admin.css' ) );
}

/**
 * Display settings page
 */

function id24_settings_page() {
	?>
	<div class='wrap mcm-settings'>
	<div id="icon-index" class="icon32"><br /></div>
	<h2><?php _e( 'Accessible Social Sharing', 'id24-social-sharing' ); ?></h2>
	<div class="postbox-container" style="width: 70%">
	
		<div class="metabox-holder">
		<div class="mcm-settings ui-sortable meta-box-sortables">   
		<div class="postbox" id="get-support">
		<h3><?php _e( 'Settings', 'id24-social-sharing' ); ?></h3>
			<div class="inside">
			<?php id24_settings_fields(); ?>
			</div>
		</div>
		</div>
		</div>
		
		<div class="metabox-holder">
		<div class="mcm-settings ui-sortable meta-box-sortables">   
		<div class="postbox" id="get-support">
		<h3><?php _e( 'Get Plug-in Support', 'id24-social-sharing' ); ?></h3>
			<div class="inside">
			<?php id24_get_support_form(); ?>
			</div>
		</div>
		</div>
		</div>		
		
	</div>
	<div class="postbox-container" style="width: 20%">
		<div class="metabox-holder">
		<div class="mcm-settings ui-sortable meta-box-sortables">   
		<div class="mcm-template-guide postbox" id="get-support">
		<h3><?php _e('Support this plug-in', 'id24-social-sharing'); ?></h3>
			<div class="inside">
				<?php id24_show_support_box(); ?>		
			</div>
		</div>
		</div>
		</div>
	</div>
</div>
<?php
}

function id24_settings_fields() {
	$settings = get_option( 'id24_settings' );
	$available = apply_filters( 'id24_social_services', array( 'twitter', 'facebook', 'google', 'pinterest'	) );	
	
	if ( is_array( $settings ) ) {
		$enabled = $settings['enabled'];
	} else {
		$enabled = array();
	}
	if ( isset( $_POST['id24_update'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'id24-social-sharing-nonce' ) ) { wp_die( __( 'Invalid request', 'id24-social-sharing' ) );	}
		$enabled = $_POST['id24_enabled'];
		foreach ( $enabled as $value ) {
			$new_enabled[$value] = 'on';
		}
		$settings['enabled'] = $new_enabled;
		update_option( 'id24_settings', $settings );
		echo "<div class='notice updated'><p>" . __( 'Social Sharing Services Updated', 'id24-social-sharing' ) . "</p></div>";
	}
		
	$fields = '';
	foreach( $available as $value ) {
		if ( is_array( $enabled ) ) {
			$checked = ( in_array( $value, array_keys( $enabled ) ) ) ? ' checked="checked"' : '';
		} else {
			$checked = '';
		}
		$fields .= "<li><input type='checkbox' name='id24_enabled[]' value='" . esc_attr( $value ) . "' id='id24_enabled_" . esc_attr( $value ) . "' $checked /> <label for='id24_enabled_" . esc_attr( $value ) . "'>" . ucfirst( esc_html( $value ) ) . "</label></li>";
	}
	$form = "
		<form method='post' action='" . admin_url( 'options-general.php?page=id24/inc/settings.php' )."'>
			<div><input type='hidden' name='_wpnonce' value='" . wp_create_nonce( 'id24-social-sharing-nonce' ) . "' /></div>
			<fieldset>
				<legend>" . __( 'Social Sharing Services', 'id24-social-sharing' ) . "</legend>
				<ul>
					$fields
				</ul>
			</fieldset>
			<p>
				<input type='submit' class='button-primary' name='id24_update' value='" . __( 'Save Settings', 'id24-social-sharing' ) . "' />
			</p>
		</form>";
	echo $form;

}

function id24_show_support_box() {
?>
	<div id="support">
		<div class="resources">
		<p>
		<a href="https://twitter.com/intent/follow?screen_name=joedolson" class="twitter-follow-button" data-size="small" data-related="joedolson">Follow @joedolson</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</p>		
		<ul>
			<li><strong><a href="#get-support" rel="external"><?php _e( "Get Support", 'id24-social-sharing' ); ?></a></strong></li>			
		</ul>
		</div>
	</div>
<?php
}


/**
 * Generates and handles submission of plugin support form.
 */
function id24_get_support_form() {
	global $current_user, $id24_version;
	get_currentuserinfo();
	// send fields for DisabilityInfo
	$version = $id24_version;
	// send fields for all plugins
	$wp_version = get_bloginfo('version');
	$home_url = home_url();
	$wp_url = site_url();
	$language = get_bloginfo('language');
	$charset = get_bloginfo('charset');
	// server
	$php_version = phpversion();

	// theme data
	$theme = wp_get_theme();
	$theme_name = $theme->Name;
	$theme_uri = $theme->ThemeURI;
	$theme_parent = $theme->Template;
	$theme_version = $theme->Version;	

	// plugin data
	$plugins = get_plugins();
	$plugins_string = '';

		foreach( array_keys($plugins) as $key ) {
			if ( is_plugin_active( $key ) ) {
				$plugin =& $plugins[$key];
				$plugin_name = $plugin['Name'];
				$plugin_uri = $plugin['PluginURI'];
				$plugin_version = $plugin['Version'];
				$plugins_string .= "$plugin_name: $plugin_version; $plugin_uri\n";
			}
		}
	$data = "
================ Installation Data ====================
==Accessible Social Sharing:==
Version: $version

==WordPress:==
Version: $wp_version
URL: $home_url
Install: $wp_url
Language: $language
Charset: $charset
Admin Email: $current_user->user_email

==Extra info:==
PHP Version: $php_version
Server Software: $_SERVER[SERVER_SOFTWARE]
User Agent: $_SERVER[HTTP_USER_AGENT]

==Theme:==
Name: $theme_name
URI: $theme_uri
Parent: $theme_parent
Version: $theme_version

==Active Plugins:==
$plugins_string
";
	$request = '';
	if ( isset($_POST['mc_support']) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce($nonce,'id24-social-sharing-nonce') ) die( "Security check failed" );	
		$request = stripslashes($_POST['support_request']);
		$subject = "Accessible Social Sharing support request.";
		$message = $request ."\n\n". $data;
		// Get the site domain and get rid of www. from pluggable.php
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
		}
		$from_email = 'wordpress@' . $sitename;		
		$from = "From: \"$current_user->display_name\" <$from_email>\r\nReply-to: \"$current_user->display_name\" <$current_user->user_email>\r\n";

		if ( !$has_read_faq ) {
			echo "<div class='message error'><p>".__('Please read the FAQ and other Help documents before making a support request.','id24-social-sharing' )."</p></div>";
		} else {
			wp_mail( "plugin@joedolson.com",$subject,$message,$from );
			echo "<div class='message updated'><p>".__('I\'ll get back to you as soon as I can.','id24-social-sharing' )."</p></div>";
		}
	}
	
	echo "
	<form method='post' action='".admin_url('options-general.php?page=id24/inc/settings.php')."'>
		<div><input type='hidden' name='_wpnonce' value='".wp_create_nonce('id24-social-sharing-nonce')."' /></div>
		<div>
		<p>
		<code>".__('From:','id24-social-sharing')." \"$current_user->display_name\" &lt;$current_user->user_email&gt;</code> &larr; ".__('Can\'t get email at this address? Provide a different one below.','disabilityinfo')."
		</p>		
		<p>
		<label for='support_request'>".__('Support Request:','id24-social-sharing')."</label><br /><textarea name='support_request' required aria-required='true' id='support_request' cols='80' rows='10'>".stripslashes($request)."</textarea>
		</p>
		<p>
		<input type='submit' value='".__('Send Support Request','id24-social-sharing' )."' name='mc_support' class='button-primary' />
		</p>
		<p>".
		__('The following additional information will be sent with your support request:','id24-social-sharing' )
		."</p>
	</form>
		<div class='mc_support'>
		".wpautop($data)."
		</div>
		</div>";
}

