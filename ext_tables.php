<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_naswowraidnloot_server"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_server',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_naswowraidnloot_server.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, continent",
	)
);

$TCA["tx_naswowraidnloot_guild"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_guild',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_naswowraidnloot_guild.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, description",
	)
);

$TCA["tx_naswowraidnloot_chars"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_naswowraidnloot_chars.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, name, server, guild, comments, armoryid, points, player",
	)
);

$TCA["tx_naswowraidnloot_collected"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_collected',		
		'label'     => 'itemid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_naswowraidnloot_collected.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, itemid, itemname, lootdate, charid, raidid, loottype",
	)
);

$TCA["tx_naswowraidnloot_raid"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_naswowraidnloot_raid.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, open, title, destinationid, start, end, member, points, pointspboss, leader",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:nas_wowraidnloot/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1','FILE:EXT:nas_wowraidnloot/pi1/flexform.xml');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Manage Characters");

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:nas_wowraidnloot/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2','FILE:EXT:nas_wowraidnloot/pi2/flexform.xml');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","Manage Raids");

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:nas_wowraidnloot/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi3/static/","Reports");

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:nas_wowraidnloot/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi4/static/","User Profile");

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi5']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:nas_wowraidnloot/locallang_db.xml:tt_content.list_type_pi5', $_EXTKEY.'_pi5'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi5/static/","Administration");
?>