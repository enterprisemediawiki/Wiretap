<?php

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Wiretap',
	'author'=> array(
		'[https://www.mediawiki.org/wiki/User:Jamesmontalvo3 James Montalvo]'
	),
	'descriptionmsg' => 'wiretap-desc',
	'version' => 0.1,
	'url' => 'https://www.mediawiki.org/wiki/Extension:Wiretap',
);

$wgExtensionMessagesFiles['Wiretap'] = __DIR__ . '/Wiretap.i18n.php';
$GLOBALS['wgMessagesDirs']['Wiretap'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['WiretapAlias'] = __DIR__ . '/Wiretap.alias.php';

$wgAutoloadClasses['Wiretap'] = __DIR__ . '/Wiretap.body.php'; // autoload body class
$wgAutoloadClasses['SpecialWiretap'] = __DIR__ . '/SpecialWiretap.php'; // autoload special page class

$wgSpecialPages['Wiretap'] = 'SpecialWiretap'; // register special page

// $wgHooks['ParserAfterTidy'][] = 'Wiretap::updateTable';
// $wgHooks['BeforePageDisplay'][] = 'Wiretap::updateTable';

// collects wiretap info from hook that provides necessary inputs
// but does not record the information in the database
$wgHooks['BeforeInitialize'][] = 'Wiretap::updateTable';

// records the information at the latest possible time in order to
// record the length of time required to build the page.
$wgHooks['AfterFinalPageOutput'][] = 'Wiretap::recordInDatabase';

// update database (using maintenance/update.php)
$wgHooks['LoadExtensionSchemaUpdates'][] = 'Wiretap::updateDatabase';



$wiretapResourceTemplate = array(
	'localBasePath' => __DIR__ . '/modules',
	'remoteExtPath' => 'Wiretap/modules',
);

$wgResourceModules += array(

	// 'ext.wiretap.base' => $watchAnalyticsResourceTemplate + array(
	// 	'styles' => 'base/ext.wiretap.base.css',
	// ),

	'ext.wiretap.charts' => $wiretapResourceTemplate + array(
		'styles' => 'charts/ext.wiretap.charts.css',
		'scripts' => array(
			'charts/Chart.js',
			'charts/ext.wiretap.charts.js',
		),
		// 'messages' => array(
		// 	'watchanalytics-pause-visualization',
		// 	'watchanalytics-unpause-visualization',
		// ),
		// 'dependencies' => array(
		// 	'base',
		// ),

	),

	'ext.wiretap.d3.js' => $wiretapResourceTemplate + array(
		'scripts' => array(
			'd3js/ext.wiretap.d3.js',
		),
		// 'messages' => array(
		// 	'watchanalytics-pause-visualization',
		// 	'watchanalytics-unpause-visualization',
		// ),
		// 'dependencies' => array(
		// 	'base',
		// ),

	),

	'ext.wiretap.charts.nvd3' => $wiretapResourceTemplate + array(
		'styles' => array(
			'nvd3js/nv.d3.css',
			'nvd3js/ext.wiretap.nvd3.css',
		),
		'scripts' => array(
			'nvd3js/nv.d3.js',
			'nvd3js/ext.wiretap.nvd3.js',
		),
		// 'messages' => array(
		// 	'watchanalytics-pause-visualization',
		// 	'watchanalytics-unpause-visualization',
		// ),
		'dependencies' => array(
			'ext.wiretap.d3.js',
		),

	),

);
