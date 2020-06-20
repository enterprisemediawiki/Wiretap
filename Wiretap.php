<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Wiretap' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Wiretap'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['WiretapAlias'] = __DIR__ . '/Wiretap.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for Wiretap extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
}
// else, use the old method of extension registry...

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

// Add wiretap view counter to bottom of page
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'Wiretap::onSkinTemplateOutputPageBeforeExec';

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
		'position' => 'bottom',
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
		'position' => 'bottom',
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
		'position' => 'bottom',
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


// for selecting a short period over which to count hits to pages
// set to 1 to count over the last day, 4 over the last 4 days, etc
$wgWiretapCounterPeriod = 30;

// use the all-time counter by default
$wgWiretapAddToAlltimeCounter = true;

// don't use the period counter by default
$wgWiretapAddToPeriodCounter = false;

// of course we want counters! why else have the extension!
$wgDisableCounters = false;
