<?php

/**
 * MCrypt PHP extension wrapper for SoftBlowfishEncryption package.
 * 
 * @category    SoftPEAR
 * @package     SoftPEAREncryption
 * @subpackage  SoftBlowfishEncryption
 * @author      Matthew Fonda <mfonda@php.net>
 * @author      Philippe Jausions <jausions@php.net>
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @copyright   2005-2008 Matthew Fonda
 * @copyright   2010-2011 Soft
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     $Id: class.SoftBlowfishEncryptionMCrypt.php 675 2011-05-31 20:48:23Z conor $
 */

/**
 * Load in the base class. As it is at the level below the current file, the path from the main
 * runtime file (index.php) must be used.
 */
require_once(DIR_WS_CLASSES . 'class.SoftBlowfishEncryption.php');


// {{{ class SoftBlowfishEncryptionMCrypt

/**
 * Example using the factory method in CBC mode and forcing using the MCrypt library.
 * <code>
 * $bf =& SoftBlowfishEncryption::factory('cbc', null, null, Soft_BLOWFISH_ENCRYPTION_MCRYPT);
 * if (PEAR::isError($bf)) {
 *     echo $bf->getMessage();
 *     exit;
 * }
 * $iv = 'abc123+=';
 * $key = 'My secret key';
 * $bf->setKey($key, $iv);
 * $encrypted = $bf->encrypt('this is some example plain text');
 * $bf->setKey($key, $iv);
 * $plaintext = $bf->decrypt($encrypted);
 * echo "plain text: $plaintext";
 * </code>
 *
 * @category    SoftPEAR
 * @package     SoftPEAREncryption
 * @subpackage  SoftBlowfishEncryption
 * @author      Matthew Fonda <mfonda@php.net>
 * @author      Philippe Jausions <jausions@php.net>
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @copyright   2005-2008 Matthew Fonda
 * @copyright   2010-2011 Soft
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     1.0.0
 */
class SoftBlowfishEncryptionMCrypt extends SoftBlowfishEncryption
{
	// {{{ properties
	
	/**
	 * MCrypt td resource.
	 *
	 * @access  private
	 * @var     resource
	 */
	var $_td = null;
	
	// }}}
	
	
	// {{{ constructor
	
	/**
	 * Initialises this SoftBlowfishEncryptionMCrypt object instance, and sets the secret key.
	 *
	 * @access  public
	 * @param   string    $mode   Operating mode 'ecb' or 'cbc' (case insensitive).
	 * @param   string    $key    The encryption/decryption key.
	 * @param   string    $iv     Initialisation vector.
	 */
	function SoftBlowfishEncryptionMCrypt($mode = 'ecb', $key = null, $iv = null)
	{
		$this->_iv = $iv . ((strlen($iv) < 8) ? str_repeat(chr(0), 8 - strlen($iv)) : '');
		
		$this->_td = mcrypt_module_open(MCRYPT_BLOWFISH, '', $mode, '');
		
		if (is_null($iv)) {
			$this->_iv = mcrypt_create_iv(8, MCRYPT_RAND);
		}
		
		switch (strtolower($mode)) {
			case 'ecb':
				$this->_iv_required = false;
				break;
			
			case 'cbc':
			default:
				$this->_iv_required = true;
				break;
		}
		
		$this->setKey($key, $this->_iv);
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
		if (!is_string($plain_text)) {
			$this->_setErrorMessage('Input must be a string');
			
			return false;
		}
		
		return mcrypt_generic($this->_td, $plain_text);
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
		if (!is_string($cipher_text)) {
			$this->_setErrorMessage('Cipher text must be a string');
			
			return false;
		}
		
		return mdecrypt_generic($this->_td, $cipher_text);
	}
	
	// }}}
	
	
	// {{{ setKey()
	
	/**
	 * Sets the secret key.
	 *
	 * The key must be non-zero, and less than or equal to 56 characters (bytes) in length.
	 *
	 * This method must be called before each encrypt() and decrypt() call.
	 *
	 * @access  public
	 * @param   string    $key   The key.
	 * @return  boolean   Returns true on success, false on failure.
	 */
	function setKey($key, $iv = null)
	{
		if (!is_string($key)) {
			$this->_setErrorMessage('Key must be a string.');
			
			return false;
		}
		
		$len = strlen($key);
		
		if ($len > 56 || $len == 0) {
			$this->_setErrorMessage('Key must be less than 56 characters (bytes) and non-zero. ' .
				'Supplied key length: ' . $len);
			
			return false;
		}
		
		if ($this->_iv_required) {
			if (strlen($iv) != 8) {
				$this->_setErrorMessage('IV must be 8-character (byte) long. Supplied IV length: ' .
					strlen($iv));
				
				return false;
			}
			
			$this->_iv = $iv;
		}
		
		$return = mcrypt_generic_init($this->_td, $key, $this->_iv);
		
		if ($return < 0) {
			$this->_setErrorMessage('Unknown PHP MCrypt library error.');
			
			false;
		}
		
		return true;
	}
	
	// }}}
}

// }}}

?>