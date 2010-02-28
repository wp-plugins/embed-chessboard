<?php

/*
Plugin Name: Embed Chessboard
Plugin URI: http://wordpress.org/extend/plugins/embed-chessboard/
Description: Allows for the graphical display of chess games from the games score in PGN format (see settings submenu for administrator plugin settings). The basic tag is: <code>[pgn] 1. e4 e6 2. d4 d5 [/pgn]</code> Optionally you can add a parameter to the pgn tag, specifying the height of the chessboard widget (default is 600), for example: <code>[pgn 325] 1. e4 e6 2. d4 d5 [/pgn]</code>
Version: 1.08
Author: Paolo Casaschi
Author URI: http://pgn4web.casaschi.net

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

		// [pgn height=600 movesDisplay=justified initialGame=1 initialHalfmove=0 autoplayMode=loop] e4 e6 d4 d5 [/pgn]

		if ( isset($atts['initialgame']) ) { $initialGame = $atts['initialgame']; }
		elseif ( isset($atts['ig']) ) { $initialGame = $atts['ig']; }
		else { $initialGame = 'f'; }

		if ( isset($atts['initialhalfmove']) ) { $initialHalfmove = $atts['initialhalfmove']; }
		elseif ( isset($atts['ih']) ) { $initialHalfmove = $atts['ih']; }
		else { $initialHalfmove = 's'; }

		if ( isset($atts['autoplaymode']) ) { $autoplayMode = $atts['autoplaymode']; }
		elseif ( isset($atts['am']) ) { $autoplayMode = $atts['am']; }
		else { $autoplayMode = get_option_with_default('embedchessboard_autoplay_mode'); }

		if ( isset($atts['movesdisplay']) ) { $movesDisplay = $atts['movesdisplay']; }
		elseif ( isset($atts['md']) ) { $movesDisplay = $atts['md']; }
		else { $movesDisplay = 'j'; }

		if ( isset($atts['height']) ) { $height = $atts['height']; }
		elseif ( isset($atts['h']) ) { $height = $atts['height']; } 
		elseif ( isset($atts[0]) ) { $height = $atts[0]; } // compatibility with v < 1.09
		else { 
			if (($movesDisplay == 'hidden') || ($movesDisplay == 'h')) $height = 370;
			else $height = 600; 
		} 

		$pgnText = preg_replace("@<.*?>@", "", $content);

		$pgnId = dechex(crc32($pgnText));

		$replacement  = "<div class='chessboard-wrapper'> ";
		$replacement .= "<textarea id='" . $pgnId . "' style='display:none;'> ";
		$replacement .= $pgnText;
		$replacement .= " </textarea> ";
		$replacement .= " <iframe src=" . plugins_url('pgn4web/board.html', __FILE__) . "?";
		$replacement .= "am=" . $autoplayMode;
		$replacement .= "&d=3000";
		$replacement .= "&ig=" . $initialGame;
		$replacement .= "&ih=" . $initialHalfmove;
		$replacement .= "&ss=26&ps=d&pf=d";
		$replacement .= "&lch=" . get_option_with_default('embedchessboard_light_squares_color');
		$replacement .= "&dch=" . get_option_with_default('embedchessboard_dark_squares_color');
		$replacement .= "&bbch=" . get_option_with_default('embedchessboard_board_border_color');
		$replacement .= "&hm=b";
		$replacement .= "&hch=" . get_option_with_default('embedchessboard_square_highlight_color');
		$replacement .= "&bd=c";
		$replacement .= "&cbch=" . get_option_with_default('embedchessboard_control_buttons_background_color');
		$replacement .= "&ctch=" . get_option_with_default('embedchessboard_control_buttons_text_color');
		$replacement .= "&hd=j";
		$replacement .= "&md=" . $movesDisplay;
		$replacement .= "&tm=13";
		$replacement .= "&fhch=" . get_option_with_default('embedchessboard_header_text_color');
		$replacement .= "&fhs=80p";
		$replacement .= "&fmch=" . get_option_with_default('embedchessboard_moves_text_color');
		$replacement .= "&fcch=" . get_option_with_default('embedchessboard_comments_text_color');
		$replacement .= "&hmch=" . get_option_with_default('embedchessboard_move_highlight_color');
		$replacement .= "&fms=80p&fcs=m&cd=i";
		$replacement .= "&bch=" . get_option_with_default('embedchessboard_background_color');
		$replacement .= "&fp=13&hl=f";
		$replacement .= "&fh=" . $height . "&fw=p";
		$replacement .= "&pi=" . $pgnId . " ";
		$replacement .= "frameborder='0' width='100%' height='" . $height . "' ";
		$replacement .= "scrolling='no' marginheight='0' marginwidth='0'>";
		$replacement .= "sorry, you'd need iframe support in your browser";
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
}

function get_option_with_default($optionName) {
	$retVal = get_option($optionName);
	
	if (strlen(trim($retVal)) == 0) {
		switch ($optionName) {
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
leave blank values to reset to defaults

<script type="text/javascript" src="<?php echo plugins_url('pgn4web/jscolor/jscolor.js', __FILE__) ?>"></script>

<form method="post" action="options.php">
    <?php settings_fields( 'embedchessboard-settings-group' ); ?>
    <table class="form-table">

	<tr><td colspan=2><h3>Colors</h3></td></tr>

	<tr valign="top">
        <th scope="row">background color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_background_color" value="<?php echo get_option_with_default('embedchessboard_background_color'); ?>" /></td>
        </tr>
        
	<tr><td></td></tr>
		
        <tr valign="top">
        <th scope="row">light squares color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_light_squares_color" value="<?php echo get_option_with_default('embedchessboard_light_squares_color'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">dark squares color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_dark_squares_color" value="<?php echo get_option_with_default('embedchessboard_dark_squares_color'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">board border color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_board_border_color" value="<?php echo get_option_with_default('embedchessboard_board_border_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">square highlight color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_square_highlight_color" value="<?php echo get_option_with_default('embedchessboard_square_highlight_color'); ?>" /></td>
        </tr>
		
	<tr><td></td></tr>
		
        <tr valign="top">
        <th scope="row">buttons background color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_control_buttons_background_color" value="<?php echo get_option_with_default('embedchessboard_control_buttons_background_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">buttons text color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_control_buttons_text_color" value="<?php echo get_option_with_default('embedchessboard_control_buttons_text_color'); ?>" /></td>
        </tr>
		
	<tr><td></td></tr>
		
        <tr valign="top">
        <th scope="row">header text color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_header_text_color" value="<?php echo get_option_with_default('embedchessboard_header_text_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">moves text color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_moves_text_color" value="<?php echo get_option_with_default('embedchessboard_moves_text_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">move highlight color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_move_highlight_color" value="<?php echo get_option_with_default('embedchessboard_move_highlight_color'); ?>" /></td>
        </tr>
        
	<tr valign="top">
        <th scope="row">comments text color</th>
        <td><input class="color {required:false}" type="text" name="embedchessboard_comments_text_color" value="<?php echo get_option_with_default('embedchessboard_comments_text_color'); ?>" /></td>
        </tr>

	<tr><td colspan=2><h3>Autoplay Mode</h3></td></tr>

	<tr valign="top">
	<th scope="row">autoplay mode</th>
	<td>
	<select name="embedchessboard_autoplay_mode">
	<option <?php if ("g" == get_option_with_default('embedchessboard_autoplay_mode')) echo "selected" ?> value="g">autoplay the initial game only</option>
	<option <?php if ("l" == get_option_with_default('embedchessboard_autoplay_mode')) echo "selected" ?> value="l">autoplay all games in a loop</option>
	<option <?php if ("n" == get_option_with_default('embedchessboard_autoplay_mode')) echo "selected" ?> value="n">do not autoplay games</option>
	</select>
	</td>
	</tr>

	<tr><td></td></tr>
		
	</table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>
