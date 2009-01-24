<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Nadine Schwingler <naddy@schattenhandel.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Reports' for the 'nas_wowraidnloot' extension.
 *
 * @author	Nadine Schwingler <naddy@schattenhandel.de>
 * @package	TYPO3
 * @subpackage	tx_naswowraidnloot
 */
class tx_naswowraidnloot_pi3 extends tslib_pibase {
	var $prefixId      = 'tx_naswowraidnloot_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_naswowraidnloot_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'nas_wowraidnloot';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$content='';
		
		$info = array();
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
	  	$URL = 'http://de.wowhead.com/?zone=3456&xml';
	  	t3lib_div::devLog('URL', $this->extKey, 0, $URL);
	  	# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 		$load = curl_exec($curl);
		curl_close($curl);
		# eingelesenen String zu SimpleXMLElement umformen
		t3lib_div::devLog('load', $this->extKey, 0, $load);
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi3/class.tx_naswowraidnloot_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi3/class.tx_naswowraidnloot_pi3.php']);
}

?>