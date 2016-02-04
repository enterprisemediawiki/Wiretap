<?php

/**
 * This script records the number of hits each page in the wiki had in the
 * legacy hit counter (pre MW 1.25) in the `page` table in the `page_counter`
 * column **prior** to the installation of Wiretap. This record may not be
 * perfect, since Wiretap and the legacy page counter counted pages slightly
 * differently. One major difference is that all views of redirect pages are
 * attributed to their target pages.
 *
 * Usage:
 *  --type: whether to record all time or hits in a period
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @author James Montalvo
 * @ingroup Maintenance
 */

// @todo: does this always work if extensions are not in $IP/extensions ??
// this was what was done by SMW
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../..';
require_once $basePath . '/maintenance/Maintenance.php';

class WiretapRecordLegacyHitCount extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->mDescription = "Count the legacy hits for each page.";

	}

	public function execute() {

		$dbw = wfGetDB( DB_MASTER );

		if ( ! $dbw->fieldExists( 'page', 'page_counter', __METHOD__ ) ) {
			$this->output(
				"\n* * * * * * * * * * * * * * * * * * * * * * * * * * * * * *"
				"\n* The `page` table does not have a field `page_counter`.  *" .
				"\n* This means you have upgraded to MW 1.25 or beyond.      *" .
				"\n* You can no longer record legacy view totals.            *" .
				"\n* * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n"
			);
			return false;
		}

		// clear the table
		$res = $dbw->delete(
			'wiretap_legacy',
			null, // conditions = none; delete everything
			__METHOD__
		);

		// get actual pages (non-redirects)
		$targetPages = $dbw->query( $this->getLegacyPageCounterQuery( false ) );

		$legacyCounter = array();
		while( $p = $targetPages->fetchObject() ) {
			$legacyCounter[ $p->id ] = $p->pre_wiretap_count;
		}
		unset( $targetPages );

		// get redirects
		$redirects = $dbw->query( $this->getLegacyPageCounterQuery( true ) );


		while( $r = $redirects->fetchObject() ) {

			// get the target page ID of this redirect. The function below will follow
			// multiple redirects to the final target (hopefully there are no circular
			// redirects!)
			$targetPageID = self::getRedirectTargetID( $r->id );

			// add this redirect's pre_wiretap_count to the target pages
			$legacyCounter[ $targetPageID ] += $r->pre_wiretap_count;
		}

		$arrayForDatabase = array();
		foreach( $legacyCounter as $id => $count ) {
			$arrayForDatabase[] = array(
				'legacy_id' => $id,
				'legacy_counter' => $count
			);
		}
		unset( $legacyCounter );

		$success = $dbw->insert(
			'wiretap_legacy',
			$arrayForDatabase,
			__METHOD__
		);

		if ( $success ) {
			$numPages = $dbw->affectedRows();
			$this->output( "\n Legacy page views recorded for $numPages pages. \n" );

		}
		else {
			$this->output( "\n Failure to insert rows \n" );
		}

	}

	static protected function getRedirectTargetID ( $id ) {
		$redirect = WikiPage::newFromID( $id );
		if ( ! $redirect ) {
			return 0;
		}
		$target = $redirect->getRedirectTarget();
		if ( ! $target ) {
			return 0;
		}

		if ( $target->isRedirect() ) {
			return self::getRedirectTargetID( $target->getArticleID() );
		}

		return $target->getArticleID();
	}

	protected function getLegacyPageCounterQuery ( $redirects=false ) {

		if ( $redirects ) {
			$redirects = '1';
		}
		else {
			$redirects = '0';
		}

		$query =
			"SELECT
			    p.page_id AS id,
			    IFNULL( p.page_counter, 0 ) - IFNULL( tmp.wiretap_counter, 0 ) AS pre_wiretap_count
			FROM page AS p
			JOIN (
			    SELECT
			        wiretap.page_id,
			        COUNT(*) AS wiretap_counter
			    FROM wiretap
			    WHERE
				 	page_id > 0
					AND page_action IS NULL
			    GROUP BY page_id
			) AS tmp ON p.page_id = tmp.page_id
			WHERE
				p.page_is_redirect = $redirects";

	}
}

$maintClass = "WiretapRecordLegacyHitCount";
require_once( DO_MAINTENANCE );
