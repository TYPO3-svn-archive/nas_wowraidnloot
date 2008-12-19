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
		$this->pi_initPIflexForm();
		$this->pi_loadLL();
		
		$template_file = t3lib_extMgm::siteRelPath('nas_wowraidnloot')."/res/main.html"; 
		$this->tmpl = $this->cObj->fileResource($template_file);
		
		$this->types = explode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayType','sDEF'));
		$this->dPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayPid','sDEF');
		$charId = 0;
		$charId = $this->piVars['char_id'];
		$userId = $GLOBALS['TSFE']->fe_user->user['uid'];
		
		foreach($this->types as $nr => $type){
			switch ($type){
				case 'CHAR-LIST-ALL': $content = $this->getCharList();
					break;
				case 'CHAR-LIST': $content = $this->getCharList($userId);
					break;
				case 'CHAR-SINGLE': 
						if ($charId != 0){
							$content = $this->getCharacter($charId);
						} else {
							$content = $this->pi_getLL('noCharSelected');
						}
					break;
			}
		}
			
		return $this->pi_wrapInBaseClass($content);
	}
	
	function getCharList($userId = -1){
		$content = '';
		
		$where = '1=1 ';
		if ($userId > -1) {
			$where = ' AND player='.$userId;
		}
		$where .= $this->cObj->enableFields('tx_naswowraidnloot_chars');
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','1=1'.$where);
		if ($res) {
			$content .= '<ul>';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$content .= '<li>';
				if ($this->dPid > 0){
					$content .= $this->pi_linkToPage($row['name'],$this->dPid,'',array($this->prefixId.'[char_id]'=>$row['uid']));
				} else {
					$content .= $this->pi_linkTP($row['name'],array($this->prefixId.'[char_id]'=>$row['uid']));
				}
				$content .= '</li>';
			}
			$content .= '</ul>';
		}
		
		return $content;
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
    		//$content .= $a.'="'.$b."\"<br>";
		}
		t3lib_div::devLog('markerArray', $this->extKey, 0, $markerArray);
		$img_add = 'default';
		$char_level = $xml->characterInfo->character['level'];
		if ($char_level >= 70){
			$img_add = '70';
		}
		if ($char_level == 80) {
			$img_add = '80';
		}
		$markerArray['###IMG###'] = $img_add.'/'.$xml->characterInfo->character['genderId'].'-'.$xml->characterInfo->character['raceId'].'-'.$xml->characterInfo->character['classId'];
		$content .= $this->renderContent('###CHAR_SHEET###',$markerArray);
	
		return $content;
	}
	
	function renderContent($subpart, $markerArray) {
	  	$wrappedSubpartArray = array();
	  	if ($this->errorText == '') {
	  		$markerArray['###ERROR###'] = '';
	  	} else {
	  		$markerArray['###ERRORCLASS###'] = 'error';
			$markerArray['###ERROR###'] = $this->errorText;
	  	}

	    $subpart = $this->cObj->getSubpart($this->tmpl,$subpart);
	    $content = $this->cObj->substituteMarkerArrayCached($subpart, $markerArray, array(), $wrappedSubpartArray);

	    return $content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi1/class.tx_naswowraidnloot_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi1/class.tx_naswowraidnloot_pi1.php']);
}

?>