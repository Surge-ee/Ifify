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

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>

 Since you did not provide instructions on the form, make sure to put plugin documentation here.
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.ifify.php */
/* Location: /system/expressionengine/third_party/ifify/pi.ifify.php */