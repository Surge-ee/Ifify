<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Ifify Plugin
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Digital Surgeons
 * @link		http://www.digitalsurgeons.com
 */

$plugin_info = array(
	'pi_name'		=> 'Ifify',
	'pi_version'	=> '0.1.0',
	'pi_author'		=> 'Digital Surgeons',
	'pi_author_url'	=> 'http://www.digitalsurgeons.com',
	'pi_description'=> 'Makes any plugin tag into an if statement.',
	'pi_usage'		=> Ifify::usage()
);


class Ifify {

	/**
	 * Required member
	 *
	 * @var string
	 */
	public $return_data;

	/**
	 * What values constitute truthy
	 *
	 * @var mixed
	 */
	protected $truthy;

	/**
	 * What method is being called
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->EE =& get_instance();

		// Parameters specific to ifify
		$this->method = $this->EE->TMPL->fetch_param('method', '');
		$this->truthy = $this->EE->TMPL->fetch_param('truthy', '');

	}

	/**
	 * We use this to call an arbitrary third party plugin.
	 *
	 * @param  strong $plugin Name of the plugin to call.
	 * @param  array $args    Argument list (required param, usually empty)
	 * @return string         Output if truthy, else empty string.
	 */
	public function __call($plugin, $args) {

		// Call plugin.
		$this->EE->TMPL->log_item("Calling third party plugin '$plugin'.");
		$obj = new $plugin;

		// If plugin works in constructor, look for it's return_data,
		// else, call the method to get it's return value.
		$return = (is_string($this->method)) ? $obj->{$this->method}() : $this->return_data;

		// If the reported truthy value is matched,
		// return contents of tag pair.
		$this->EE->TMPL->log_item("Comparing plugin return value '$return' to truthy value '{$this->truthy}'");

		if ($return == $this->truthy) {

			$this->EE->TMPL->log_item('Evaluated true, returning tag pair contents.');

			return $this->EE->TMPL->tagdata;

		} else {

			// Not truthy, so return empty.
			$this->EE->TMPL->log_item('Evaluated false, returning nothing.');
			return '';

		}

	}

	// ----------------------------------------------------------------
	// Usage
	// ----------------------------------------------------------------

	/**
	 * Plugin Usage
	 */
	public static function usage() {
		ob_start();
?>
	{exp:ifify:surgeree method="modulo" numerator="3" denominator="2" truthy="1"}
		This content will only show if plugin yields a truthy value.
	{/exp:ifify:surgeree}
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

}

/* End of file pi.ifify.php */
/* Location: /system/expressionengine/third_party/ifify/pi.ifify.php */
