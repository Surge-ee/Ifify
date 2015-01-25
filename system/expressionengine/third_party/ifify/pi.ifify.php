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
	'pi_version'	=> '0.3.0',
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
	 * Whether or not to force strict comparison
	 *
	 * @var boolean
	 */
	protected $strict = false;

	/**
	 * Values EE considers boolean true
	 *
	 * @var array
	 */
	protected $yesyValues = array(
		'y',
		'yes',
		'true',
		'1'
	);

	/**
	 * Values EE considers boolean false
	 *
	 * @var array
	 */
	protected $noeyValues = array(
		'n',
		'no',
		'false',
		'0'
	);

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->EE =& get_instance();

		// Parameters specific to ifify
		$this->method = $this->EE->TMPL->fetch_param('method', '');
		$this->truthy = $this->EE->TMPL->fetch_param('truthy', '');
		$this->strict = in_array($this->EE->TMPL->fetch_param('ifify_strict'), $this->yesyValues);

	}

	/**
	 * We use this to call an arbitrary third party plugin.
	 *
	 * @param  strong $plugin Name of the plugin to call.
	 * @param  array $args    Argument list (required param, usually empty)
	 * @return string         Output if truthy, else empty string.
	 */
	public function __call($plugin, $args) {

		// Translations of plugin variable
		$plugin_class = ucfirst($plugin);
		$plugin_path = PATH_THIRD."{$plugin}/pi.{$plugin}.php";
		$module_path = PATH_THIRD."{$plugin}/mod.{$plugin}.php";

		// Check if class is defined and load it if not.
		if (!class_exists($plugin_class)) {

			// Attempt to load the file if it exists.
			if (file_exists($plugin_path)) {

				require_once $plugin_path;

			} elseif (file_exists($module_path)) {

				require_once $module_path;

			}

			// Check one last time in case its a bad plugin file.
			if (!class_exists($plugin_class)) {

				$this->EE->TMPL->log_item("WARNING: Plugin or module '$plugin' is not defined. Returning empty.");
				return '';

			}

		}

		// Make sure that parameters passed are parsed.
		$this->parse_params();

		// Call plugin.
		$this->EE->TMPL->log_item("Calling third party plugin or module '$plugin'.");
		$obj = new $plugin_class;

		// If plugin works in constructor, look for it's return_data,
		// else, call the method to get it's return value.
		if (is_string($this->method) && $this->method !== '') {

			// Let's make sure the method actually exists.
			if (method_exists($obj, $this->method)) {

				$return = $obj->{$this->method}();

				// Something the plugin authors don't actually
				// return a vlue, but just rely on $this->return_data.
				// So let's check for that.
				if ($return === null) {

					$return = $obj->return_data;

				}

			} else {

				$this->EE->TMPL->log_item("WARNING: Method '{$this->method}' doesn't exist. Returning empty.");
				return '';

			}

		} else {

			$return =  $obj->return_data;

		}

		// If the reported truthy value is matched,
		// return contents of tag pair.
		$this->EE->TMPL->log_item("Comparing plugin return value '$return' to truthy value '{$this->truthy}'");

		$comparison = false;

		// Check if the truthy value is an EE y/n interchangeable,
		// compare based on that fuzzy logic if so. Do a direct relaxed
		// comparison otherwise.
		if ($this->is_yn_value($this->truthy) && !$this->strict) {
			$comparison = $this->compare_yn_values($return, $this->truthy);
		} else {
			$comparison = ($return == $this->truthy);
		}

		if ($comparison) {

			$this->EE->TMPL->log_item('Evaluated true, returning tag pair contents.');

			return $this->EE->TMPL->tagdata;

		} else {

			// Not truthy, so return empty.
			$this->EE->TMPL->log_item('Evaluated false, returning nothing.');
			return '';

		}

	}

	/**
	 * Checks if a value is like the EE y/n interchangeables
	 * @param  string  $value Value to check
	 * @return boolean        Is or isn't
	 */
	protected function is_yn_value($value) {

		$ynValues = array_merge($this->yesyValues, $this->noeyValues);
		return in_array($value, $ynValues);

	}

	/**
	 * Compares EE y/n interchangeables
	 * @param  string $val1 First value
	 * @param  string $val2 Second value
	 * @return bool         True if both are yesy or both are noey, else false.
	 */
	protected function compare_yn_values($val1, $val2) {

		$val1Yesy = in_array($val1, $this->yesyValues);
		$val1Noey = in_array($val1, $this->noeyValues);

		$val2Yesy = in_array($val2, $this->yesyValues);
		$val2Noey = in_array($val2, $this->noeyValues);

		return ($val1Yesy && $val2Yesy) || ($val1Noey && $val2Noey);

	}

	/**
	 * Invokes the EE parser on a parameter's value.
	 *
	 * @param  string $param Value of the parameter to be parsed.
	 * @return string        Parsed value.
	 */
	protected function parse_param($param) {

		$TMPL2 = new EE_Template();
		$TMPL2->start_microtime = $this->EE->TMPL->start_microtime;
		$TMPL2->template        = $param;
		$TMPL2->tag_data	    = array();
		$TMPL2->var_single      = array();
		$TMPL2->var_cond	    = array();
		$TMPL2->var_pair	    = array();
		$TMPL2->plugins         = $this->EE->TMPL->plugins;
		$TMPL2->modules         = $this->EE->TMPL->modules;
		$TMPL2->parse_tags();
		$TMPL2->process_tags();

		return $TMPL2->template;

	}

	/**
	 * Iteratively parses all parameters in $this->EE->TMPL->tagparams.
	 *
	 * Operates on $this->EE->TMPL->tagparams directly.
	 *
	 * @return void
	 */
	protected function parse_params() {

		$params = $this->remove_reserved_params($this->EE->TMPL->tagparams);

		foreach ($params as &$value) {

			$value = $this->parse_param($value);

		}

		foreach ($this->EE->TMPL->tagparams as $key => $value) {

			if (array_key_exists($key, $params)) {

				$this->EE->TMPL->tagparams[$key] = $params[$key];

			}

		}

	}

	/**
	 * Copies a parameter array without params specific to Ifify.
	 *
	 * @param  array $params Parameters array.
	 * @return array         Filtered parameters array.
	 */
	protected function remove_reserved_params($params) {

		$reserved_params = array(
			'method',
			'truthy'
		);

		$new_params = array();

		foreach ($params as $key => $value) {

			if (!in_array($key, $reserved_params)) {

				$new_params[$key] = $value;

			}

		}

		return $new_params;

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
