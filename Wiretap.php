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
else {
	die("This version of Wiretap requires MW 1.25+");
}
