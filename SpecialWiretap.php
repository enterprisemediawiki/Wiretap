<?php

class SpecialWiretap extends SpecialPage {

	public function __construct() {
		parent::__construct( 
			"Wiretap", // 
			"",  // rights required to view
			true // show in Special:SpecialPages
		);
	}
	
	function execute( $parser = null ) {
		global $wgRequest, $wgOut, $wgUser;

		$wgOut->setPageTitle( 'Special Wire Tap' );

		list( $limit, $offset ) = wfCheckLimits();

		// $userTarget = isset( $parser ) ? $parser : $wgRequest->getVal( 'username' );

		// $pager = new UserPageViewTrackerPager( $wgUser );
		// $form = $pager->getForm();
		// $body = $pager->getBody();
		// $html = $form;
		// if ( $body ) {
			// $html .= $pager->getNavigationBar();
			// $html .= '<table class="wikitable" width="100%" cellspacing="0" cellpadding="0">';
			// $html .= '<tr><th>Username</th><th>Page</th><th>Views</th><th>Last</th></tr>';
			// $html .= $body;
			// $html .= '</table>';
			// $html .= $pager->getNavigationBar();
		// } else {
			// $html .= '<p>' . wfMsgHTML('listusers-noresult') . '</p>';
		// }
		// $wgOut->addHTML( $html );
	
		$wgOut->addHTML( "this is a test" );
	}
}

class WiretapPager extends IndexPager {
	protected $rowCount = 0;

	function __construct( $username = null ) {
		parent::__construct();
		global $wgRequest;
		$this->filterUsers = $wgRequest->getVal( 'filterusers' );
		$this->filterUserList = explode("|", $this->filterUsers);
		$this->ignoreUsers = $wgRequest->getVal( 'ignoreusers' );
		$this->ignoreUserList = explode("|", $this->ignoreUsers);
	}

	// Implementing remaining abstract method
	function getIndexField() {
		return "rownum";
	}

	function getNavigationBar() {
		return "";
	}
	
	function getQueryInfo() {
		global $wgDBprefix;
		$conds = array();
		if ( $this->filterUsers ) {
			$includeUsers = "user_name in ( '";
			$includeUsers .= implode( "', '", $this->filterUserList ) . "')";
			$conds[] = $includeUsers;
		}
		if ( $this->ignoreUsers ) {
			$excludeUsers = "user_name not in ( '";
			$excludeUsers .= implode( "', '", $this->ignoreUserList ) . "')";
			$conds[] = $excludeUsers;
		}
		return array(
			'tables' => $wgDBprefix.'wiretap',
			'fields' => array( 
				'page_id',
				'page_name',
				'user_name',
				"concat(substr(hit_timestamp, 1, 4),'-',substr(hit_timestamp,5,2),'-',substr(hit_timestamp,7,2),' ',substr(hit_timestamp,9,2),':',substr(hit_timestamp,11,2),':',substr(hit_timestamp,13,2)) AS hit_timestamp"
			),
			'conds' => $conds
		);
	}

	function formatRow( $row ) {
		$userPage = Title::makeTitle( NS_USER, $row->user_name );
		$name = $this->getSkin()->makeLinkObj( $userPage, htmlspecialchars( $userPage->getText() ) );
		$pageTitle = Title::makeTitle( $row->page_namespace, $row->page_title );
		if ( $row->page_namespace > 0 ) {
			$pageFullName = $pageTitle->getNsText() . ':' . htmlspecialchars( $pageTitle->getText() );
		} else {
			$pageFullName = htmlspecialchars( $pageTitle->getText() );
		}
		$page = $this->getSkin()->makeLinkObj( $pageTitle, $pageFullName );

		$res = '<tr>';
		$res .= '<td>' . $name . '</td><td>';
		$res .= "$page</td>";
		$res .= '<td style="text-align:right">' . $row->hits . '</td>';
		$res .= '<td style="text-align:center">' . $row->last . '</td>';
		$res .= "</tr>\n";
		return $res;
	}

	function getBody() {
		if ( ! $this->mQueryDone ) {
			$this->doQuery();
		}
		$batch = new LinkBatch;
		$db = $this->mDb;
		$this->mResult->rewind();
		$this->rowCount = 0;
		while ( $row = $this->mResult->fetchObject() ) {
			$batch->addObj( Title::makeTitleSafe( NS_USER, $row->user_name ) );
		}
		$batch->execute();
		$this->mResult->rewind();
		return parent::getBody();
	}

	function getForm() {
		$out = '<form name="filteruser" id="filteruser" method="post">';
		$out .='Usernames: <input type="text" name="filterusers" value="' . $this->filterUsers . '">';
		$out .='<input type="submit" value="Filter">';
		$out .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$out .='Usernames: <input type="text" name="ignoreusers" value="' . $this->ignoreUsers . '">';
		$out .='<input type="submit" value="Exclude">';
		$out .='</form><br /><hr /><br />';
		return $out;
	}

	/**
	 * Preserve filter offset parameters when paging
	 * @return array
	 */
	function getDefaultQuery() {
		$query = parent::getDefaultQuery();
		if( $this->filterUsers != '' )
			$query['filterusers'] = $this->filterUsers;
		if( $this->ignoreUsers != '' )
			$query['ignoreusers'] = $this->ignoreUsers;
		return $query;
	}
}