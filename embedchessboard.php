<?php

/*
Plugin Name: Embed Chessboard
Plugin URI: http://wordpress.org/extend/plugins/embed-chessboard/
Description: Embeds a javascript chessboard in wordpress articles for replaying chess games. Use plugin options to blend the chessboard with the site template; use tag parameters to customize each chessboard. Insert chess games in PGN format into your wordpress article using the syntax: <code>[pgn parameter=value ...] e4 e6 d4 d5 [/pgn]</code>. For more info on plugin options and tag parameters please <a href="http://code.google.com/p/pgn4web/wiki/User_Notes_wordpress">read the tutorial</a>.
Version: 1.39
Author: Paolo Casaschi
Author URI: http://pgn4web.casaschi.net
Copyright: copyright (C) 2009, 2011 Paolo Casaschi

ChangeLog:
  1.00  - initial release, based on pgn4web version 1.88 and 
          on the Embed Iframe plugin of Deskera (http://deskera.com)
  1.01  - minor modifications for hosting on wordpress.org/extend/plugins
  1.02  - fixing the "Cannot modify header information" warning
  1.03  - properly detecting wordpress address URI
  1.04  - minor fix
  1.05  - major rewrite simplyfying the plugin core (replacing the Embed Iframe template 
          with a template from the bbcode plugin of Viper007Bond http://www.viper007bond.com/)
          added the option to configure chessboard colors (see settings submenu)
  1.06  - minor fix
  1.07  - changed settings names (you might need to enter your custom config again)
  1.08  - added option for controlling autoplay of games at load
  1.09  - added options to the pgn tag [pgn parameter=value ...] ... [/pgn]
          and upgraded pgn4web to 1.89
  1.10  - added tutorial info on the admin page
  1.11  - added advanced option with the CSS style for the HTML DIV container of the plugin frame
  1.12  - added admin option and tag parameter to set horizontal/vertical layout
  1.13  - bug fixes and upgraded pgn4web to 1.92
  1.14  - bug fixes
  1.15  - more bug fixes and upgraded pgn4web to 1.93
  1.16  - upgraded pgn4web to 1.94 with search tool addition 
  1.17  - minor bug fix
  1.18  - upgraded pgn4web to 1.95 and minor bug fix
  1.19  - upgraded pgn4web to 1.96 and minor bug fix
  1.20  - upgraded pgn4web to 1.97
  1.21  - upgraded pgn4web to 1.98
  1.22  - upgraded pgn4web to 2.02 with improved PGN error handling
  1.23  - upgraded pgn4web to 2.03
  1.24  - upgraded pgn4web to 2.04
  1.25  - minor bug fix
  1.26  - added rawurlencode() to url parameters and upgraded pgn4web to 2.05
  1.27  - added extendedOptions switch to the [pgn] tag and upgraded pgn4web to 2.06
  1.28  - upgraded pgn4web to 2.07, inlcuding Chess960 support
  1.29  - upgraded pgn4web to 2.08, fixing a bug in the square highlight code
  1.30  - upgraded pgn4web to 2.09, fixing a bug with IE
  1.31  - upgraded pgn4web to 2.10 and minor bug fix
  1.32  - upgraded pgn4web to 2.11 and minor bug fix
  1.33  - upgraded pgn4web to 2.12
  1.34  - enhanced frame height management and upgraded pgn4web to 2.13
  1.35  - upgraded pgn4web to 2.14
  1.36  - upgraded pgn4web to 2.15
  1.37  - upgraded pgn4web to 2.16
  1.38  - upgraded pgn4web to 2.17
  1.39  - updated compatibility flag from 3.0 to 3 and upgraded pgn4web to 2.17+
*/

class pgnBBCode {

	// Plugin initialization
	function pgnBBCode() {
		// This version only supports WP 2.5+ (learn to upgrade please!)
		if ( !function_exists('add_shortcode') ) return;

		// Register the shortcodes
		add_shortcode( 'pgn' , array(&$this, 'shortcode_pgn') );
	}

	// pgn shortcode
	function shortcode_pgn( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		// [pgn height=auto showMoves=justified initialGame=1 initialHalfmove=0 autoplayMode=loop] e4 e6 d4 d5 [/pgn]

		$pgnText = preg_replace("@(<.*?>|\n)@", " ", $content);

		if ( isset($atts['layout']) ) { $layout = $atts['layout']; }
		elseif ( isset($atts['l']) ) { $layout = $atts['l']; }
		if ( isset($atts['layout']) || isset($atts['l'])) {
			if (($layout == "horizontal") || ($layout == "h")) { $horizontalLayout = "t"; }
			elseif (($layout == "vertical") || ($layout == "v")) { $horizontalLayout = "f"; }
			else { $horizontalLayout = "f"; }
		} else { 
			$horizontalLayout = get_option_with_default('embedchessboard_horizontal_layout');
		}

		if ( isset($atts['showmoves']) ) { $movesDisplay = $atts['showmoves']; }
		elseif ( isset($atts['sm']) ) { $movesDisplay = $atts['sm']; }
		else { $movesDisplay = 'j'; }

		if ( isset($atts['height']) ) { $height = $atts['height']; }
		elseif ( isset($atts['h']) ) { $height = $atts['h']; } 
		elseif ( isset($atts[0]) ) { $height = $atts[0]; } // compatibility with v < 1.09
		else { $height = get_option_with_default('embedchessboard_height'); }

		if (($height == "auto") || (strlen($height) == 0)) {
			$height = 268; // 26*8 squares + 3*2 border + 13*2 padding + 28 buttons
			// guessing if one game or multiple games are supplied
			$multiGamesRegexp = '/\s*\[\s*\w+\s*"[^"]*"\s*\]\s*[^\s\[\]]+[\s\S]*\[\s*\w+\s*"[^"]*"\s*\]\s*/';
			if (preg_match($multiGamesRegexp, $pgnText) > 0) { $height += 34; }
			if ($horizontalLayout == "t") {
				$frameHeight = "b";
			} else {
				$height += 75; // header
				if (($movesDisplay != 'hidden') && ($movesDisplay != 'h')) { $height += 300; } // moves
				$frameHeight = $height;
			}
		} else {
			$frameHeight = $height;
		}

		if ( isset($atts['initialgame']) ) { $initialGame = $atts['initialgame']; }
		elseif ( isset($atts['ig']) ) { $initialGame = $atts['ig']; }
		else { $initialGame = 'f'; }

		if ( isset($atts['initialhalfmove']) ) { $initialHalfmove = $atts['initialhalfmove']; }
		elseif ( isset($atts['ih']) ) { $initialHalfmove = $atts['ih']; }
		else { $initialHalfmove = 's'; }

		if ( isset($atts['autoplaymode']) ) { $autoplayMode = $atts['autoplaymode']; }
		elseif ( isset($atts['am']) ) { $autoplayMode = $atts['am']; }
		else { $autoplayMode = get_option_with_default('embedchessboard_autoplay_mode'); }

		$pgnSourceOverride = false;
		$extendedOptionsString = '';
		if ( isset($atts['extendedoptions']) ) { $extendedOptions = $atts['extendedoptions']; }
		elseif ( isset($atts['eo']) ) { $extendedOptions = htmlspecialchars($atts['eo']); }
		else { $extendedOptions = 'false'; }
		if (($extendedOptions == 'true') || ($extendedOptions == 't')) {
			$skipParameters = array('layout', 'l', 'showmoves', 'sm', 'height', 'h', 'initialgame', 'ig', 'initialhalfmove', 'ih', 'autoplaymode', 'am', 'extendedoptions', 'eo');
			$pgnParameters = array('pgntext', 'pt', 'pgnencoded', 'pe', 'fenstring', 'fs', 'pgnid', 'pi', 'pgndata', 'pd');
			foreach ($atts as $key => $value) {
				if (in_array(strtolower($key), $skipParameters)) { continue; }
				if (in_array(strtolower($key), $pgnParameters)) { $pgnSourceOverride = true;  }
				$extendedOptionsString .= '&amp;' . rawurlencode($key) . '=' . rawurlencode($value);
			}
		}

		$pgnId = "pgn4web_" . dechex(crc32($pgnText));

		$containerStyle = get_option_with_default('embedchessboard_container_style');
		if ($containerStyle == '') { $replacement  = "<div class='chessboard-wrapper'>"; }
		else { $replacement  = "<div style='" . $containerStyle . "' class='chessboard-wrapper'>"; }
		
		$replacement .= "<textarea id='" . $pgnId . "' style='display:none;' cols='40' rows='8'>";
		$replacement .= $pgnText;
		$replacement .= "</textarea>";
		$replacement .= "<iframe src='" . plugins_url("pgn4web/board.html", __FILE__) . "?";
		$replacement .= "am=" . rawurlencode($autoplayMode);
		$replacement .= "&amp;d=3000";
		$replacement .= "&amp;ig=" . rawurlencode($initialGame);
		$replacement .= "&amp;ih=" . rawurlencode($initialHalfmove);
		$replacement .= "&amp;ss=26&amp;ps=d&amp;pf=d";
		$replacement .= "&amp;lch=" . rawurlencode(get_option_with_default('embedchessboard_light_squares_color'));
		$replacement .= "&amp;dch=" . rawurlencode(get_option_with_default('embedchessboard_dark_squares_color'));
		$replacement .= "&amp;bbch=" . rawurlencode(get_option_with_default('embedchessboard_board_border_color'));
		$replacement .= "&amp;hm=b";
		$replacement .= "&amp;hch=" . rawurlencode(get_option_with_default('embedchessboard_square_highlight_color'));
		$replacement .= "&amp;bd=c";
		$replacement .= "&amp;cbch=" . rawurlencode(get_option_with_default('embedchessboard_control_buttons_background_color'));
		$replacement .= "&amp;ctch=" . rawurlencode(get_option_with_default('embedchessboard_control_buttons_text_color'));
		$replacement .= "&amp;hd=j";
		$replacement .= "&amp;md=" . rawurlencode($movesDisplay);
		$replacement .= "&amp;tm=13";
		$replacement .= "&amp;fhch=" . rawurlencode(get_option_with_default('embedchessboard_header_text_color'));
		$replacement .= "&amp;fhs=80p";
		$replacement .= "&amp;fmch=" . rawurlencode(get_option_with_default('embedchessboard_moves_text_color'));
		$replacement .= "&amp;fcch=" . rawurlencode(get_option_with_default('embedchessboard_comments_text_color'));
		$replacement .= "&amp;hmch=" . rawurlencode(get_option_with_default('embedchessboard_move_highlight_color'));
		$replacement .= "&amp;fms=80p&amp;fcs=m&amp;cd=i";
		$replacement .= "&amp;bch=" . rawurlencode(get_option_with_default('embedchessboard_background_color'));
		$replacement .= "&amp;fp=13";
		$replacement .= "&amp;hl=" . rawurlencode($horizontalLayout);
		$replacement .= "&amp;fh=" . rawurlencode($frameHeight);
		$replacement .= "&amp;fw=p";
		if (!$pgnSourceOverride) { $replacement .= "&amp;pi=" . rawurlencode($pgnId); }
		$replacement .= $extendedOptionsString . "' ";
		$replacement .= "frameborder='0' width='100%' height='" . $height . "' ";
		$replacement .= "scrolling='no' marginheight='0' marginwidth='0'>";
		$replacement .= "your web browser and/or your host do not support iframes as required to display the chessboard; alternatively your wordpress theme might suppress the html iframe tag from articles or excerpts";
		$replacement .= "</iframe>";
		$replacement .= "</div>";

		return $replacement;

	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $pgnBBCode; $pgnBBCode = new pgnBBCode();' ) );

// create custom plugin settings menu
add_action('admin_menu', 'embedchessboard_create_menu');

function embedchessboard_create_menu() {

	//create new sub-level menu from the settings menu
	add_submenu_page('options-general.php', 'Embed Chessboard Plugin Settings', 'Embed Chessboard', 'administrator', __FILE__, 'embedchessboard_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
	//register our settings
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_horizontal_layout' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_height' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_background_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_light_squares_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_dark_squares_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_board_border_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_square_highlight_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_control_buttons_background_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_control_buttons_text_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_header_text_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_moves_text_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_move_highlight_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_comments_text_color' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_autoplay_mode' );
	register_setting( 'embedchessboard-settings-group', 'embedchessboard_container_style' );
}

function get_option_with_default($optionName) {
	$retVal = get_option($optionName);
	
	if (strlen(trim($retVal)) == 0) {
		switch ($optionName) {
			case 'embedchessboard_horizontal_layout':
				$retVal = 'f';
				break;
			case 'embedchessboard_height':
				$retVal = 'auto';
				break;
			case 'embedchessboard_background_color':
				$retVal = 'FFFFFF';
				break;
			case 'embedchessboard_light_squares_color':
				$retVal = 'F6F6F6';
				break;
			case 'embedchessboard_dark_squares_color':
				$retVal = 'E0E0E0';
				break;
			case 'embedchessboard_board_border_color':
				$retVal = 'E0E0E0';
				break;
			case 'embedchessboard_square_highlight_color':
				$retVal = 'ABABAB';
				break;
			case 'embedchessboard_control_buttons_background_color':
				$retVal = 'F0F0F0';
				break;
			case 'embedchessboard_control_buttons_text_color':
				$retVal = '696969';
				break;
			case 'embedchessboard_header_text_color':
				$retVal = '000000';
				break;
			case 'embedchessboard_moves_text_color':
				$retVal = '000000';
				break;
			case 'embedchessboard_move_highlight_color':
				$retVal = 'E0E0E0';
				break;
			case 'embedchessboard_comments_text_color':
				$retVal = '808080';
				break;
			case 'embedchessboard_autoplay_mode':
				$retVal = 'l';
				break;
			case 'embedchessboard_container_style':
				$retVal = '';
				break;
			default:
				$retVal = '';
				break;
		}
	}
	return $retVal;
}

function embedchessboard_settings_page() {
?>
<div class="wrap">
<h2>Embed Chessboard Plugin Settings</h2>
<small>
<a href="http://code.google.com/p/pgn4web/wiki/User_Notes_wordpress" target="_blank">read the tutorial</a> for more details about this plugin
<br>
leave blank values to reset to defaults
</small>

<script type="text/javascript" src="<?php echo plugins_url('pgn4web/jscolor/jscolor.js', __FILE__) ?>"></script>

<form method="post" action="options.php">
	<?php settings_fields( 'embedchessboard-settings-group' ); ?>
	<table class="form-table">

		<tr><td colspan=3><h3>Layout</h3></td></tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_horizontal_layout">chessboard frame layout</label></th>
		<td colspan=2>
			<select name="embedchessboard_horizontal_layout">
			<option <?php if ("t" == get_option_with_default('embedchessboard_horizontal_layout')) echo "selected" ?> value="t">horizontal layout</option>
			<option <?php if ("f" == get_option_with_default('embedchessboard_horizontal_layout')) echo "selected" ?> value="f">vertical layout</option>
			</select>
		</td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_height">chessboard frame height</label></th>
		<td><input type="text" name="embedchessboard_height" value="<?php echo get_option_with_default('embedchessboard_height'); ?>" /></td>
		<td><small>normally set to <b>auto</b>, it can be set to a number to assign the chessboard frame height</small></td>
		</tr>

		<tr><td colspan=3></td></tr>

		<tr><td colspan=3><h3>Colors</h3></td></tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_background_color">background color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_background_color" value="<?php echo get_option_with_default('embedchessboard_background_color'); ?>" /></td>
		<td></td>
		</tr>
        
		<tr><td colspan=3></td></tr>
		
		<tr valign="top">
		<th scope="row"><label for="embedchessboard_light_squares_color">light squares color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_light_squares_color" value="<?php echo get_option_with_default('embedchessboard_light_squares_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_dark_squares_color">dark squares color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_dark_squares_color" value="<?php echo get_option_with_default('embedchessboard_dark_squares_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_board_border_color">board border color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_board_border_color" value="<?php echo get_option_with_default('embedchessboard_board_border_color'); ?>" /></td>
		<td></td>
		</tr>
	
		<tr valign="top">
		<th scope="row"><label for="embedchessboard_square_highlight_color">square highlight color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_square_highlight_color" value="<?php echo get_option_with_default('embedchessboard_square_highlight_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr><td colspan=3></td></tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_control_buttons_background_color">buttons background color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_control_buttons_background_color" value="<?php echo get_option_with_default('embedchessboard_control_buttons_background_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_control_buttons_text_color">buttons text color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_control_buttons_text_color" value="<?php echo get_option_with_default('embedchessboard_control_buttons_text_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr><td colspan=3></td></tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_header_text_color">header text color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_header_text_color" value="<?php echo get_option_with_default('embedchessboard_header_text_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_moves_text_color">moves text color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_moves_text_color" value="<?php echo get_option_with_default('embedchessboard_moves_text_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_move_highlight_color">move highlight color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_move_highlight_color" value="<?php echo get_option_with_default('embedchessboard_move_highlight_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_comments_text_color">comments text color</label></th>
		<td><input class="color {required:false}" type="text" name="embedchessboard_comments_text_color" value="<?php echo get_option_with_default('embedchessboard_comments_text_color'); ?>" /></td>
		<td></td>
		</tr>

		<tr><td colspan=3></td></tr>
	
		<tr><td colspan=3><h3>Autoplay Mode</h3></td></tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_autoplay_mode">autoplay mode</label></th>
		<td colspan=2>
			<select name="embedchessboard_autoplay_mode">
			<option <?php if ("g" == get_option_with_default('embedchessboard_autoplay_mode')) echo "selected" ?> value="g">autoplay the initial game only</option>
			<option <?php if ("l" == get_option_with_default('embedchessboard_autoplay_mode')) echo "selected" ?> value="l">autoplay all games in a loop</option>
			<option <?php if ("n" == get_option_with_default('embedchessboard_autoplay_mode')) echo "selected" ?> value="n">do not autoplay games</option>
			</select>
		</td>
		</tr>

		<tr><td colspan=3></td></tr>

		<tr><td colspan=3><h3>Advanced Settings</h3></td></tr>

		<tr valign="top">
		<th scope="row"><label for="embedchessboard_container_style">CSS style for the HTML DIV container of the plugin frame</label></th>
		<td><input type="text" name="embedchessboard_container_style" value="<?php echo get_option_with_default('embedchessboard_container_style'); ?>" /></td>
		<td><small>normally left blank, it can be used to fix layout issues with certain wordpress templates; for instance, if the chessboard frame is constraint too narrow, setting this parameter as <b>width:500px;</b> might improve the layout</small></td>
		</tr>

		<tr><td colspan=3></td></tr>

	</table>
    
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>

</form>
</div>
<?php } ?>
