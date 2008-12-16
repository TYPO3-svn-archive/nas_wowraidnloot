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
 * Plugin 'Manage Characters' for the 'nas_wowraidnloot' extension.
 *
 * @author	Nadine Schwingler <naddy@schattenhandel.de>
 * @package	TYPO3
 * @subpackage	tx_naswowraidnloot
 */
class tx_naswowraidnloot_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_naswowraidnloot_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_naswowraidnloot_pi1.php';	// Path to this script relative to the extension dir.
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
		
		$content= $this->getCharacter(1);
			
		return $this->pi_wrapInBaseClass($content);
	}
	
	function getCharacter($charId){
		$content = '';
		$markerArray = array();
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','uid='.$charId);
		if ($res){
			$character = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$res_realm = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_server','uid='.$character['server']);
			if ($res_realm){
				$realm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_realm);
			}
		}
		
		if ($realm == '' and $character == ''){
			return 'nix';
		}
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		# URL vorbereiten
		$realm_name = str_replace(' ','+',$realm['title']);
		$URL = "http://".$realm['continent'].".wowarmory.com/character-sheet.xml?r=".$realm_name."&n=".$character['name'];
 		# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 
		curl_setopt ($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
		$load = curl_exec($curl);
		curl_close($curl);
		
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
		
		# Namen und Level des eingelesenen Charakters ausgeben
 		//$content = $xml->characterInfo->character['name']." hat das Level ".$xml->characterInfo->character['level'];
		foreach($xml->characterInfo->character->attributes() as $a => $b) {
			$markerArray['###'.strtoupper($a).'###'] = (string)$b;
    		$content .= $a.'="'.$b."\"<br>";
		}
		t3lib_div::devLog('markerArray', $this->extKey, 0, $markerArray);
		
		return $content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi1/class.tx_naswowraidnloot_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi1/class.tx_naswowraidnloot_pi1.php']);
}

?>