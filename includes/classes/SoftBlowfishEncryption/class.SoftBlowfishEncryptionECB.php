<?php

/**
 * PHP implementation of the Blowfish algorithm in ECB mode.
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
 * @version     $Id: class.SoftBlowfishEncryptionECB.php 675 2011-05-31 20:48:23Z conor $
 */

/**
 * Load in the parent class. File path is relative to this file.
 */
require_once('class.SoftBlowfishEncryptionPHP.php');


// {{{ class SoftBlowfishEncryptionECB

/**
 * Example
 * <code>
 * $bf =& SoftBlowfishEncryptionECB::factory('ecb');
 * if (PEAR::isError($bf)) {
 *     echo $bf->getMessage();
 *     exit;
 * }
 * $bf->setKey('My secret key');
 * $encrypted = $bf->encrypt('this is some example plain text');
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
class SoftBlowfishEncryptionECB extends SoftBlowfishEncryptionPHP
{
	// {{{ PHP4 compatible constructor
	
	/**
	 * Initialises this SoftBlowfishEncryptionECB object instance, and sets the secret key.
	 *
	 * @access  public
	 * @param   string    $key
	 * @param   string    $iv   Initialisation vector.
	 */
	function SoftBlowfishEncryptionECB($key = null, $iv = null)
	{
		$this->__construct($key, $iv);
	}
	
	// }}}
	
	
	// {{{ constructor
	
	/**
	 * Class constructor.
	 *
	 * @access  public
	 * @param   string    $key
	 * @param   string    $iv   Initialisation vector.
	 */
	function __construct($key = null, $iv = null)
	{
		$this->_iv_required = false;
		
		parent::__construct($key, $iv);
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
			
		} elseif (empty($this->_p)) {
			$this->_setErrorMessage('The key is not initialized.');
			
			return false;
		}
		
		$cipher_text = '';
		
		$len = strlen($plain_text);
		
		$plain_text .= str_repeat(chr(0), (8 - ($len % 8)) % 8);
		
		for ($i = 0; $i < $len; $i += 8) {
			list(, $Xl, $Xr) = unpack('N2', substr($plain_text, $i, 8));
			
			$this->_encipher($Xl, $Xr);
			
			$cipher_text .= pack('N2', $Xl, $Xr);
		}
		
		return $cipher_text;
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
			$this->_setErrorMessage('Cipher text must be a string.');
			
			return false;
		}
		
		if (empty($this->_p)) {
			$this->_setErrorMessage('The key is not initialised.');
			
			return false;
		}
		
		$plain_text = '';
		
		$len = strlen($cipher_text);
		
		$cipher_text .= str_repeat(chr(0), (8 - ($len % 8)) % 8);
		
		for ($i = 0; $i < $len; $i += 8) {
			list(, $Xl, $Xr) = unpack('N2', substr($cipher_text, $i, 8));
			
			$this->_decipher($Xl, $Xr);
			
			$plain_text .= pack('N2', $Xl, $Xr);
		}
		
		return $plain_text;
	}
	
	// }}}
}

// }}}

?>