<?php

/*
Plugin Name: Embed Chessboard
Plugin URI: http://wordpress.org/extend/plugins/embed-chessboard/
Description: Allows for the graphical display of chess games from the games score in PGN format. The basic tag is: <code>[pgn] 1. e4 e6 2. d4 d5 [/pgn]</code> Optionally you can add a parameter to the pgn tag, specifying the height of the chessboard widget (default is 600), for example: <code>[pgn 325] 1. e4 e6 2. d4 d5 [/pgn]</code>
Version: 1.04
Author: Paolo Casaschi
Author URI: http://pgn4web.casaschi.net

ChangeLog:
  1.00  - initial release, based on pgn4web version 1.88 and 
          on the Embed Iframe plugin of Deskera (http://deskera.com)
  1.01  - minor modifications for hosting on wordpress.org/extend/plugins
  1.02  - fixing the "Cannot modify header information" warning
  1.03  - properly detecting wordpress address URI
  1.04  - minor fix
*/


class EmbedChessboard_Plugin
{
	/**
	 * Plugin name
	 * @var string
	 **/
	var $plugin_name;
	
	/**
	 * Plugin 'view' directory
	 * @var string Directory
	 **/
	var $plugin_base;
	
	
	/**
	 * Register your plugin with a name and base directory.  This <strong>must</strong> be called once.
	 *
	 * @param string $name Name of your plugin.  Is used to determine the plugin locale domain
	 * @param string $base Directory containing the plugin's 'view' files.
	 * @return void
	 **/
	
	function register_plugin ($name, $base)
	{
		$this->plugin_base = rtrim (dirname ($base), '/');
		$this->plugin_name = $name;

		$this->add_action ('init', 'load_locale');
	}
	
	function load_locale ()
	{
		// Here we manually fudge the plugin locale as WP doesnt allow many options
		$locale = get_locale ();
		if ( empty($locale) )
			$locale = 'en_US';

		$mofile = dirname (__FILE__)."/locale/$locale.mo";
		load_textdomain ($this->plugin_name, $mofile);
	}
	
	
	/**
	 * Register a WordPress action and map it back to the calling object
	 *
	 * @param string $action Name of the action
	 * @param string $function Function name (optional)
	 * @param int $priority WordPress priority (optional)
	 * @param int $accepted_args Number of arguments the function accepts (optional)
	 * @return void
	 **/
	
	function add_action ($action, $function = '', $priority = 10, $accepted_args = 1)
	{
		add_action ($action, array (&$this, $function == '' ? $action : $function), $priority, $accepted_args);
	}


	/**
	 * Register a WordPress filter and map it back to the calling object
	 *
	 * @param string $action Name of the action
	 * @param string $function Function name (optional)
	 * @param int $priority WordPress priority (optional)
	 * @param int $accepted_args Number of arguments the function accepts (optional)
	 * @return void
	 **/
	
	function add_filter ($filter, $function = '', $priority = 10, $accepted_args = 1)
	{
		add_filter ($filter, array (&$this, $function == '' ? $filter : $function), $priority, $accepted_args);
	}


	/**
	 * Special activation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location (i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'activate'
	 * @return void
	 **/
	
	function register_activation ($pluginfile, $function = '')
	{
		add_action ('activate_'.basename (dirname ($pluginfile)).'/'.basename ($pluginfile), array (&$this, $function == '' ? 'activate' : $function));
	}
	
	
	/**
	 * Special deactivation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location (i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'deactivate'
	 * @return void
	 **/
	
	function register_deactivation ($pluginfile, $function = '')
	{
		add_action ('deactivate_'.basename (dirname ($pluginfile)).'/'.basename ($pluginfile), array (&$this, $function == '' ? 'deactivate' : $function));
	}
	
	
	/**
	 * Renders an admin section of display code
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 **/
	
	function render_admin ($ug_name, $ug_vars = array ())
	{
		global $plugin_base;
		foreach ($ug_vars AS $key => $val)
			$$key = $val;

		if (file_exists ("{$this->plugin_base}/view/admin/$ug_name.php"))
			include ("{$this->plugin_base}/view/admin/$ug_name.php");
		else
			echo "<p>Rendering of admin template {$this->plugin_base}/view/admin/$ug_name.php failed</p>";
	}


	/**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 **/
	
	function render ($ug_name, $ug_vars = array ())
	{
		foreach ($ug_vars AS $key => $val)
			$$key = $val;

		if (file_exists (TEMPLATEPATH."/view/{$this->plugin_name}/$ug_name.php"))
			include (TEMPLATEPATH."/view/{$this->plugin_name}/$ug_name.php");
		else if (file_exists ("{$this->plugin_base}/view/{$this->plugin_name}/$ug_name.php"))
			include ("{$this->plugin_base}/view/{$this->plugin_name}/$ug_name.php");
		else
			echo "<p>Rendering of template $ug_name.php failed</p>";
	}
	
	
	/**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 **/

	function capture ($ug_name, $ug_vars = array ())
	{
		ob_start ();
		$this->render ($ug_name, $ug_vars);
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}
	

	/**
	 * Captures an admin section of display code
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return string Captured code
	 **/

	function capture_admin ($ug_name, $ug_vars = array ())
	{
		ob_start ();
		$this->render_admin ($ug_name, $ug_vars);
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}
	
	
	/**
	 * Display a standard error message (using CSS ID 'message' and classes 'fade' and 'error)
	 *
	 * @param string $message Message to display
	 * @return void
	 **/
	
	function render_error ($message)
	{
	?>
<div class="fade error" id="message">
 <p><?php echo $message ?></p>
</div>
<?php
	}
	
	
	/**
	 * Display a standard notice (using CSS ID 'message' and class 'updated').
	 * Note that the notice can be made to automatically disappear, and can be removed
	 * by clicking on it.
	 *
	 * @param string $message Message to display
	 * @param int $timeout Number of seconds to automatically remove the message (optional)
	 * @return void
	 **/
	
	function render_message ($message, $timeout = 0)
	{
		?>
<div class="updated" id="message" onclick="this.parentNode.removeChild (this)">
 <p><?php echo $message ?></p>
</div>
	<?php	
	}


	/**
	 * Get the plugin's base directory
	 *
	 * @return string Base directory
	 **/
	
	function dir ()
	{
		return $this->plugin_base;
	}
	
	
	/**
	 * Get a URL to the plugin.  Useful for specifying JS and CSS files
	 *
	 * For example, <img src="<?php echo $this->url () ?>/myimage.png"/>
	 *
	 * @return string URL
	 **/
	
	function url ($url = '')
	{
		if ($url)
			return str_replace ('\\', urlencode ('\\'), str_replace ('&amp;amp', '&amp;', str_replace ('&', '&amp;', $url)));
		else
		{
			$url = substr ($this->plugin_base, strlen ($this->realpath (ABSPATH)));
			if (DIRECTORY_SEPARATOR != '/')
				$url = str_replace (DIRECTORY_SEPARATOR, '/', $url);

			$url = get_bloginfo ('wpurl').'/'.ltrim ($url, '/');
		
			// Do an SSL check - only works on Apache
			global $is_IIS;
			if (isset ($_SERVER['HTTPS']) && !$is_IIS)
				$url = str_replace ('http://', 'https://', $url);
		}
		return $url;
	}

	
	
	/**
	 * Performs a version update check using an RSS feed.  The function ensures that the feed is only
	 * hit once every given number of days, and the data is cached using the WordPress Magpie library
	 *
	 * @param string $url URL of the RSS feed
	 * @param int $days Number of days before next check
	 * @return string Text to display
	 **/
	
	function version_update ($url, $days = 7)
	{
		if (!function_exists ('fetch_rss'))
		{
			if (!file_exists (ABSPATH.'wp-includes/rss.php'))
				return '';
			include (ABSPATH.'wp-includes/rss.php');
		}

		$now = time ();
		
		$checked = get_option ('plugin_urbangiraffe_rss');
	
		// Use built-in Magpie caching
		if (!isset ($checked[$this->plugin_name]) || $now > $checked[$this->plugin_name] + ($days * 24 * 60 * 60))
		{
			$rss = fetch_rss ($url);
			if (count ($rss->items) > 0)
			{
				foreach ($rss->items AS $pos => $item)
				{
					if (isset ($checked[$this->plugin_name]) && strtotime ($item['pubdate']) < $checked[$this->plugin_name])
						unset ($rss->items[$pos]);
				}
			}
		
			$checked[$this->plugin_name] = $now;
			update_option ('plugin_urbangiraffe_rss', $checked);
			return $rss;
		}
	}
	
	
	/**
	 * Version of realpath that will work on systems without realpath
	 *
	 * @param string $path The path to canonicalize
	 * @return string Canonicalized path
	 **/
	
	function realpath ($path)
	{
		$path = str_replace ('~', $_SERVER['DOCUMENT_ROOT'], $path);
		if (function_exists ('realpath'))
			return realpath ($path);
		else if (DIRECTORY_SEPARATOR == '/')
		{
	    // canonicalize
	    $path = explode (DIRECTORY_SEPARATOR, $path);
	    $newpath = array ();
	    for ($i = 0; $i < sizeof ($path); $i++)
			{
				if ($path[$i] === '' || $path[$i] === '.')
					continue;
					
				if ($path[$i] === '..')
				{
					array_pop ($newpath);
					continue;
				}
				
				array_push ($newpath, $path[$i]);
	    }
	
	    $finalpath = DIRECTORY_SEPARATOR.implode (DIRECTORY_SEPARATOR, $newpath);
      return $finalpath;
		}
		
		return $path;
	}
	
	
	function checked ($item, $field = '')
	{
		if ($field && is_array ($item))
		{
			if (isset ($item[$field]) && $item[$field])
				echo ' checked="checked"';
		}
		else if (!empty ($item))
			echo ' checked="checked"';
	}
	
	function select ($items, $default = '')
	{
		if (count ($items) > 0)
		{
			foreach ($items AS $key => $value)
				echo '<option value="'.$key.'"'.($key == $default ? ' selected="selected"' : '').'>'.$value.'</option>';
		}
	}
}

if (!class_exists ('Widget'))
{
	class Widget
	{
		function Widget ($name, $max = 1, $id = '', $args = '')
		{
			$this->name        = $name;
			$this->id          = $id;
			$this->widget_max  = $max;
			$this->args        = $args;
			
			if ($this->id == '')
				$this->id = strtolower (preg_replace ('/[^A-Za-z]/', '-', $this->name));

			$this->widget_available = 1;
			if ($this->widget_max > 1)
			{
				$this->widget_available = get_option ('widget_available_'.$this->id ());
				if ($this->widget_available === false)
					$this->widget_available = 1;
			}
			
			add_action ('plugins_loaded', array (&$this, 'initialize'));
		}
		
		function initialize ()
		{
			// Compatability functions for WP 2.1
			if (!function_exists ('wp_register_sidebar_widget'))
			{
				function wp_register_sidebar_widget ($id, $name, $output_callback, $classname = '')
				{
					register_sidebar_widget($name, $output_callback, $classname);
				}
			}

			if (!function_exists ('wp_register_widget_control'))
			{
				function wp_register_widget_control($name, $control_callback, $width = 300, $height = 200)
				{
					register_widget_control($name, $control_callback, $width, $height);
				}
			}
			
			if (function_exists ('wp_register_sidebar_widget'))
			{
				if ($this->widget_max > 1)
				{
					add_action ('sidebar_admin_setup', array (&$this, 'setup_save'));
					add_action ('sidebar_admin_page', array (&$this, 'setup_display'));
				}

				$this->load_widgets ();
			}
		}
		
		function load_widgets ()
		{
			for ($pos = 1; $pos <= $this->widget_max; $pos++)
			{
				wp_register_sidebar_widget ($this->id ($pos), $this->name ($pos), $pos <= $this->widget_available ? array (&$this, 'show_display') : '', $this->args (), $pos);
			
				if ($this->has_config ())
					wp_register_widget_control ($this->id ($pos), $this->name ($pos), $pos <= $this->widget_available ? array (&$this, 'show_config') : '', $this->args (), $pos);
			}
		}
		
		function args ()
		{
			if ($this->args)
				return $args;
			return array ('classname' => '');
		}
		
		function name ($pos)
		{
			if ($this->widget_available > 1)
				return $this->name.' ('.$pos.')';
			return $this->name;
		}
		
		function id ($pos = 0)
		{
			if ($pos == 0)
				return $this->id;
			return $this->id.'-'.$pos;
		}
		
		function show_display ($args, $number = 1)
		{
			$config = get_option ('widget_config_'.$this->id ($number));
			if ($config === false)
				$config = array ();
				
			$this->load ($config);
			$this->display ($args);
		}
		
		function show_config ($position)
		{
			if (isset ($_POST['widget_config_save_'.$this->id ($position)]))
			{
				$data = $_POST[$this->id ()];
				if (count ($data) > 0)
				{
					$newdata = array ();
					foreach ($data AS $item => $values)
						$newdata[$item] = $values[$position];
					$data = $newdata;
				}
				
				update_option ('widget_config_'.$this->id ($position), $this->save ($data));
			}

			$options = get_option ('widget_config_'.$this->id ($position));
			if ($options === false)
				$options = array ();
				
			$this->config ($options, $position);
			echo '<input type="hidden" name="widget_config_save_'.$this->id ($position).'" value="1" />';
		}
		
		function has_config () { return false; }
		function save ($data)
		{
			return array ();
		}
		
		function setup_save ()
		{
			if (isset ($_POST['widget_setup_save_'.$this->id ()]))
			{
				$this->widget_available = intval ($_POST['widget_setup_count_'.$this->id ()]);
				if ($this->widget_available < 1)
					$this->widget_available = 1;
				else if ($this->widget_available > $this->widget_max)
					$this->widget_available = $this->widget_max;

				update_option ('widget_available_'.$this->id (), $this->widget_available);
				
				$this->load_widgets ();
			}
		}
		
		function config_name ($field, $pos)
		{
			return $this->id ().'['.$field.']['.$pos.']';
		}
		
		function setup_display ()
		{
			?>
			<div class="wrap">
				<form method="post">
					<h2><?php echo $this->name ?></h2>
					<p style="line-height: 30px;"><?php _e('How many widgets would you like?', $this->id); ?>
						<select name="widget_setup_count_<?php echo $this->id () ?>" value="<?php echo $options; ?>">
							<?php for ( $i = 1; $i < 10; ++$i ) : ?>
							 <option value="<?php echo $i ?>"<?php if ($this->widget_available == $i) echo ' selected="selected"' ?>><?php echo $i ?></option>
							<?php endfor; ?>
						</select>
						<span class="submit">
							<input type="submit" name="widget_setup_save_<?php echo $this->id () ?>" value="<?php echo attribute_escape(__('Save', $this->id)); ?>" />
						</span>
					</p>
				</form>
			</div>
			<?php
		}
	}
}


class EmbedChessboard extends EmbedChessboard_Plugin
{
	function EmbedChessboard ()
	{
		$this->register_plugin ('embedchessboard', __FILE__);
		
		$this->add_filter ('the_content');
		$this->add_action ('wp_head');
	}
	
	function wp_head ()
	{
		
	}
	
	function replace ($matches)
	{
		$height = $matches[1];
                if ($height == "") { $height = 600; }
		$pgnText = preg_replace("@<.*?>@", "", $matches[2]);
		$pgnId = dechex(crc32($pgnText));
                return $this->capture ('chessboard', array ('height' => $height, 'pgnText' => $pgnText, 'pgnId' => $pgnId));
	}

	function the_content ($text)
	{
	  return preg_replace_callback ("@\[pgn\s*(.*?)\](.*?)\[/pgn\]@s", array (&$this, 'replace'), $text);
	}
}

$embedchessboard = new EmbedChessboard;
?>
