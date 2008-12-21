<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_naswowraidnloot_guild", field "description"
	# ***************************************************************************************
RTE.config.tx_naswowraidnloot_guild.description {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_naswowraidnloot_chars", field "comments"
	# ***************************************************************************************
RTE.config.tx_naswowraidnloot_chars.comments {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_naswowraidnloot_pi1 = < plugin.tx_naswowraidnloot_pi1.CSS_editor
',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_naswowraidnloot_pi1.php','_pi1','list_type',1);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_naswowraidnloot_pi2 = < plugin.tx_naswowraidnloot_pi2.CSS_editor
',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_naswowraidnloot_pi2.php','_pi2','list_type',0);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_naswowraidnloot_pi3 = < plugin.tx_naswowraidnloot_pi3.CSS_editor
',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_naswowraidnloot_pi3.php','_pi3','list_type',1);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_naswowraidnloot_pi4 = < plugin.tx_naswowraidnloot_pi4.CSS_editor
',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi4/class.tx_naswowraidnloot_pi4.php','_pi4','list_type',1);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_naswowraidnloot_pi5 = < plugin.tx_naswowraidnloot_pi5.CSS_editor
',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi5/class.tx_naswowraidnloot_pi5.php','_pi5','list_type',1);
?>