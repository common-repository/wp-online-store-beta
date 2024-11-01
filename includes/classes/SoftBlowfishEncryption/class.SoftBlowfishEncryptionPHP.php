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
 * @author      Philippe Jausions <jausions@php.net>
 * @author      Conor Kerr <pear@dev.Soft.net>
 * @copyright   2005-2008 Matthew Fonda
 * @copyright   2010-2011 Soft
 * @license     http://www.opensource.net/licenses/bsd-license.php New BSD
 * @version     $Id: class.SoftBlowfishEncryptionPHP.php 675 2011-05-31 20:48:23Z conor $
 */

/**
 * Load in the base class. As it is at the level below the current file, the path from the main
 * runtime file (index.php) must be used.
 */
require_once(DIR_WS_CLASSES . 'class.SoftBlowfishEncryption.php');


// {{{ class SoftBlowfishEncryptionPHP

/**
 * Common class for PHP-only implementations of the Blowfish algorithm.
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
class SoftBlowfishEncryptionPHP extends SoftBlowfishEncryption
{
	// {{{ properties
	
	/**
	 * Array containing 18 32-bit subkeys.
	 *
	 * @access  protected
	 * @var     array
	 */
	var $_p = array();
	
	/**
	 * Array of four S-Blocks each containing 256 32-bit entries.
	 *
	 * @access  protected
	 * @var     array
	 */
	var $_s = array();
	
	/**
	 * Whether the IV is required.
	 *
	 * @access  protected
	 * @var     boolean
	 */
	var $_iv_required = false;
	
	/**
	 * Hash value of the last used key.
	 * 
	 * @access  protected
	 * @var	    string
	 */
	var $_key_hash = null;
	
	// }}}
	
	
	// {{{ constructor()
	
	/**
	 * Initialises this SoftBlowfishEncryptionPHP object instance, and sets the secret key.
	 *
	 * @access  protected
	 * @param   string    $key    
	 * @param   string    $mode   Operating mode. Either 'ecb' or 'cbc'.
	 * @param   string    $iv     Initialisation vector.
	 */
	function __construct($key = null, $iv = null)
	{
		$this->_iv = $iv . ((strlen($iv) < $this->_iv_size)
			? str_repeat(chr(0), $this->_iv_size - strlen($iv)) : '');
		
		if (!is_null($key)) {
			$this->setKey($key, $this->_iv);
		}
	}
	
	// }}}
	
	
	// {{{ _init()
	
	/**
	 * Initialises this SoftBlowfishEncryptionPHP object instance.
	 *
	 * @access  private
	 */
	function _init()
	{
		require_once('class.SoftBlowfishEncryptionDefaultKey.php');
		
		$defaults = new SoftBlowfishEncryptionDefaultKey();
		
		$this->_p = $defaults->p;
		$this->_s = $defaults->s;
	}
	
	// }}}
	
	
	// {{{ _binXOR()
	
	/**
	 * Workaround for XOR on certain systems.
	 *
	 * @access  protected
	 * @param   integer|float   $l
	 * @param   integer|float   $r
	 * @return  float
	 */
	function _binXOR($l, $r)
	{
		$x = (($l < 0) ? (float) ($l + 4294967296) : (float)$l) ^
			(($r < 0) ? (float) ($r + 4294967296) : (float)$r);
		
		return (float) (($x < 0) ? $x + 4294967296 : $x);
	}
	
	// }}}
	
	
	// {{{ _encipher()
	
	/**
	 * Enciphers a single 64-bit block.
	 *
	 * @access  protected
	 * @param   integer   &$Xl
	 * @param   integer   &$Xr
	 */
	function _encipher(&$Xl, &$Xr)
	{
		if ($Xl < 0) {
			$Xl += 4294967296;
		}
		
		if ($Xr < 0) {
			$Xr += 4294967296;
		}
		
		for ($i = 0; $i < 16; $i++) {
			$temp = $Xl ^ $this->_p[$i];
			
			if ($temp < 0) {
				$temp += 4294967296;
			}
			
			$Xl = fmod((fmod($this->_s[0][($temp >> 24) & 255] +
				$this->_s[1][($temp >> 16) & 255], 4294967296)  ^
				$this->_s[2][($temp >> 8) & 255]) +
				$this->_s[3][$temp & 255], 4294967296) ^ $Xr;
			
			$Xr = $temp;
		}
		
		$Xr = $this->_binXOR($Xl, $this->_p[16]);
		$Xl = $this->_binXOR($temp, $this->_p[17]);
	}
	
	// }}}
	
	
	// {{{ _decipher()
	
	/**
	 * Deciphers a single 64-bit block.
	 *
	 * @access  protected
	 * @param   integer   &$Xl
	 * @param   integer   &$Xr
	 */
	function _decipher(&$Xl, &$Xr)
	{
		if ($Xl < 0) {
			$Xl += 4294967296;
		}
		
		if ($Xr < 0) {
			$Xr += 4294967296;
		}
		
		for ($i = 17; $i > 1; $i--) {
			$temp = $Xl ^ $this->_p[$i];
			
			if ($temp < 0) {
				$temp += 4294967296;
			}
			
			$Xl = fmod((fmod($this->_s[0][($temp >> 24) & 255] +
				$this->_s[1][($temp >> 16) & 255], 4294967296)  ^
				$this->_s[2][($temp >> 8) & 255]) +
				$this->_s[3][$temp & 255], 4294967296) ^ $Xr;
			
			$Xr = $temp;
		}
		
		$Xr = $this->_binXOR($Xl, $this->_p[1]);
		$Xl = $this->_binXOR($temp, $this->_p[0]);
	}
	
	// }}}
	
	
	// {{{ setKey()
	
	/**
	 * Sets the secret key to be used for the encryption/decryption.
	 *
	 * The key must be non-zero, and less than or equal to 56 characters (bytes) in length.
	 *
	 * If the PHP mcrypt extension is being used, this method must be called before each encrypt()
	 * and decrypt() call.
	 *
	 * @access  public
	 * @param   string    $key   The key to be used.
	 * @param   string    $iv    8-char initialisation vector (required for CBC mode).
	 * @return  boolean   Returns true on success, false on failure.
	 * 
	 * @TODO Fix the caching of the key.
	 */
	function setKey($key, $iv = null)
	{
		if (!is_string($key)) {
			$this->_setErrorMessage('Key must be a string');
			
			return false;
		}
		
		$len = strlen($key);
		
		if ($len > $this->_key_size || $len == 0) {
			$this->_setErrorMessage('Key must be less than ' . $this->_key_size .
				' characters (bytes) and non-zero. Supplied key length: ' . $len);
			
			return false;
		}
		
		if ($this->_iv_required) {
			if (strlen($iv) != $this->_iv_size) {
				$this->_setErrorMessage('IV must be ' . $this->_iv_size .
					' characters (bytes) long. Supplied IV length: ' . strlen($iv));
				
				return false;
			}
			
			$this->_iv = $iv;
		}
		
		if ($this->_key_hash == md5($key)) {
			return true;
		}
		
		$this->_init();
		
		$k = 0;
		$data = 0;
		$datal = 0;
		$datar = 0;
		
		for ($i = 0; $i < 18; $i++) {
			$data = 0;
			
			for ($j = 4; $j > 0; $j--) {
				$data = $data << 8 | ord($key{$k});
				
				$k = ($k+1) % $len;
			}
			
			$this->_p[$i] ^= $data;
		}
		
		for ($i = 0; $i <= 16; $i += 2) {
			$this->_encipher($datal, $datar);
			$this->_p[$i] = $datal;
			$this->_p[$i+1] = $datar;
		}
		
		for ($i = 0; $i < 256; $i += 2) {
			$this->_encipher($datal, $datar);
			$this->_s[0][$i] = $datal;
			$this->_s[0][$i+1] = $datar;
		}
		
		for ($i = 0; $i < 256; $i += 2) {
			$this->_encipher($datal, $datar);
			$this->_s[1][$i] = $datal;
			$this->_s[1][$i+1] = $datar;
		}
		
		for ($i = 0; $i < 256; $i += 2) {
			$this->_encipher($datal, $datar);
			$this->_s[2][$i] = $datal;
			$this->_s[2][$i+1] = $datar;
		}
		
		for ($i = 0; $i < 256; $i += 2) {
			$this->_encipher($datal, $datar);
			$this->_s[3][$i] = $datal;
			$this->_s[3][$i+1] = $datar;
		}
		
		$this->_key_hash = md5($key);
		
		return true;
	}
	
	// }}}
}

// }}}

?>