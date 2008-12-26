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
//XAJAX
require_once (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');


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
		//TODO: Sicherheitsabfragen für die XML-Abfragen auf der Armory...
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_initPIflexForm();
		$this->pi_loadLL();
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nas_wowraidnloot']);

		$content = '';
		//t3lib_div::devLog('piVars', $this->extKey, 0, $this->piVars);
		//t3lib_div::devLog('extConf', $this->extKey, 0, $this->extConf);
		
		//make the date2cal instance
        if (t3lib_extMgm::isLoaded('date2cal')) {
            $this->date2cal = frontend_JScalendar::getInstance();
        } else {
            return '<p class="error">' . $this->pi_getLL('error_date2cal_not_loaded') . '</p>';
        }
        //XAJAX ini
		$this->XAJAX_start();
		
		$template_file = t3lib_extMgm::siteRelPath('nas_wowraidnloot')."/res/main.html"; 
		$this->tmpl = $this->cObj->fileResource($template_file);
		
		$this->types = explode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayType','sDEF'));
		$this->singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displaySingle','sDEF');
		$this->newPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayNew','sDEF');
		$this->editPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayEdit','sDEF');
		$this->backPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayBack','sDEF');
		$this->myPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayMy','sDEF');
		$this->leadPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayLead','sDEF');
		//t3lib_div::devLog('types', $this->extKey, 0, $this->types);
		
		$userId = $GLOBALS['TSFE']->fe_user->user['uid'];
		$raidId = $this->piVars['raid_id'];
		$charId = $this->piVars['member'];
		
		foreach($this->types as $nr => $type){
			switch ($type){
				case 'MENU': $content .= $this->getMenu($userId);
					break;
				case 'RAID-LIST-ALL': $content .= $this->getRaidList(0,'all');
					break;
				case 'RAID-LIST-LEADER': $content .= $this->getRaidList($userId,'leader');
					break;
				case 'RAID-LIST-MEMBER': $content .= $this->getRaidList($userId,'member');
					break;
				case 'RAID-SINGLE': 
						if ($raidId != 0){
							$content .= $this->getRaid($raidId);
							$content .= $this->getShowLoot($raidId);
						} else {
							$content .= $this->pi_getLL('noRaidSelected');
						}
					break;
				case 'RAID-NEW': if ($this->piVars['save_new_raid']){
									$raidId = $this->saveRaid('new',$userId);
									$content .= $this->getEditForm($userId,$raidId);
									$content .= $this->getEditLootForm($raidId);
									$content .= $this->getRandomLootForm($raidId);
									$content .= $this->getShowLoot($raidId);
								} else {
									$content .= $this->getNewForm($userId);
								}
					break;
				case 'RAID-EDIT': if ($this->piVars['save_edit_raid']){
									$content .= $this->saveRaid('edit',$userId,$raidId);
								}
								$content .= $this->getEditForm($userId,$raidId);
								if($this->piVars['save_loot']) {
									$content .= $this->saveLoot($raidId);
								} else if($this->piVars['save_random']){
									$content .= $this->saveRandomLoot($raidId);
								}
								$content .= $this->getEditLootForm($raidId);
								$content .= $this->getRandomLootForm($raidId);
								$content .= $this->getShowLoot($raidId);
					break;
			}
		}
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function saveRaid($saveType, $userId, $raidId = 0) {
		$content = '';

		$saveValues = array();
		
		$saveValues['pid'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'storagePid','sDEF');
		if ($saveType == 'new'){
			$saveValues['leader'] = $userId;
		} else {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_naswowraidnloot_raid_member_mm','uid_local='.$raidId);
			$saveValues['leader'] = implode(',',$this->piVars['leader']);
		}
		$saveValues['title'] = $this->piVars['title'];
		if ($this->piVars['open'] == 'on'){
			$saveValues['open'] = 1;
		} else {
			$saveValues['open'] = 0;
		}
		$saveValues['destinationid'] = $this->piVars['dungeonId'];
		$saveValues['start'] = strtotime($this->piVars['start']);
		$saveValues['end'] = strtotime($this->piVars['end']);
		
		//t3lib_div::devLog('saveValues', $this->extKey, 0, $saveValues);

		if ($saveType == 'new'){
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_raid',$saveValues);
			$raidId = $GLOBALS['TYPO3_DB']->sql_insert_id();
			$content = $raidId;
		} else {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_naswowraidnloot_raid','uid='.$raidId,$saveValues);
			$content .= '<span class="save_message">'.$this->pi_getLL('raid_saved').'</span>';
		}
		
		if ($this->piVars['h_member']){
			$member = explode(',',$this->piVars['h_member']);
			foreach ($member as $nr => $id) {
				$mm_saveVars = array();
				$mm_saveVars['uid_local'] = $raidId;
				$mm_saveVars['uid_foreign'] = $id;
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_raid_member_mm',$mm_saveVars);
			}
		}
		
		return $content;
	}
	
	function saveLoot($raidId){
		$content = '';
		
		$saveValues = array();
		$saveValues['raidid'] = $raidId;
		$saveValues['itemid'] = $this->piVars['items'];
		$saveValues['bossid'] = $this->piVars['boss'];
		$saveValues['charid'] = $this->piVars['item_member'];
		$saveValues['loottype'] = $this->piVars['loottype'];
		$saveValues['pid'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'storagePid','sDEF');
		//t3lib_div::devLog('saveValues', $this->extKey, 0, $saveValues);
				
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_collected',$saveValues);
		$content .= '<span class="save_message">'.$this->pi_getLL('loot_saved').'</span>';

		return $content;
	}
	
	function saveRandomLoot($raidId){
		$content = '';
		
		$itemName = $this->piVars['random_name'];
		$searchName = str_replace(' ','%20',$itemName);
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
		# URL vorbereiten
		$URL = "http://eu.wowarmory.com/search.xml?searchQuery=".$searchName.'&searchType=all';
		//t3lib_div::devLog('URL', $this->extKey, 0, $URL);
 		# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
		 
		curl_setopt ($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
		$load = curl_exec($curl);
		curl_close($curl);
		
		# eingelesenen String zu SimpleXMLElement umformen
		//t3lib_div::devLog('load', $this->extKey, 0, $load);
		$xml = new SimpleXMLElement($load);
		$item = array();
		foreach($xml->armorySearch->searchResults->items->item as $searchItem) {
			foreach($searchItem->attributes() as $a => $b) {
					$item[$a] = (string)$b;
				}
		}
		//t3lib_div::devLog('item', $this->extKey, 0, $item);
		$itemId = intval($item['id']);
		
		$saveValues = array();
		$saveValues['raidid'] = $raidId;
		$saveValues['itemid'] = $itemId;
		$saveValues['itemname'] = $itemName;
		$saveValues['bossid'] = 0;
		$saveValues['charid'] = $this->piVars['item_member'];
		$saveValues['loottype'] = $this->piVars['loottype'];
		$saveValues['pid'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'storagePid','sDEF');
		//t3lib_div::devLog('saveValues', $this->extKey, 0, $saveValues);
				
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_collected',$saveValues);
		$content .= '<span class="save_message">'.$this->pi_getLL('loot_saved').'</span>';

		return $content;
	}
	
	function getMenu($userId = 0){
		$content = '<p>';
		$list = '';
		
		$usergroups = explode(',',$GLOBALS['TSFE']->fe_user->user['usergroup']);
		//t3lib_div::devLog('usergroups', $this->extKey, 0, $usergroups);
		if ($this->backPid){
			$list .= '<li>'.$this->pi_linkToPage($this->pi_getLL('back'),$this->backPid).'</li>';
		}
		if ($this->newPid AND in_array($this->extConf['manage_group'],$usergroups)){
			$list .= '<li>'.$this->pi_linkToPage($this->pi_getLL('newRaid'),$this->newPid).'</li>';
		}
		if ($this->myPid){
			$list .= '<li>'.$this->pi_linkToPage($this->pi_getLL('myRaids'),$this->myPid).'</li>';
		}
		if ($this->leadPid AND in_array($this->extConf['manage_group'],$usergroups)){
			$list .= '<li>'.$this->pi_linkToPage($this->pi_getLL('myLeads'),$this->leadPid).'</li>';
		}
		
		if ($list != ''){
			$content .= '<div id="raid_menu"><ul>'.$list.'</ul></div>';
		}
		$content .= '</p>';
		return $content;
	}
	
	function getRaidList($userId = 0,$type = ''){
		//t3lib_div::devLog('setMember', $this->extKey, 0, $userId .' - '.$type);
		$show = false;
		$content = '';
				
		$where = '1=1 ';
		if ($type == 'leader'){
			$where .= ' AND (leader=\''.$userId.'\' OR leader LIKE \''.$userId.',%\' OR leader LIKE \'%,'.$userId.',%\' OR leader LIKE \'%,'.$userId.'\')';
		} elseif ($type == 'all'){
			$where .= ' AND open=1';
		}
		$where .= $this->cObj->enableFields('tx_naswowraidnloot_raid');
		//t3lib_div::devLog('where', $this->extKey, 0, $where);
		$sort = 'start DESC';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid',$where,'',$sort);
		//t3lib_div::devLog('setMember', $this->extKey, 0, $GLOBALS['TYPO3_DB']->SELECTquery('*','tx_naswowraidnloot_raid',$where));
		if ($res) {
			$content .= '<ul>';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if ($type == 'member'){
					$res_member = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign','tx_naswowraidnloot_raid_member_mm','uid_local='.$row['uid']);
					if ($res_member){
						while($row_member = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_member)){
							if ($row_member['uid_foreign'] == $userId){
								$show = true;
							}
						}
					}
				} else {
					$show = true;
				}
				if ($show){
					$content .= '<li>';
					if ($this->editPid > 0 and $userId > 0){
						$leaders = explode(',',$row['leader']);
						if (in_array($userId,$leaders)){
							$content .= '<span class="edit_link">'.$this->pi_linkToPage($this->pi_getLL('editRaid'),$this->editPid,'',array($this->prefixId.'[raid_id]'=>$row['uid'])).'</span>';
						}
					}
					setlocale(LC_ALL,'de_DE.utf8');
					$title_line = $row['title'].' ('.strftime("%A, %e. %b. %Y",$row['start']).')';
					if ($this->singlePid > 0){
						$content .= $this->pi_linkToPage($title_line,$this->singlePid,'',array($this->prefixId.'[raid_id]'=>$row['uid']));
					} else {
						$content .= $this->pi_linkTP($title_line,array($this->prefixId.'[raid_id]'=>$row['uid']));
					}
					$content .= '</li>';
				}
			}
		}
		if ($show){
			$content = '<div id="raid_list"><ul>'.$content.'</ul></div>';
		} else {
			$content = 	$this->pi_getLL('noRaid');
		}
		
		return $content;
	}
	
	function getRaid($raidId){
		$content = '';
		$markerArray = array();
		
		$where = 'uid='.$raidId;
		$where .= $this->cObj->enableFields('tx_naswowraidnloot_raid');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid',$where);
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$markerArray['###TITLE###'] = $row['title'];
			$markerArray['###DESTINATION###'] = $this->getArmoryDestination($row['destinationid']);
			setlocale(LC_ALL,'de_DE.utf8');
			$markerArray['###START###'] = strftime("%A, %e. %b. %Y",$row['start']);
			if ($row['end'] != 0){
				$markerArray['###END###'] = strftime("%A, %e. %b. %Y",$row['end']);
			} else {
				$markerArray['###END###'] = '';	
			}
			$markerArray['###MEMBER_TITLE###'] = $this->pi_getLL('member');
			$markerArray['###MEMBER###'] = $this->getMemberList($row['uid']);
						
			$markerArray['###LOOT###'] = $this->pi_getLL('loot');
			
			$content = $this->renderContent('###SHOW_RAID###',$markerArray);
		}
		
		return $content;
	}
	
	function getNewForm($userId = 0){
		$content = '';
		$markerArray = array();
		$markerArray['###PI###'] = $this->prefixId;
		$markerArray['###FORM_ACTION###'] = $this->pi_linkTP_keepPIvars_url();
		
		//Falls kein Kalenderfeld geladen wird
		$markerArray['###DATE2CAL_JS###'] = '';
		// date2cal js for singleview
        $markerArray['###DATE2CAL_JS###'] = $this->date2cal->getMainJS();

        // Raidtitle
		$markerArray['###TITLE###'] = $this->pi_getLL('title');
		// Visible for all
		$markerArray['###OPEN###'] = $this->pi_getLL('open');
		// Destination Select
		$markerArray['###DESTINATION###'] = $this->pi_getLL('destination');
		$markerArray['###DESTINATION_SELECT###'] = $this->getDestSelect();
		// start-Date
		$markerArray['###START###'] = $this->pi_getLL('start');
		$prefillValue = '';
		// render the datefield using the date2cal extension
		$field = $this->prefixId . '[start]';
		$this->date2cal->config['inputField'] = $field;
		$this->date2cal->config['calConfig']['ifFormat'] = '%d-%m-%Y';
		$this->date2cal->setConfigOption('ifFormat', '%d-%m-%Y');
		$this->date2cal->setConfigOption('showsTime', 0, true);
		$this->date2cal->setConfigOption('time24', 1, true);
		$fieldContent = $this->date2cal->render($prefillValue, $field);
		$markerArray['###START_FIELD###'] = $fieldContent;
		// end-Date
		$markerArray['###END###'] = $this->pi_getLL('end');
		// render the datefield using the date2cal extension
		$field = $this->prefixId . '[end]';
		$this->date2cal->config['inputField'] = $field;
		$this->date2cal->config['calConfig']['ifFormat'] = '%d-%m-%Y';
		$this->date2cal->setConfigOption('ifFormat', '%d-%m-%Y');
		$this->date2cal->setConfigOption('showsTime', 0, true);
		$this->date2cal->setConfigOption('time24', 1, true);
		$fieldContent = $this->date2cal->render($prefillValue, $field);
		$markerArray['###END_FIELD###'] = $fieldContent;
		// member
		$markerArray['###MEMBER###'] = $this->pi_getLL('member');
		$markerArray['###MEMBER_SELECT###'] = $this->getMemberSelect();
		// save Button
		$markerArray['###SAVE###'] = $this->pi_getLL('save');
		
		$content = $this->renderContent('###NEW_RAID###',$markerArray);
		
		return $content;
	}
	
	function getEditForm($userId, $raidId){
		//t3lib_div::devLog('getEditForm', $this->extKey, 0, $userId .' - ' .$raidId);
		$content = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid','uid='.$raidId);
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			
			$markerArray = array();
			$markerArray['###PI###'] = $this->prefixId;
			$markerArray['###FORM_ACTION###'] = $this->pi_linkTP_keepPIvars_url();
			
			//Falls kein Kalenderfeld geladen wird
			$markerArray['###DATE2CAL_JS###'] = '';
			// date2cal js for singleview
	        $markerArray['###DATE2CAL_JS###'] = $this->date2cal->getMainJS();
	
	        // Raidtitle
			$markerArray['###TITLE###'] = $this->pi_getLL('title');
			$markerArray['###RAID_TITLE###'] = $row['title'];
			// Visible for all
			$markerArray['###OPEN###'] = $this->pi_getLL('open');
			if ($row['open'] == 1) {
				$markerArray['###OPEN_CHECKED###'] = 'checked';
			} else {
				$markerArray['###OPEN_CHECKED###'] = '';
			}
			// Destination Select
			$markerArray['###DESTINATION###'] = $this->pi_getLL('destination');
			$markerArray['###DESTINATION_SELECT###'] = $this->getDestSelect($row['destinationid']);
			// start-Date
			$markerArray['###START###'] = $this->pi_getLL('start');
			if ($row['start'] != 0){
				$prefillValue = date('d-m-Y',$row['start']);
			} else {
				$prefillValue = '';
			}
			// render the datefield using the date2cal extension
			$field = $this->prefixId . '[start]';
			$this->date2cal->config['inputField'] = $field;
			$this->date2cal->config['calConfig']['ifFormat'] = '%d-%m-%Y';
			$this->date2cal->setConfigOption('ifFormat', '%d-%m-%Y');
			$this->date2cal->setConfigOption('showsTime', 0, true);
			$this->date2cal->setConfigOption('time24', 1, true);
			$fieldContent = $this->date2cal->render($prefillValue, $field);
			$markerArray['###START_FIELD###'] = $fieldContent;
			// end-Date
			$markerArray['###END###'] = $this->pi_getLL('end');
			// render the datefield using the date2cal extension
			if ($row['end'] != 0){
				$prefillValue = date('d-m-Y',$row['end']);
			} else {
				$prefillValue = '';
			}
			$field = $this->prefixId . '[end]';
			$this->date2cal->config['inputField'] = $field;
			$this->date2cal->config['calConfig']['ifFormat'] = '%d-%m-%Y';
			$this->date2cal->setConfigOption('ifFormat', '%d-%m-%Y');
			$this->date2cal->setConfigOption('showsTime', 0, true);
			$this->date2cal->setConfigOption('time24', 1, true);
			$fieldContent = $this->date2cal->render($prefillValue, $field);
			$markerArray['###END_FIELD###'] = $fieldContent;
			// member
			$markerArray['###MEMBER###'] = $this->pi_getLL('member');
			$markerArray['###MEMBER_SELECT###'] = $this->getMemberSelect($raidId);
			// save Button
			$markerArray['###SAVE###'] = $this->pi_getLL('save');
			$markerArray['###SAVE_LOOT###'] = $this->pi_getLL('save_loot');
			// Raid-Leader
			$markerArray['###LEADER###'] = $this->pi_getLL('leader');
			$markerArray['###LEADER_OPTIONS###'] = $this->getLeaderOptions($raidId);
			
			$content = $this->renderContent('###EDIT_RAID###',$markerArray);
		} else {
			$content .= $this->pi_getLL('noRaidFound');
		}
		
		return $content;
	}
	
	function getEditLootForm ($raidId) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid','uid='.$raidId);
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				
			$markerArray = array();
			$markerArray['###PI###'] = $this->prefixId;
			$markerArray['###FORM_ACTION###'] = $this->pi_linkTP_keepPIvars_url();
			
			// Loot Info
			// TODO: Loot-Info-Text in FlexForms bringen
			$markerArray['###LOOT_INFO###'] = '';
			// Boss Select
			$markerArray['###BOSS###'] = $this->pi_getLL('boss');
			$markerArray['###BOSS_SELECT###'] = $this->getBossSelect($row['destinationid']);
			// Item Select
			$markerArray['###ITEM###'] = $this->pi_getLL('item'); 
			$markerArray['###ITEM_SELECT###'] = '<span id="loot_item"><select id="'.$this->prefixId.'[items]" name="'.$this->prefixId.'[items]"><option value="0">---</option></select></span>';
			// Member Select
			$markerArray['###MEMBER###'] = $this->pi_getLL('member');
			$markerArray['###MEMBER_SELECT###'] = $this->getMemberSelect($raidId,'single');
			// Loot Type
			$markerArray['###LOOTTYPE###'] = $this->pi_getLL('loottype');
			$markerArray['###LOOTTYPE_SELECT###'] = $this->getLootTypeSelect();
			
			// save Button
			$markerArray['###SAVE_LOOT###'] = $this->pi_getLL('save_loot');
			$markerArray['###SAVE_TYPE###'] = 'save_loot';
			
			$content = $this->renderContent('###EDIT_LOOT###',$markerArray);
		}
		
		return $content;
	}
	function getRandomLootForm ($raidId) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid','uid='.$raidId);
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				
			$markerArray = array();
			$markerArray['###PI###'] = $this->prefixId;
			$markerArray['###FORM_ACTION###'] = $this->pi_linkTP_keepPIvars_url();
			
			// Loot Info
			// TODO: Loot-Info-Text in FlexForms bringen
			$markerArray['###LOOT_INFO###'] = '';
			// Item Input
			$markerArray['###ITEM###'] = $this->pi_getLL('item'); 
			$markerArray['###ITEM_INPUT###'] = '<input type="text" name="'.$this->prefixId.'[random_name]" name="'.$this->prefixId.'[random_id]></input>"';
			// Member Select
			$markerArray['###MEMBER###'] = $this->pi_getLL('member');
			$markerArray['###MEMBER_SELECT###'] = $this->getMemberSelect($raidId,'single');
			// Loot Type
			$markerArray['###LOOTTYPE###'] = $this->pi_getLL('loottype');
			$markerArray['###LOOTTYPE_SELECT###'] = $this->getLootTypeSelect();
			
			// save Button
			$markerArray['###SAVE_LOOT###'] = $this->pi_getLL('save_loot');
			$markerArray['###SAVE_TYPE###'] = 'save_random';
			
			$content = $this->renderContent('###EDIT_RANDOMLOOT###',$markerArray);
		}
		
		return $content;
	}
	
	function getShowLoot($raidId, $charId = 0, $type = 'raid'){
		$markerArray = array();
		
		$where = 'raidid='.$raidId;
		if ($charId > 0){
			$where .= 'AND charid='.$charId;
		}
		$where .= $this->cObj->enableFields('tx_naswowraidnloot_collected');
		
		$lines = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_collected',$where);
		if ($res) {
			$temp_markerArray = array();
			$temp_markerArray['###MEMBER###'] = $this->pi_getLL('member');	
			$temp_markerArray['###ITEM###'] = $this->pi_getLL('item');
			$temp_markerArray['###BOSS###'] = $this->pi_getLL('boss');
			$temp_markerArray['###LOOTTYPE###'] = $this->pi_getLL('loottype');
			$lines .= $this->renderContent('###SHOW_LOOT_LINE_RAID_TOP###',$temp_markerArray);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$temp_markerArray = array();
				$res_member = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','uid='.$row['charid']);
				if ($res_member) {
					$row_member = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_member);
					$temp_markerArray['###MEMBER###'] = $row_member['name'];
				} else {
					$temp_markerArray['###MEMBER###'] = '';	
				}
				//TODO: Abhängigkeit vom Server herstellen... FF für eu oder www-Armory
				$item_info = $this->getArmoryItem($row['itemid']);
				//t3lib_div::devLog('getArmoryItem', $this->extKey, 0, $item_info);
				$temp_markerArray['###ITEM###'] = '<a target="_blank" href="http://eu.wowarmory.com/item-info.xml?i='.$row['itemid'].'">'.$item_info['item']['name'].'</a>';
				if ($temp_markerArray['###ITEM###'] == '') {
					$temp_markerArray['###ITEM###'] = '<span class="error">'.$this->pi_getLL('armory_notFound').'</span>';
				}
				$temp_markerArray['###BOSS###'] = $item_info['drop']['name'];
				if ($row['bossid'] > 0) {
					$boss_info = array();
					$boss_info = $this->getArmoryBoss($row['bossid']);
					//t3lib_div::devLog('getArmoryBoss', $this->extKey, 0, $boss_info);
					$temp_markerArray['###BOSS###'] = $boss_info['filter']['creatureName'];
				}				
				if ($temp_markerArray['###BOSS###'] == '') {
					$temp_markerArray['###BOSS###'] = '<span class="error">'.$this->pi_getLL('armory_notFound').'</span>';
				}
				$temp_markerArray['###LOOTTYPE###'] = $this->pi_getLL('loottype_'.$row['loottype']);
				if ($type == 'raid'){
					$lines .= $this->renderContent('###SHOW_LOOT_LINE_RAID###',$temp_markerArray);
				}
			}
		}
		$markerArray['###LINES###'] = $lines;
		$content = $this->renderContent('###SHOW_LOOT###',$markerArray);
		
		return $content;
	}
	
	function getArmoryItem($itemId){
		$info = array();
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
	  	$URL = 'http://eu.wowarmory.com/item-info.xml?i='.$itemId;
	  	//t3lib_div::devLog('getArmoryItem URL', $this->extKey, 0, $URL);
	  	# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 		$load = curl_exec($curl);
		curl_close($curl);
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
				
		if ($xml->itemInfo->children()){
			foreach ($xml->itemInfo->item as $item){
				foreach ($item->attributes() as $a => $b){
					$info['item'][$a] = (string)$b;
				}
				foreach ($item->dropCreatures->creature as $creature){
					foreach ($creature->attributes() as $a => $b){
						$info['drop'][$a] = (string)$b;
						
					}
				}
			}
		}
		
		return $info;
	}
	
	function getArmoryBoss($bossId){
		$info = array();
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
	  	$URL = 'http://eu.wowarmory.com/search.xml?searchType=items&fl[source]=dungeon&fl[difficulty]=normal&fl[boss]='.$bossId;
	  	t3lib_div::devLog('getArmoryBoss URL', $this->extKey, 0, $URL);
	  	# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 		$load = curl_exec($curl);
		curl_close($curl);
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
		//t3lib_div::devLog('getArmoryBoss load', $this->extKey, 0, $load);
		
		if ($xml->armorySearch->searchResults->items->children()){
			$item = $xml->armorySearch->searchResults->items->item;
			foreach ($item->attributes() as $a => $b){
				$info['item'][$a] = (string)$b;
			}
			foreach ($item->filter as $filter){
				foreach ($filter->attributes() as $a => $b){
					$info['filter'][$a] = (string)$b;
				}
			}
		}
		
		return $info;
	}
	
	function getArmoryDestination($destinationId){
		$content = '';
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
		# URL vorbereiten
		$URL = 'http://eu.wowarmory.com/search.xml?fl[source]=dungeon&fl[dungeon]='.$destinationId.'&fl[difficulty]=all&searchType=items';
		//t3lib_div::devLog('URL', $this->extKey, 0, $URL);
 		# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 
		curl_setopt($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
		$load = curl_exec($curl);
		curl_close($curl);
		
		//t3lib_div::devLog('getBossSelect load', $this->extKey, 0, $load);
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
		
		if ($xml->armorySearch->searchResults->items->children()){
			$item = $xml->armorySearch->searchResults->items->item;
			foreach ($item->attributes() as $a => $b){
				$info['item'][$a] = (string)$b;
			}
			foreach ($item->filter as $filter){
				foreach ($filter->attributes() as $a => $b){
					$info['filter'][$a] = (string)$b;
				}
			}
		}
		//t3lib_div::devLog('info', $this->extKey, 0, $info);
		$content = $info['filter']['areaName'];
		
		return $content;
	}
	
	function getLootTypeSelect() {
		$content = '';
		
		$select .= '<option value="0">'.$this->pi_getLL('loottype_0').'</option>';
		$select .= '<option value="1">'.$this->pi_getLL('loottype_1').'</option>';
		$select .= '<option value="2">'.$this->pi_getLL('loottype_2').'</option>';
		
		if ($select != ''){
			$content .= '<select id="'.$this->prefixId.'[loottype]" name="'.$this->prefixId.'[loottype]">'.$select.'</select>';
		}

		return $content;
	}
	
	function getBossSelect($destinationId){
		$content = '';
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
		# URL vorbereiten
		$URL = "http://eu.wowarmory.com/data/dungeonStrings.xml";
 		# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 
		curl_setopt($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
		$load = curl_exec($curl);
		curl_close($curl);
		
		//t3lib_div::devLog('getBossSelect load', $this->extKey, 0, $load);
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
		
		# Namen und IDs der Dungeons ausgeben
		$bossis = array();
		foreach ($xml->dungeons->dungeon as $lair){
			if ($lair['id'] == $destinationId){
				foreach($lair->boss as $boss){
					foreach($boss->attributes() as $a => $b) {
						$bossis[(string)$boss['name']][$a] = (string)$b;
					}
				}
			}
		}
		ksort($bossis);
		$select .= '<option value="0">---</option>';
		foreach ($bossis as $name => $boss){
			$select .= '<option value="'.$boss['id'].'" ';
			$select .= '>'.$boss['name'].'</option>';
		}
		//t3lib_div::devLog('getBossSelect bossis', $this->extKey, 0, $bossis);
		if ($select != ''){
			$content .= '<select onchange="nas_wowraidnloot_setItems('.$destinationId.',this.value);" id="'.$this->prefixId.'[boss]" name="'.$this->prefixId.'[boss]">'.$select.'</select>';
		}
		
		return $content;
	}
	
	function getMemberList ($raidId){
		$content = '';
		
		$res_mm = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid_member_mm','uid_local='.$raidId);
		if ($res_mm){
			while ($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)){
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','uid='.$row_mm['uid_foreign'].$this->cObj->enableFields('tx_naswowraidnloot_chars'));
				if ($res){
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$content .= $row['name'].'<br>';	
				}
			}
		}
		
		return $content;
	}
	
	function getMemberSelect($raidId = 0, $type = 'more') {
		$content = '';
		
		$select_set = '';
		$select_get = '';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','1=1'.$this->cObj->enableFields('tx_naswowraidnloot_chars'));
		if ($res){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$select_get .= '<option value="'.$row['uid'].'">'.$row['name'].'</option>';
			}
		}
		
		if ($raidId > 0){
			$res_mm = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_raid_member_mm','uid_local='.$raidId);
			if ($res_mm){
				while($row_mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm)){
					$res_char = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','uid='.$row_mm['uid_foreign'].$this->cObj->enableFields('tx_naswowraidnloot_chars'));
					if ($res_char){
						$row_char = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_char);
						$select_set .= '<option value="'.$row_char['uid'].'">'.$row_char['name'].'</option>';
						if ($hline == ''){
							$hline .= $row_char['uid'];
						} else {
							$hline .= ','.$row_char['uid'];
						}
					}
				}
			}
				
		}
		
		if ($select_get != ''){
			if ($type == 'more'){
				$content .= '<span id="set_member"><select size="10" id="'.$this->prefixId.'[member]" name="'.$this->prefixId.'[member]">'.$select_set.'</select></span>';
				$content .= '<a onclick="nas_wowraidnloot_unSetMember(document.getElementById(\'tx_naswowraidnloot_pi2[member]\').value,document.getElementById(\'tx_naswowraidnloot_pi2[h_member]\').value);" href="#"><img height="14" width="14" border="0" title="Remove selected items" alt="Remove selected items" src="typo3/sysext/t3skin/icons/gfx/group_clear.gif"/></a>';
				$content .= '<select size="10" id="'.$this->prefixId.'[member_get]" name="'.$this->prefixId.'[member_get]" onclick="nas_wowraidnloot_setMember(document.getElementById(\'tx_naswowraidnloot_pi2[member_get]\').value,document.getElementById(\'tx_naswowraidnloot_pi2[member]\').innerHTML,document.getElementById(\'tx_naswowraidnloot_pi2[h_member]\').value);">'.$select_get.'</select>';
				$content .= '<input type="hidden" id="'.$this->prefixId.'[h_member]" name="'.$this->prefixId.'[h_member]" value="'.$hline.'" />';	
			} elseif ($type == 'single') {
				$select_set = '<option value="0">---</option>'.$select_set;
				$content .= '<select size="1" id="'.$this->prefixId.'[item_member]" name="'.$this->prefixId.'[item_member]" >'.$select_set.'</select>';
			}
		}

		return $content;
	}
	
	function getLeaderOptions($raidId){
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('leader','tx_naswowraidnloot_raid','uid='.$raidId);
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$leader = explode(',',$row['leader']);
		}
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','1=1'.$this->cObj->enableFields('fe_users'));
		if ($res){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$options .= '<option value="'.$row['uid'].'" ';
				if (in_array($row['uid'],$leader)){
					$options .= ' selected ';
				}
				$options .= '>'.$row['username'].'</option>';
			}
		}
		
		return $options;
	}
	
	function getDestSelect($destId = 0) {
		$content = '';
		
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
		# URL vorbereiten
		$URL = "http://eu.wowarmory.com/data/dungeonStrings.xml";
 		# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 
		curl_setopt ($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
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
				//$dungeons[intval($lair['id'])][$a] = (string)$b;
				$dungeons[(string)$lair['name']][$a] = (string)$b;
				//$markerArray['###'.strtoupper($a).'###'] = (string)$b;
		    	//$content .= $a.'="'.$b."\"<br>";
			}			
		}
		//t3lib_div::devLog('getDestSelect', $this->extKey, 0, $dungeons);
		ksort($dungeons);
		$select = '';
		foreach ($dungeons as $id => $dungeon){
			$select .= '<option value="'.$dungeon['id'].'" ';
			if ($destId == $dungeon['id']){
				$select .= 'selected';
			}
			$select .= '>'.$dungeon['name'].'</option>';
		}
		if ($select != ''){
				$content .= '<select id="'.$this->prefixId.'[dungeonId]" name="'.$this->prefixId.'[dungeonId]">'.$select.'</select>';
		}
		//t3lib_div::devLog('dungeons', $this->extKey, 0, $dungeons);
		
		return $content;
	}
	
	/***********************************************
	* XajaX Funcs
	************************************************/
	function setMember($member, $line, $hline){
	  	//t3lib_div::devLog('setMember', $this->extKey, 0, $member);
	  	$objResponse = new tx_xajax_response();
	  	
	  	$selected = explode(',',$hline);
	  	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','uid='.$member.$this->cObj->enableFields('tx_naswowraidnloot_chars'));
		if ($res){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if (!in_array($row['uid'],$selected)){
				$line .= '<option value="'.$row['uid'].'">'.$row['name'].'</option>';
				if ($hline == ''){
					$hline .= $row['uid'];
				} else {
					$hline .= ','.$row['uid'];
				}
			}
		}
		
		$content = '<select size="10" id="'.$this->prefixId.'[member]" name="'.$this->prefixId.'[member]">'.$line.'</select>';
		
		//t3lib_div::devLog('setMember line', $this->extKey, 0, $selected);
		
	  	$objResponse->addAssign("set_member","innerHTML", $content);
	  	$objResponse->addAssign($this->prefixId."[h_member]","value", $hline);

		//return the  xajaxResponse object
		return $objResponse->getXML();	
	}
	
	function unSetMember($member, $all){
	  	//t3lib_div::devLog('unSetMember member', $this->extKey, 0, $member);
	  	//t3lib_div::devLog('unSetMember all 1', $this->extKey, 0, $all);
	  	$objResponse = new tx_xajax_response();
	  	$line = '';
	  	
	  	if ($member != '' AND $member != 0){
		  	$all_array = explode(',',$all);
		  	//t3lib_div::devLog('unSetMember all', $this->extKey, 0, $all_array);
		  	foreach ($all_array as $nr => $item) {
		  		if ($item == $member){
		  			unset($all_array[$nr]);
		  		} else {
		  			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_naswowraidnloot_chars','uid='.$item);
					if ($res){
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		  				$line .= '<option value="'.$row['uid'].'">'.$row['name'].'</option>';
					}
		  		}
		  	}
		  	$all = implode(',',$all_array);
		  	
		  	$content = '<select size="10" id="'.$this->prefixId.'[member]" name="'.$this->prefixId.'[member]">'.$line.'</select>';
		  	
		  	$objResponse->addAssign("set_member","innerHTML", $content);
	  		$objResponse->addAssign($this->prefixId."[h_member]","value", $all);
	  	}
	  	//t3lib_div::devLog('unSetMember all 2', $this->extKey, 0, $all);
	  	//t3lib_div::devLog('unSetMember line', $this->extKey, 0, $line);

		//return the  xajaxResponse object
		return $objResponse->getXML();	
	}
	
	function setItems($destinationId,$boss){
		//t3lib_div::devLog('setItems', $this->extKey, 0, $destinationId.'/'.$boss);
	  	$objResponse = new tx_xajax_response();
	  	$line = '';
	  	
	  	$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
		ini_set('user_agent',$useragent); 
		header('Content-Type: text/html; charset=utf-8');
		$header[] = "Accept-Language: de-de,de;q=0.5"; 
	  	$URL = 'http://eu.wowarmory.com/search.xml?fl[source]=dungeon&fl';
	  	$URL .= '[dungeon]='.$destinationId.'&fl';
	  	$URL .= '[boss]='.$boss.'&fl[difficulty]=all&searchType=items'; 
	  	# CURL initialisieren und XML-Datei laden
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $URL);
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 		$load = curl_exec($curl);
		curl_close($curl);
		# eingelesenen String zu SimpleXMLElement umformen
		$xml = new SimpleXMLElement($load);
		
		$items = array();
		//TODO: Item-Rarity einstellbar machen.
		foreach($xml->armorySearch->searchResults->items->item as $item){
			if (intval($item['rarity'] > 2)){
				foreach($item->attributes() as $a => $b) {
					$items[(string)$item['name']][$a] = (string)$b;
				}
			}
		}
		ksort($items);
		//t3lib_div::devLog('setItems', $this->extKey, 0, $items);
		
		$select .= '<option value="0">---</option>';
		foreach ($items as $name => $item){
			$select .= '<option value="'.$item['id'].'">'.$item['name'].'</option>';
		}
		
		$content = '<select id="'.$this->prefixId.'[items]" name="'.$this->prefixId.'[items]">'.$select.'</select>';
				
		$objResponse->addAssign("loot_item","innerHTML", $content);
		
	  	return $objResponse->getXML();	
	}
	
	/***********************************************/
	
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
	
	function XAJAX_start() {
		// Make the instance
		$this->xajax = t3lib_div::makeInstance('tx_xajax');
		// Decode form vars from utf8
		$this->xajax->decodeUTF8InputOn();
		// Encoding of the response to utf-8.
		$this->xajax->setCharEncoding('utf-8');
		// To prevent conflicts, prepend the extension prefix.
		$this->xajax->setWrapperPrefix('nas_wowraidnloot_');
		// Do you want messages in the status bar?
		$this->xajax->statusMessagesOn();
	 	// Turn only on during testing
		$this->xajax->debugOff();
		// Register the names of the PHP functions you want to be able to call through xajax
		$this->xajax->registerFunction(array('setMember', &$this, 'setMember'));
		$this->xajax->registerFunction(array('unSetMember', &$this, 'unSetMember'));
		$this->xajax->registerFunction(array('setItems', &$this, 'setItems'));

		// If this is an xajax request call our registered function, send output and exit
		$this->xajax->processRequest();
		 // Else create javacript and add it to the normal output
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi2/class.tx_naswowraidnloot_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/pi2/class.tx_naswowraidnloot_pi2.php']);
}

?>