<?php

/**
 * This script records the number of hits each page in the wiki has
 * recieved in the last day/week/month/all-time.
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

class WiretapRecordPageHitCount extends Maintenance {
	
	public function __construct() {
		parent::__construct();
		
		$this->mDescription = "Count the recent hits for each page.";
		$this->addOption(
			'type',
			'Whether to record all-time count (--type=all) or period count (default, or --type=period)',
			false, // required (will assume period)
			true, // with arg @todo what does this mean?
			false // short name
		);
	}
	
	public function execute() {

		global $egWiretapCounterPeriod;

		$type = $this->getOption( 'type', false );
		if ( ! $type ) {
			$type = 'period';
		}
		else if ( ! in_array( $type, array( 'period', 'all' ) ) ) {
			$this->output( "\n \"type\" option must be set to either \"all\" or \"period\". \n" );
		}

		date_default_timezone_set("UTC"); 
		$ts = new MWTimestamp( date( 'YmdHis', strtotime( "now - $egWiretapCounterPeriod days" ) ) );

		$readConditions = array( 'page_id != 0' );

		if ( $type == 'all' ) {
			$writeTable = array( 'c' => 'wiretap_counter_alltime' );
		}
		else {
			$writeTable = array( 'c' => 'wiretap_counter_period' );
			$readConditions[] = "hit_timestamp > $ts";
		}

		$dbw = wfGetDB( DB_MASTER );

		// clear the table
		$res = $dbw->delete(
			$writeTable['c'],
			array( 'page_id > 0' ), // conditions = none; delete everything
			__METHOD__
		);

		// query wiretap table, repopulate $writeTable
		$res = $dbw->insertSelect(
			$writeTable['c'],
			array( 'w' => 'wiretap' ),
			array(
				// 'dest' => 'source'
				'page_id' => 'page_id',
				'count' => 'COUNT(*) AS total_hits',
				'count_unique' => 'COUNT( DISTINCT user_name ) AS unique_hits',
			),
			$readConditions,
			__METHOD__,
			array(), // insert options
			array( // select options
				"GROUP BY" => "page_id",
				"ORDER BY" => "page_id ASC",
			) 
		);

		$this->output( "\n Finished recording page traffic. \n" );
	}
}

$maintClass = "WiretapRecordPageHitCount";
require_once( DO_MAINTENANCE );
