<?php

/*
Plugin Name: Embed Chessboard
Plugin URI: http://wordpress.org/extend/plugins/embed-chessboard/
Description: Allows for the graphical display of chess games from the games score in PGN format (see settings submenu for administrator plugin settings). The basic tag is: <code>[pgn] 1. e4 e6 2. d4 d5 [/pgn]</code> Optionally you can add a parameter to the pgn tag, specifying the height of the chessboard widget (default is 600), for example: <code>[pgn 325] 1. e4 e6 2. d4 d5 [/pgn]</code>
Version: 1.05
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
*/

class pgnBBCode {

	// Plugin initialization
	function pgnBBCode() {
		// This version only supports WP 2.5+ (learn to upgrade please!)
		if ( !function_exists('add_shortcode') ) return;

		// Register the shortcodes
		add_shortcode( 'pgn' , array(&$this, 'shortcode_pgn') );
	}

	// No-name attribute fixing
	function attributefix( $atts = array() ) {
		if ( empty($atts[0]) ) return $atts;

		if ( 0 !== preg_match( '#=("|\')(.*?)("|\')#', $atts[0], $match ) )
			$atts[0] = $match[2];

		return $atts;
	}

	// pgn shortcode
	function shortcode_pgn( $atts = array(), $content = NULL ) {
		if ( NULL === $content ) return '';

		$atts = $this->attributefix( $atts );

		// [pgn height] e4 e6 d4 d5 [/pgn]

		if ( isset($atts[0]) ) {
			$height = $atts[0];
		} else {
			$height = 600;
		}
		$pgnText = preg_replace("@<.*?>@", "", $content);
		$pgnId = dechex(crc32($pgnText));

		$replacement  = "<div class='chessboard-wrapper'> ";
            $replacement .= "<textarea id='" . $pgnId . "' style='display:none;'> ";
            $replacement .= $pgnText;
            $replacement .= " </textarea> ";
            $replacement .= " <iframe src=" . plugins_url('pgn4web/board.html', __FILE__) . "?";
            $replacement .= "am=l&d=3000&ss=26&ps=d&pf=d";
            $replacement .= "&lch=" . get_option_with_default('light_squares_color');
            $replacement .= "&dch=" . get_option_with_default('dark_squares_color');
            $replacement .= "&bbch=" . get_option_with_default('board_border_color');
            $replacement .= "&hm=b";
            $replacement .= "&hch=" . get_option_with_default('square_highlight_color');
            $replacement .= "&bd=c";
            $replacement .= "&cbch=" . get_option_with_default('control_buttons_background_color');
            $replacement .= "&ctch=" . get_option_with_default('control_buttons_text_color');
            $replacement .= "&hd=j&md=j&tm=13";
            $replacement .= "&fhch=" . get_option_with_default('header_text_color');
            $replacement .= "&fhs=80p";
            $replacement .= "&fmch=" . get_option_with_default('moves_text_color');
            $replacement .= "&fcch=" . get_option_with_default('comments_text_color');
            $replacement .= "&hmch=" . get_option_with_default('move_highlight_color');
            $replacement .= "&fms=80p&fcs=m&cd=i";
            $replacement .= "&bch=" . get_option_with_default('background_color');
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
	register_setting( 'embedchessboard-settings-group', 'background_color' );
	register_setting( 'embedchessboard-settings-group', 'light_squares_color' );
	register_setting( 'embedchessboard-settings-group', 'dark_squares_color' );
	register_setting( 'embedchessboard-settings-group', 'board_border_color' );
	register_setting( 'embedchessboard-settings-group', 'square_highlight_color' );
	register_setting( 'embedchessboard-settings-group', 'control_buttons_background_color' );
	register_setting( 'embedchessboard-settings-group', 'control_buttons_text_color' );
	register_setting( 'embedchessboard-settings-group', 'header_text_color' );
	register_setting( 'embedchessboard-settings-group', 'moves_text_color' );
	register_setting( 'embedchessboard-settings-group', 'move_highlight_color' );
	register_setting( 'embedchessboard-settings-group', 'comments_text_color' );
}

function get_option_with_default($optionName) {
	$retVal = get_option($optionName);
	
	if (strlen(trim($retVal)) == 0) {
		switch ($optionName) {
			case 'background_color':
				$retVal = 'FFFFFF';
				break;
			case 'light_squares_color':
				$retVal = 'F6F6F6';
				break;
			case 'dark_squares_color':
				$retVal = 'E0E0E0';
				break;
			case 'board_border_color':
				$retVal = 'E0E0E0';
				break;
			case 'square_highlight_color':
				$retVal = 'ABABAB';
				break;
			case 'control_buttons_background_color':
				$retVal = 'F0F0F0';
				break;
			case 'control_buttons_text_color':
				$retVal = '696969';
				break;
			case 'header_text_color':
				$retVal = '000000';
				break;
			case 'moves_text_color':
				$retVal = '000000';
				break;
			case 'move_highlight_color':
				$retVal = 'E0E0E0';
				break;
			case 'comments_text_color':
				$retVal = '808080';
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

<p>All color settings must be in hexadecimal format, like FF0000 for red.
<br>
Leave blank to reset to default values.</p>
<p></p>

<script type="text/javascript" src="<?php echo plugins_url('pgn4web/jscolor/jscolor.js', __FILE__) ?>"></script>

<form method="post" action="options.php">
    <?php settings_fields( 'embedchessboard-settings-group' ); ?>
    <table class="form-table">

		<tr valign="top">
        <th scope="row">background color</th>
        <td><input class="color {required:false}" type="text" name="background_color" value="<?php echo get_option_with_default('background_color'); ?>" /></td>
        </tr>
        
		<tr><td><hr></td></tr>
		
        <tr valign="top">
        <th scope="row">light squares color</th>
        <td><input class="color {required:false}" type="text" name="light_squares_color" value="<?php echo get_option_with_default('light_squares_color'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">dark squares color</th>
        <td><input class="color {required:false}" type="text" name="dark_squares_color" value="<?php echo get_option_with_default('dark_squares_color'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">board border color</th>
        <td><input class="color {required:false}" type="text" name="board_border_color" value="<?php echo get_option_with_default('board_border_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">square highlight color</th>
        <td><input class="color {required:false}" type="text" name="square_highlight_color" value="<?php echo get_option_with_default('square_highlight_color'); ?>" /></td>
        </tr>
		
		<tr><td><hr></td></tr>
		
        <tr valign="top">
        <th scope="row">buttons background color</th>
        <td><input class="color {required:false}" type="text" name="control_buttons_background_color" value="<?php echo get_option_with_default('control_buttons_background_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">buttons text color</th>
        <td><input class="color {required:false}" type="text" name="control_buttons_text_color" value="<?php echo get_option_with_default('control_buttons_text_color'); ?>" /></td>
        </tr>
		
		<tr><td><hr></td></tr>
		
        <tr valign="top">
        <th scope="row">header text color</th>
        <td><input class="color {required:false}" type="text" name="header_text_color" value="<?php echo get_option_with_default('header_text_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">moves text color</th>
        <td><input class="color {required:false}" type="text" name="moves_text_color" value="<?php echo get_option_with_default('moves_text_color'); ?>" /></td>
        </tr>
		
        <tr valign="top">
        <th scope="row">move highlight color</th>
        <td><input class="color {required:false}" type="text" name="move_highlight_color" value="<?php echo get_option_with_default('move_highlight_color'); ?>" /></td>
        </tr>
        
		<tr valign="top">
        <th scope="row">comments text color</th>
        <td><input class="color {required:false}" type="text" name="comments_text_color" value="<?php echo get_option_with_default('comments_text_color'); ?>" /></td>
        </tr>

		<tr><td><hr></td></tr>
		
	</table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>