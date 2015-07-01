<?php

/**
 * This script records the number of hits each page in the wiki has
 * recieved in the last day/week/month/all-time.
 *
 * Usage:
 *  no parameters @todo: add a --quick parameter (to not do a full caluculation but make a best-guess)
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
	}
	
	public function execute() {
		$recorder = new WatchStateRecorder();
		$recorder->recordAll();

		if ( $quick ) {

		}
		else {

			SELECT 

			FROM wiretap

		}


		$this->output( "\n Finished recording page traffic. \n" );
	}
}

$maintClass = "WatchAnalyticsRecordState";
require_once( DO_MAINTENANCE );
