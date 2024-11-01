<?php

/**
 * Simple superclass which provides extension loading and error message recording functionality for
 * rewrites of PEAR classes to work without requiring the full PEAR system.
 *
 * @category    SoftPEAR
 * @package     SoftPEAR
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @author      Sterling Hughes <sterling@php.net>
 * @author      Stig Bakken <ssb@php.net>
 * @author      Tomas V.V.Cox <cox@idecnet.com>
 * @author      Greg Beaver <cellog@php.net>
 * @copyright   2010-2011 Soft
 * @copyright   1997-2009 Sterling Hughes, Stig Bakken,Tomas V.V.Cox, Greg Beaver
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     $Id: class.SoftPEAR.php 675 2011-05-31 20:48:23Z conor $
 */


// {{{ class SoftPEAR

/**
 * Simple superclass which provides extension loading and error message recording functionality for
 * rewrites of PEAR classes to work without requiring the full PEAR system.
 * 
 * @category    SoftPEAR
 * @package     SoftPEAR
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @author      Sterling Hughes <sterling@php.net>
 * @author      Stig Bakken <ssb@php.net>
 * @author      Tomas V.V.Cox <cox@idecnet.com>
 * @author      Greg Beaver <cellog@php.net>
 * @copyright   2010-2011 Soft
 * @copyright   1997-2009 Sterling Hughes, Stig Bakken,Tomas V.V.Cox, Greg Beaver
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     1.0.0
 */
class SoftPEAR
{
	// {{{ properties
	
	/**
	 * Holds the message for any error that was encountered/has occurred.
	 *
	 * @access  protected
	 * @var     string
	 */
	var $_error_message = null;
	
	// }}}
	
	
	// {{{ constructor()
	
	/**
	 * Doesn't currently do anything.
	 *
	 * @access  public
	 */
	function SoftPEAR()
	{

	}
	
	// }}}
	
	
	// {{{ loadExtension()

    /**
    * OS-independant PHP extension load. Care must be taken to use the correct extension name for
    * case sensitive OSes.
    *
    * @param    string    $extension_name   The extension's name.
    * @return   boolean   True if the dl() call was succesful, false otherwise.
    */
    function loadExtension($extension_name)
    {
        if (!extension_loaded($extension_name)) {
            // If either returns true dl() will produce a fatal error, must prevent that
            if ((ini_get('enable_dl') != 1) || (ini_get('safe_mode') == 1)) {
                return false;
            }
			
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $suffix = '.dll';
            } elseif (PHP_OS == 'HP-UX') {
                $suffix = '.sl';
            } elseif (PHP_OS == 'AIX') {
                $suffix = '.a';
            } elseif (PHP_OS == 'OSX') {
                $suffix = '.bundle';
            } else {
                $suffix = '.so';
            }
			
            return @dl('php_' . $extension_name . $suffix) || @dl($extension_name . $suffix);
        }
		
        return true;
    }

    // }}}
	
	
	// {{{ _setErrorMessage()
	
	/**
	 * Stores a message about an error which has just been encountered/has occurred.
	 *
	 * @access  protected
	 * @param   string    $message   The text for the error message.
	 * @return  none
	 */
	function _setErrorMessage($message)
	{
		$this->_error_message = $message;
	}
	
	// }}}
	
	
	// {{{ getErrorMessage()
	
	/**
	 * Returns the message for an error which has just been encountered/has occurred.
	 *
	 * @access  public
	 * @return  string    The text message for the latest error to be encountered.
	 */
	function getErrorMessage()
	{
		return $this->_error_message;
	}
	
	// }}}
}

// }}}

?>