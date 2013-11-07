<?php

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Wiretap',
	'author'=> array( 'James Montalvo' ),
	'descriptionmsg' => 'wiretap-desc',
	'version' => 0.1,
	'url' => 'https://www.mediawiki.org/wiki/Extension:Wiretap',
);

$wgExtensionMessagesFiles['Wiretap'] = __DIR__ . '/Wiretap.i18n.php';
$wgExtensionMessagesFiles['WiretapAlias'] = __DIR__ . '/Wiretap.alias.php';

$wgAutoloadClasses['SpecialWiretap'] = __DIR__ . '/SpecialWiretap.php';

$wgSpecialPages['Wiretap'] = 'SpecialWiretap';

$wgHooks['ParserAfterTidy'][] = 'Wiretap::updateTable';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'Wiretap::updateDatabase';