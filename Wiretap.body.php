<?php

class Wiretap {

	public static function updateTable( &$parser, &$text ) {
		global $wgUser, $wgOut;

		return true; // don't do anything for now.
		
		$wgOut->enableClientCache( false );
		$wgOut->addMeta( 'http:Pragma', 'no-cache' );

		$dbw = wfGetDB( DB_MASTER );
		$user_id = $wgUser->getID();
		$page_id = $parser->getTitle()->getArticleID();
		$hits = 0;
		$last = wfTimestampNow();

		$result = $dbw->select( 'user_page_views', array('hits','last'), "user_id = $user_id AND page_id = $page_id", __METHOD__ );
		if ( $row = $result->fetchRow() ) {
			$hits = $row['hits'];
			$last = $row['last'];
		}
		$dbw->upsert(
			'user_page_views',
			array( 'user_id' => $user_id, 'page_id' => $page_id, 'hits' => $hits + 1, 'last' => $last ),
			array( 'user_id', 'page_id' ),
			array( 'user_id' => $user_id, 'page_id' => $page_id, 'hits' => $hits + 1, 'last' => $last ),
			__METHOD__
		);
		return true;
	}

	public static function updateDatabase( DatabaseUpdater $updater ) {
		global $wgDBprefix;
		die('fixme: table names'); 
		$updater->addExtensionTable( $wgDBprefix . 'user_page_views', __DIR__ . '/Wiretap.sql' );
		$updater->addExtensionTable( $wgDBprefix . 'user_page_hits', __DIR__ . '/Wiretap.sql' );
		return true;
	}

}