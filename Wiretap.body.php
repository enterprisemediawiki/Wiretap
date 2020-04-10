<?php

class Wiretap {

	static $referers = null;
	static $called = false;

	/**
	 *
	 *
	 *
	 **/
	public static function updateTable( &$title, &$article, &$output, &$user, $request, $mediaWiki ) {

		$output->enableClientCache( false );
		$output->addMeta( 'http:Pragma', 'no-cache' );

		global $wgRequestTime, $egWiretapCurrentHit;

		$now = time();
		$hit = array(
			'page_id' => $title->getArticleID(),
			'page_name' => $title->getFullText(),
			'user_name' => $user->getName(),
			'hit_timestamp' => wfTimestampNow(),

			'hit_year' => date('Y',$now),
			'hit_month' => date('m',$now),
			'hit_day' => date('d',$now),
			'hit_hour' => date('H',$now),
			'hit_weekday' => date('w',$now), // 0' => sunday, 1=monday, ... , 6=saturday

			'page_action' => $request->getVal( 'action' ),
			'oldid' => $request->getVal( 'oldid' ),
			'diff' => $request->getVal( 'diff' ),

		);

		$hit['referer_url'] = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
		$hit['referer_title'] = self::getRefererTitleText( $request->getVal('refererpage') );

		// @TODO: this is by no means the ideal way to do this...but it'll do for now...
		$egWiretapCurrentHit = $hit;

		return true;

	}

	public static function recordInDatabase (  ) { // could have param &$output
		global $wgRequestTime, $egWiretapCurrentHit, $wgReadOnly;

		if ( $wgReadOnly || ! isset( $egWiretapCurrentHit ) || ! isset( $egWiretapCurrentHit['page_id'] ) ) {
			return true; // for whatever reason the poorly-named "updateTable" method was not called; abort.
		}

		// calculate response time now, in the last hook (that I know of).
		$egWiretapCurrentHit['response_time'] = round( ( microtime( true ) - $wgRequestTime ) * 1000 );

		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'wiretap',
			$egWiretapCurrentHit,
			__METHOD__
		);

		global $wgWiretapAddToPeriodCounter, $wgWiretapAddToAlltimeCounter;

		if ( $wgWiretapAddToAlltimeCounter ) {
			self::upsertHit( $egWiretapCurrentHit['page_id'], 'all' );
		}

		if ( $wgWiretapAddToPeriodCounter ) {
			self::upsertHit( $egWiretapCurrentHit['page_id'], 'period' );
		}

		return true;
	}

	public static function upsertHit ( $pageId, $type='all' ) {

		if ( $type === 'period' ) {
			$table = 'wiretap_counter_period';
		}
		else if ( $type === 'all' ) {
			$table = 'wiretap_counter_alltime';
		}
		else return;

		$dbw = wfGetDB( DB_MASTER );
		$dbw->upsert(
			$table,
			array(
				'page_id' => $pageId,
				'count' => 1,
				'count_unique' => 1,
			),
			array( 'page_id' ),
			array(
				'count = count + 1',
				// does not guess this is a new unique hit
				// need to run maint script for that
				// 'count_unique = count_unique + 1',
			),
			__METHOD__
		);

		return;

	}

	public static function updateDatabase( DatabaseUpdater $updater ) {
		global $wgDBprefix;

		$wiretapTable = $wgDBprefix . 'wiretap';
		$wiretapCounterTable = $wgDBprefix . 'wiretap_counter_period';
		$wiretapLegacyTable = $wgDBprefix . 'wiretap_legacy';
		$schemaDir = __DIR__ . '/schema';

		$updater->addExtensionTable(
			$wiretapTable,
			"$schemaDir/Wiretap.sql"
		);
		$updater->addExtensionField(
			$wiretapTable,
			'response_time',
			"$schemaDir/patch-1-response-time.sql"
		);
		$updater->addExtensionTable(
			$wiretapCounterTable,
			"$schemaDir/patch-2-page-counter.sql"
		);
		$updater->addExtensionTable(
			$wiretapLegacyTable,
			"$schemaDir/patch-3-legacy-counter.sql"
		);
		return true;
	}

	/**
	 *	See WebRequest::getPathInfo() for ideas/info
	 *  Make better use of: $wgScript, $wgScriptPath, $wgArticlePath;
	 *
	 *  Other recommendations:
	 *	 wfSuppressWarnings();
	 *	 $a = parse_url( $url );
	 *	 wfRestoreWarnings();
	 **/
	public static function getRefererTitleText ( $refererpage=null ) {

		// global $egWiretapReferers;
		global $wgScriptPath;

		if ( $refererpage )
			return $refererpage;
		else if ( ! isset($_SERVER["HTTP_REFERER"]) )
			return null;

		$wikiBaseUrl = WebRequest::detectProtocol() . '://' . $_SERVER['HTTP_HOST'] . $wgScriptPath;

		// if referer URL starts
		if ( strpos($_SERVER["HTTP_REFERER"], $wikiBaseUrl) === 0 ) {

			$questPos = strpos( $_SERVER['HTTP_REFERER'], '?' );
			$hashPos = strpos( $_SERVER['HTTP_REFERER'], '#' );

			if ($hashPos !== false) {
				$queryStringLength = $hashPos - $questPos;
				$queryString = substr($_SERVER['HTTP_REFERER'], $questPos+1, $queryStringLength);
			} else {
				$queryString = substr($_SERVER['HTTP_REFERER'], $questPos+1);
			}

			$query = array();
			parse_str( $queryString, $query );

			return isset($query['title']) ? $query['title'] : false;

		}
		else
			return false;

	}

	// taken from Extension:HitCounter, which I think took it from MW core pre 1.25
	public static function onSkinTemplateOutputPageBeforeExec( SkinTemplate &$skin, QuickTemplate &$tpl ) {
		global $wgDisableCounters;

		/* Without this check two lines are added to the page. */
		if ( self::$called ) {
			return;
		}
		self::$called = true;

		if ( ! $wgDisableCounters ) {
			$footer = $tpl->get( 'footerlinks' );
			if ( isset( $footer['info'] )
				&& is_array( $footer['info'] )
				&& ! in_array( 'viewcount', $footer['info'] )
			) {
				// 'viewcount' goes after 'lastmod', we'll just assume
				// 'viewcount' is the 0th item
				array_splice( $footer['info'], 1, 0, 'viewcount' );
				$tpl->set( 'footerlinks', $footer );
			}

			$viewcount = Wiretap::getCount( $skin->getTitle() );
			if ( $viewcount ) {
				wfDebugLog(
					"Wiretap",
					"Got viewcount and putting in page"
				);
				$tpl->set(
					'viewcount',
					$skin->msg( 'wiretap-viewcount' )->numParams(
						$viewcount->page + $viewcount->redirect,
						$viewcount->redirect
					)->parse()
				);
			}
		}
	}

	// eventually add a $period param allowing to specify a
	static public function getCount ( Title $title ) {

		$counts = (object)array( 'page' => 0, 'redirect' => 0 );

		$id = $title->getArticleID();
		if ( ! $id ) {
			return $counts;
		}

		$findIDs = array( $id );
		$redirects = $title->getRedirectsHere();
		foreach( $redirects as $r ) {
			$findIDs[] = $r->getArticleID();
		}

		$dbr = wfGetDB( DB_REPLICA );
		$result = $dbr->select(
			array(
				'w' => 'wiretap_counter_alltime',
				'leg' => 'wiretap_legacy'
			),
			array(
				'id' => 'w.page_id',
				'legacy_counter' => 'legacy_counter',
				'wiretap_counter' => 'w.count'
			),
			array( 'w.page_id' => $findIDs ),
			__METHOD__,
			null,
			array(
				'leg' => array( 'LEFT JOIN', 'leg.legacy_id = w.page_id' )
			)
		);

		// $pageHits = 0;
		// $redirectHits = 0;
		while( $page = $result->fetchObject() ) {
			$total = intval( $page->legacy_counter ) + intval( $page->wiretap_counter );
			if ( $page->id == $id ) {
				$counts->page = $total;
			}
			else {
				$counts->redirect += $total;
			}
		}

		return $counts;
	}

}
