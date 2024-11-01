<?php

/**
 * SoftBlowfishEncryption is a rewrite of the PEAR Crypt_Blowfish class, modified to no longer use
 * PEAR, and to use a Soft namespace to avoid any potential clashes with the original PEAR class. It
 * allows for encryption and decryption on the fly using the Blowfish algorithm. It does not require
 * the MCrypt PHP extension, but uses it if available, otherwise it uses only PHP.
 * Encryption/decryption is supported with or without a secret key.
 *
 * @category    SoftPEAR
 * @package     SoftPEAREncryption
 * @subpackage  SoftBlowfishEncryption
 * @author      Matthew Fonda <mfonda@php.net>
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @copyright   2005-2010 Matthew Fonda
 * @copyright   2011-2011 Soft
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     $Id: class.SoftBlowfishEncryption.php 675 2011-05-31 20:48:23Z conor $
 */

/**
 * Load in the base Soft PEAR class so it can be extended. File path is relative to this file.
 */
require_once('class.SoftPEAR.php');


// {{{ Constants

/**
 * Engine choice constants
 */

/**
 * To let the SoftBlowfishEncryption package decide which engine to use.
 */
define('Soft_BLOWFISH_ENCRYPTION_AUTO', 1);

/**
 * To use the MCrypt PHP extension.
 */
define('Soft_BLOWFISH_ENCRYPTION_MCRYPT', 2);

/**
 * To use the PHP-only engine.
 */
define('Soft_BLOWFISH_ENCRYPTION_PHP', 3);

// }}}


// {{{ class SoftBlowfishEncryption

/**
 * To disable using the mcrypt library, define the Soft_BLOWFISH_ENCRYPTION_DONT_USE_MCRYPT
 * constant. This is useful for instance on Windows platform with a buggy mdecrypt_generic()
 * function.
 * 
 * <code>
 * define('Soft_BLOWFISH_ENCRYPTION_DONT_USE_MCRYPT', true);
 * </code>
 *
 * @category    SoftPEAR
 * @package     SoftPEAREncryption
 * @subpackage  SoftBlowfishEncryption
 * @author      Matthew Fonda <mfonda@php.net>
 * @author      Philippe Jausions <jausions@php.net>
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @copyright   2005-2010 Matthew Fonda
 * @copyright   2011-2011 Soft
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     1.0.0
 */
class SoftBlowfishEncryption extends SoftPEAR
{
	// {{{ properties
	
	/**
	 * Implementation-specific SoftBlowfishEncryption object instance.
	 *
	 * @access  private
	 * @var     object
	 */
	var $_crypt = null;
	
	/**
	 * Initialisation vector.
	 *
	 * @access  protected
	 * @var     string
	 */
	var $_iv = null;
	
	/**
	 * Holds block size.
	 *
	 * @access  protected
	 * @var     integer
	 */
	var $_block_size = 8;
	
	/**
	 * Holds IV size.
	 *
	 * @access  protected
	 * @var     integer
	 */
	var $_iv_size = 8;
	
	/**
	 * Holds max key size.
	 *
	 * @access  protected
	 * @var     integer
	 */
	var $_key_size = 56;
	
	// }}}
	
	
	// {{{ constructor()
	
	/**
	 * Initialises this SoftBlowfishEncryption object instance (in EBC mode), and sets the secret
	 * key.
	 *
	 * @access  public
	 * @param   string $key
	 * @deprecated
	 * @see     SoftBlowfishEncryption::factory()
	 */
	function SoftBlowfishEncryption($key)
	{
		$this->_crypt =& SoftBlowfishEncryption::factory('ecb', $key);
		
		if (!is_object($this->_crypt)) {
			$this->_crypt->setKey($key);
		}
	}
	
	// }}}
	
	
	// {{{ factory()
	
	/**
	 * SoftBlowfishEncryption object factory.
	 *
	 * This is the recommended method to create a SoftBlowfishEncryption instance.
	 *
	 * When using Soft_BLOWFISH_ENCRYPTION_AUTO, the package can be forced to ignore the MCrypt
	 * extension by defining Soft_BLOWFISH_ENCRYPTION_DONT_USE_MCRYPT.
	 *
	 * @static
	 * @access  public
	 * @param   string    $mode   Operating mode 'ecb' or 'cbc' (case insensitive).
	 * @param   string    $key    The encryption/decryption key.
	 * @param   string    $iv     Initialization vector (must be provided for CBC mode).
	 * @param   integer   $engine   One of Soft_BLOWFISH_ENCRYPTION_AUTO,
	 *                              Soft_BLOWFISH_ENCRYPTION_PHP or Soft_BLOWFISH_ENCRYPTION_MCRYPT.
	 * @return  object|false   SoftBlowfishEncryption object or false if an error occurred.
	 */
	function &factory($mode = 'ecb', $key = null, $iv = null,
		$engine = Soft_BLOWFISH_ENCRYPTION_AUTO)
	{
		switch ($engine) {
			case Soft_BLOWFISH_ENCRYPTION_AUTO:
				if (!defined('Soft_BLOWFISH_ENCRYPTION_DONT_USE_MCRYPT') && 
						extension_loaded('mcrypt')) {
					$engine = Soft_BLOWFISH_ENCRYPTION_MCRYPT;
				} else {
					$engine = Soft_BLOWFISH_ENCRYPTION_PHP;
				}
				
				break;
			
			case Soft_BLOWFISH_ENCRYPTION_MCRYPT:
				if (!$this->_loadExtension('mcrypt')) {
					$this->_setErrorMessage('MCrypt extension is not available.');
					
					return false;
				}
				
				break;
		}
		
		switch ($engine) {
			case Soft_BLOWFISH_ENCRYPTION_PHP:
				$mode = strtoupper($mode);
				$class = 'SoftBlowfishEncryption' . $mode;
				
				// Files are in a folder relative to this file
				include_once('SoftBlowfishEncryption/' . 'class.' . $class . '.php');
				
				$crypt = new $class(null);
				break;
			
			case Soft_BLOWFISH_ENCRYPTION_MCRYPT:
				// File is in a folder relative to this file
				include_once('SoftBlowfishEncryption/' . 'class.SoftBlowfishEncryptionMCrypt.php');
				
				$crypt = new SoftBlowfishEncryptionMCrypt($mode);
				break;
		}
		
		if (!is_null($key) || !is_null($iv)) {
			$result = $crypt->setKey($key, $iv);
			
			if ($result == false) {
				return false;
			}
		}
		
		return $crypt;
	}
	
	// }}}
	
	
	// {{{ getBlockSize()
	
	/**
	 * Returns the algorithm's block size.
	 *
	 * @access  public
	 * @return  integer
	 */
	function getBlockSize()
	{
		return $this->_block_size;
	}
	
	// }}}
	
	
	// {{{ getIVSize()
	
	/**
	 * Returns the algorithm's IV size.
	 *
	 * @access  public
	 * @return  integer
	 */
	function getIVSize()
	{
		return $this->_iv_size;
	}
	
	// }}}
	
	
	// {{{ getMaxKeySize()
	
	/**
	 * Returns the algorithm's maximum key size.
	 *
	 * @access  public
	 * @return  integer
	 */
	function getMaxKeySize()
	{
		return $this->_key_size;
	}
	
	// }}}
	
	
	// {{{ encrypt()
	
	/**
	 * Encrypts a string.
	 *
	 * Value is padded with NULL characters prior to encryption. The type may need to be trimmed or
	 * cast when it is decrypted.
	 *
	 * @access  public
	 * @param   string    $plain_text   The string of characters/bytes to encrypt.
	 * @return  string|false   Returns cipher text on success, false on failure.
	 */
	function encrypt($plain_text)
	{
		return $this->_crypt->encrypt($plain_text);
	}
	
	// }}}
	
	
	// {{{ decrypt()
	
	/**
	 * Decrypts an encrypted string.
	 *
	 * The value was padded with NULL characters when encrypted. The decrypted string may need to be
	 * trimmed or to have its type cast.
	 *
	 * @access  public
	 * @param   string    $cipher_text   The binary string to decrypt.
	 * @return  string|false   Returns plain text on success, false on failure.
	 */
	function decrypt($cipher_text)
	{
		return $this->_crypt->decrypt($cipher_text);
	}
	
	// }}}
	
	
	// {{{ setKey()
	
	/**
	 * Sets the secret key.
	 *
	 * The key must be non-zero, and less than or equal to 56 characters (bytes) in length.
	 *
	 * If the PHP mcrypt extension is being used, this method must be called before each encrypt()
	 * and decrypt() call.
	 *
	 * @access  public
	 * @param   string    $key   The key.
	 * @return  boolean   Returns true on success, false on failure.
	 */
	function setKey($key)
	{
		return $this->_crypt->setKey($key);
	}
	
	// }}}
}

// }}}

?>