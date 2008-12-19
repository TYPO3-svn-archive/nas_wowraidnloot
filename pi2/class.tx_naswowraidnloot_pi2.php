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
// date2cal, modified to work in the frontend
require_once(t3lib_extMgm::extPath('nas_wowraidnloot').'res/class.frontend_JScalendar.php');


/**
 * Plugin 'Manage Raids' for the 'nas_wowraidnloot' extension.
 *
 * @author	Nadine Schwingler <naddy@schattenhandel.de>
 * @package	TYPO3
 * @subpackage	tx_naswowraidnloot
 */
class tx_naswowraidnloot_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_naswowraidnloot_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_naswowraidnloot_pi2.php';	// Path to this script relative to the extension dir.
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

		$content = '';
		
		//make the date2cal instance
        if (t3lib_extMgm::isLoaded('date2cal')) {
            $this->date2cal = frontend_JScalendar::getInstance();
        } else {
            return '<p class="error">' . $this->pi_getLL('error_date2cal_not_loaded') . '</p>';
        }
		
		$template_file = t3lib_extMgm::siteRelPath('nas_wowraidnloot')."/res/main.html"; 
		$this->tmpl = $this->cObj->fileResource($template_file);
		
		$this->types = explode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayType','sDEF'));
		$this->singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displaySingle','sDEF');
		$this->newPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayNew','sDEF');
		$this->editPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayEdit','sDEF');
		$this->backPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayBack','sDEF');
		//t3lib_div::devLog('types', $this->extKey, 0, $this->types);
		
		$userId = $GLOBALS['TSFE']->fe_user->user['uid'];
		$raidId = $this->piVars['raid_id'];
		
		foreach($this->types as $nr => $type){
			switch ($type){
				case 'MENU': $content .= $this->getMenu($userId);
					break;
				case 'RAID-LIST': $content .= $this->getRaidList($userId);
					break;
				case 'RAID-SINGLE': 
						if ($raidId != 0){
							$content .= $this->getRaid($raidId);
						} else {
							$content .= $this->pi_getLL('noRaidSelected');
						}
					break;
				case 'RAID-NEW': $content .= $this->getNewForm($userId);
					break;
				case 'RAID-EDIT': $content .= $this->getEditForm($userId, $raidId);
					break;
			}
		}
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function getMenu($userId = 0){
		$content = '<p>';
		$list = '';
		if ($this->newPid){
			$list .= '<li>'.$this->pi_linkToPage($this->pi_getLL('newRaid'),$this->newPid).'</li>';
		}
		if ($this->backPid){
			$list .= '<li>'.$this->pi_linkToPage($this->pi_getLL('back'),$this->backPid).'</li>';
		}
		
		if ($list != ''){
			$content .= '<ul>'.$list.'</ul>';
		}
		$content .= '</p>';
		return $content;
	}
	
	function getRaidList($userId = 0){
		$content = '<p>';
		
		$content .= '</p>';
		
		return $content;
	}
	
	function getRaid($raidId){
		$content = '';
		return $content;
	}
	
	function getNewForm($userId = 0){
		$content = '';
		$markerArray = array();
		$markerArray['###PI###'] = $this->prefixId;
		
		//Falls kein Kalenderfeld geladen wird
		$markerArray['###DATE2CAL_JS###'] = '';
		// date2cal js for singleview
        $markerArray['###DATE2CAL_JS###'] = $this->date2cal->getMainJS();
        	
		$markerArray['###TITLE###'] = $this->pi_getLL('title');
		$markerArray['###OPEN###'] = $this->pi_getLL('open');
		$markerArray['###DESTINATION###'] = $this->pi_getLL('destination');
		$markerArray['###DESTINATION_SELECT###'] = $this->getDestSelect();
		$markerArray['###START###'] = $this->pi_getLL('start');
		$prefillValue = '';
		// render the datefield using the date2cal extension
		$field = $this->prefixId . '[start]';
		$this->date2cal->config['inputField'] = $field;
		$this->date2cal->config['calConfig']['ifFormat'] = '%d-%m-%Y %H:%M';
		$this->date2cal->setConfigOption('ifFormat', '%d-%m-%Y %H:%M');
		$this->date2cal->setConfigOption('showsTime', 1, true);
		$this->date2cal->setConfigOption('time24', 1, true);
		$fieldContent = $this->date2cal->render($prefillValue, $field);
		$markerArray['###START_FIELD###'] = $fieldContent;
		$markerArray['###END###'] = $this->pi_getLL('end');
		// render the datefield using the date2cal extension
		$field = $this->prefixId . '[end]';
		$this->date2cal->config['inputField'] = $field;
		$this->date2cal->config['calConfig']['ifFormat'] = '%d-%m-%Y %H:%M';
		$this->date2cal->setConfigOption('ifFormat', '%d-%m-%Y %H:%M');
		$this->date2cal->setConfigOption('showsTime', 1, true);
		$this->date2cal->setConfigOption('time24', 1, true);
		$fieldContent = $this->date2cal->render($prefillValue, $field);
		$markerArray['###END_FIELD###'] = $fieldContent;
		
		$content = $this->renderContent('###NEW_RAID###',$markerArray);
		
		return $content;
	}
	
	function getEditForm($userId, $raidId){
		$content = '';
		return $content;
	}
	
	function getDestSelect() {
		$content = '';
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		# URL vorbereiten
		$URL = "http://eu.wowarmory.com/data/dungeonStrings.xml";
 		# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 
		curl_setopt ($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
		$load = curl_exec($curl);
		curl_close($curl);
		
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
		
		//foreach($xml->attributes() as $a => $b) {
		//   	$content .= $a.'="'.$b."\"<br>";
		//}
		
		# Namen und IDs der Dungeons ausgeben
		$dungeons = array();
		foreach ($xml->dungeons->dungeon as $lair){
			foreach($lair->attributes() as $a => $b) {
				$dungeons[intval($lair['id'])][$a] = (string)$b;
				//$markerArray['###'.strtoupper($a).'###'] = (string)$b;
		    	//$content .= $a.'="'.$b."\"<br>";
			}			
		}
		$select = '';
		foreach ($dungeons as $id => $dungeon){
			$select .= '<option value="'.$dungeon['id'].'">'.$dungeon['name'].'</option>';
		}
		if ($select != ''){
				$content .= '<select id="'.$this->prefixId.'[dungeonid]" name="'.$this->prefixId.'[dungeonId]">'.$select.'</select>';
		}
		//t3lib_div::devLog('dungeons', $this->extKey, 0, $dungeons);
		
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi2/class.tx_naswowraidnloot_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi2/class.tx_naswowraidnloot_pi2.php']);
}

?>