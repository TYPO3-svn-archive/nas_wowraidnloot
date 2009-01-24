<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Nadine Schwingler <naddy@schattenhandel.de>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:nas_wowraidnloot/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'RnL-Importer' for the 'nas_wowraidnloot' extension.
 *
 * @author	Nadine Schwingler <naddy@schattenhandel.de>
 * @package	TYPO3
 * @subpackage	tx_naswowraidnloot
 */
class  tx_naswowraidnloot_module1 extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					/*
					if (t3lib_div::_GP('clear_all_cache'))	{
						$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
					}
					*/
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function_import1'),
							//'2' => $LANG->getLL('function2'),
							//'3' => $LANG->getLL('function3'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="POST">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
					global $LANG;
					$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nas_wowraidnloot']);
					t3lib_div::devLog('extConf', 'rnl mod1', 0, $this->extConf);
										
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content = $this->basicImport();
							$this->content.=$this->doc->section($LANG->getLL('function_import1').':',$content,0,1);
						break;
						case 2:
							$content='<div align=center><strong>Menu item #2...</strong></div>';
							$this->content.=$this->doc->section('Message #2:',$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content.=$this->doc->section('Message #3:',$content,0,1);
						break;
					}
				}
				
				function basicImport(){
					$content = '';

					if ($_POST['import_stuff']){
						$content .= $this->makeImport();
					} else {
						$content .= $this->getBasicImportForm();
					}
					
					return $content;
				}
				
				function makeImport(){
					global $LANG;
					$content = '';
					
					$content .= $LANG->getLL('imported_header').'<br />';
					if ($_POST['import_dungeons'] == 1){
						$dungeons = $this->getDungeonsFA();
						//t3lib_div::devLog('dungeons', 'rnl mod1', 0, $dungeons);
						$content .= '- '.$LANG->getLL('imported_dungeons').' ('.count($dungeons).')<br />';
						if (is_array($dungeons)){
							foreach ($dungeons as $id => $dungeon){
								$saveValues = array();
								$saveValues['pid'] = $this->extConf['dungeons_pid'];
								$saveValues['uid'] = $dungeon['id'];
								$saveValues['name'] = $dungeon['name'];
								$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_dungeons',$saveValues);
							}
						}
					}
					$bosses = $this->getBossesFA();
					//t3lib_div::devLog('bosses', 'rnl mod1', 0, $bosses);
					if ($_POST['import_bosses'] == 1){
						$content .= '- '.$LANG->getLL('imported_bosses').' ('.count($bosses).')<br />';
						if (is_array($bosses)){
							foreach ($bosses as $id => $boss){
								$saveValues = array();
								$saveValues['pid'] = $this->extConf['bosses_pid'];
								$saveValues['uid'] = $boss['id'];
								$saveValues['name'] = $boss['name'];
								$saveValues['dungeonid'] = $boss['dungeonid'];
								$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_bosses',$saveValues);
							}
						}
					}
					
					if ($_POST['import_bossitems'] == 1){
						foreach ($bosses as $id => $boss){
							$bossitems = $this->getBossitemsFA($boss);
							//t3lib_div::devLog('bossitems', 'rnl mod1', 0, $bossitems);
							$content .= '- '.$LANG->getLL('imported_bossitems').' ('.$boss['name'].'/'.count($bossitems).')<br />';
							if (is_array($bossitems)){
								foreach ($bossitems as $id => $item){
									$saveValues = array();
									$saveValues['pid'] = $this->extConf['bossitems_pid'];
									$saveValues['uid'] = $item['id'];
									$saveValues['name'] = $item['name'];
									$saveValues['bossid'] = $boss['id'];
									$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_naswowraidnloot_bossItems',$saveValues);
								}
							} elseif ($bossitems == 614) {
								$content .= '<span style="color:red;font-weight:bold;font-size:15px;">'.$LANG->getLL('armory_error614').'</span>';
								return $content;
							}	
						}
					}
					

					return $content;
				}
				
				function getBossitemsFA($boss){
					$items = array();
					
					$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
					ini_set('user_agent',$useragent); 
					header('Content-Type: text/html; charset=utf-8');
					$header[] = "Accept-Language: ".$this->extConf['import_lang'].";q=0.5"; 
					//foreach ($bosses as $id => $boss){
						$URL = 'http://eu.wowarmory.com/search.xml?fl[source]=dungeon&fl';
						$URL .= '[dungeon]='.$boss['dungeonid'].'&fl';
					  	$URL .= '[boss]='.$boss['id'].'&fl[difficulty]=all&searchType=items';
					  	//t3lib_div::devLog('URL', 'rnl mod1', 0, $URL);
					  	# CURL initialisieren und XML-Datei laden
						$curl = curl_init();
				 		
						curl_setopt($curl, CURLOPT_URL, $URL);
						curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
						curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				 		
						$load = curl_exec($curl);
						curl_close($curl);
						# eingelesenen String zu SimpleXMLElement umformen
						$sleng = strlen($load);
						//t3lib_div::devLog('sleng', 'rnl mod1', 0, $sleng);
						//t3lib_div::devLog('load', 'rnl mod1', 0, $load);
						 
						# Pruefen ob online / offline mittels Laenge
						if($sleng >= 4000) {
							$xml = new SimpleXMLElement($load);
									
							//TODO: Item-Rarity einstellbar machen.
							foreach($xml->armorySearch->searchResults->items->item as $item){
								if (intval($item['rarity'] > 2)){
									foreach($item->attributes() as $a => $b) {
										$items[intval($item['id'])][$a] = (string)$b;
									}
								}
							}
						} else {
							$items = $sleng;
						}
					//}
							
					return $items;	
				}
				
				function getBossesFA(){
					$bosses = array();
					$type = $_POST['import_release'];
					
					$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
					ini_set('user_agent',$useragent); 
					header('Content-Type: text/html; charset=utf-8');
					$header[] = "Accept-Language: ".$this->extConf['import_lang'].";q=0.5"; 
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
					
					# eingelesenen String zu SimpleXMLElement umformen
					$xml = new SimpleXMLElement($load);
					
					# Namen und IDs der Dungeons ausgeben
					foreach ($xml->dungeons->dungeon as $lair){
						if ($lair['release'] == $type){
							foreach($lair->boss as $boss){
								foreach($boss->attributes() as $a => $b) {
									$bosses[intval($boss['id'])][$a] = (string)$b;
									$bosses[intval($boss['id'])]['dungeonid'] = intval($lair['id']);
								}
							}
						}
					}
					
					return $bosses;
				}
				
				function getDungeonsFA(){
					$dungeons = array();
					$type = $_POST['import_release'];
					
					$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; de-DE; rv:1.6)Gecko/20040206 Firefox/1.0.1"; 
					ini_set('user_agent',$useragent); 
					header('Content-Type: text/html; charset=utf-8');
					$header[] = "Accept-Language: ".$this->extConf['import_lang'].";q=0.5"; 
					# URL vorbereiten
					$URL = $this->extConf['import_link']."data/dungeonStrings.xml";
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
					
					# Namen und IDs der Dungeons ausgeben
					$dungeons = array();
					foreach ($xml->dungeons->dungeon as $lair){
						if ($lair['release'] == $type){
							foreach($lair->attributes() as $a => $b) {
								$dungeons[intval($lair['id'])][$a] = (string)$b;
							}
						}			
					}
					
					return $dungeons;
				}

				function getBasicImportForm(){
					global $LANG;
					$content = '';
					
					$markerArray = array();
					$markerArray['###ARMORY_LINK_LABEL###'] = $LANG->getLL('armory_link_label');
					$markerArray['###ARMORY_LINK###'] = $this->extConf['import_link'];
					
					$markerArray['###IMPORT_WHAT###'] = $LANG->getLL('import_what');
					$markerArray['###IMPORT_RELEASE###'] = $LANG->getLL('import_release');
					$markerArray['###IMPORT_DUNGEONS###'] = $LANG->getLL('import_dungeons');
					$markerArray['###IMPORT_BOSSES###'] = $LANG->getLL('import_bosses');
					$markerArray['###IMPORT_BOSSITEMS###'] = $LANG->getLL('import_bossitems');
					$markerArray['###IMPORT_BUTTON###'] = $LANG->getLL('import_button');
					
					$templ = file_get_contents('res/basicImport.html');
					$content .= $this->getCSS();
					$content .= $this->fillTemplate($templ, $markerArray);
					
					return $content;
				}
				
				function fillTemplate ($templ, $markerArray){
					$content = $templ;
					
					if (is_array($markerArray)){
						foreach ($markerArray as $marker => $value){
							$content = str_replace($marker,$value,$content);	
						}
					}
			
					return $content;
				}
				
				function getCSS(){
					$content = '<style type="text/css">';
					$content .= '.raidnloot_import {width:800px;}';
					$content .= '</style>';
					return $content;
				}
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nas_wowraidnloot/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_naswowraidnloot_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);


$SOBE->main();
$SOBE->printContent();

?>