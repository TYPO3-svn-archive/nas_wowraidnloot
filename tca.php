<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_naswowraidnloot_server"] = array (
	"ctrl" => $TCA["tx_naswowraidnloot_server"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,continent"
	),
	"feInterface" => $TCA["tx_naswowraidnloot_server"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_server.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"continent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_server.continent",		
			"config" => Array (
				"type" => "select",
				"size" => "1",	
				"minitems" => "1",
				"maxtems" => "1",
				"items" => Array(
						Array("LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_server.continent.I","eu"),
						Array("LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_server.continent.II","www"),
				)
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, continent;;;;3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_naswowraidnloot_guild"] = array (
	"ctrl" => $TCA["tx_naswowraidnloot_guild"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,description"
	),
	"feInterface" => $TCA["tx_naswowraidnloot_guild"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_guild.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_guild.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_naswowraidnloot/rte/];3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_naswowraidnloot_chars"] = array (
	"ctrl" => $TCA["tx_naswowraidnloot_chars"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,name,server,guild,comments,armoryid,points,player"
	),
	"feInterface" => $TCA["tx_naswowraidnloot_chars"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"server" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.server",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_naswowraidnloot_server",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
/*		"guild" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.guild",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_naswowraidnloot_guild",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
*/		"comments" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.comments",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"armoryid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.armoryid",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"points" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.points",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"player" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_chars.player",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, server, guild, comments;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_naswowraidnloot/rte/], armoryid, points, player")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_naswowraidnloot_collected"] = array (
	"ctrl" => $TCA["tx_naswowraidnloot_collected"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,itemid,itemname,lootdate,charid"
	),
	"feInterface" => $TCA["tx_naswowraidnloot_collected"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"itemid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_collected.itemid",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"itemname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_collected.itemname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"lootdate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_collected.lootdate",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"charid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_collected.charid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_naswowraidnloot_chars",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, itemid, itemname, lootdate, charid")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_naswowraidnloot_raid"] = array (
	"ctrl" => $TCA["tx_naswowraidnloot_raid"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,destinationid,start,end,member,points,pointspboss"
	),
	"feInterface" => $TCA["tx_naswowraidnloot_raid"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"destinationid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.destinationid",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"start" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.start",		
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"end" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.end",		
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"member" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.member",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_naswowraidnloot_chars",	
				"size" => 25,	
				"minitems" => 0,
				"maxitems" => 50,	
				"MM" => "tx_naswowraidnloot_raid_member_mm",
			)
		),
		"points" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.points",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"pointspboss" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:nas_wowraidnloot/locallang_db.xml:tx_naswowraidnloot_raid.pointspboss",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, destinationid;;;;3-3-3, start, end, member, points, pointspboss")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>