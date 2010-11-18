<?php
//	$Id: SF_Database.php,v 1.5 2008/04/29 18:57:18 jablan Exp $
// +---------------------------------------------------------------------------+
// | Pogostick SnowFrame™ (PHP5)                                               |
// | http://www.snowframe.com                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007-2008 Pogostick, LLC.                                   |
// | All rights reserved.                                                      |
// |                                                                           |
// | This file is part of SnowFrame™.                                          |
// |                                                                           |
// | SnowFrame™ is free software: you can redistribute it and/or modify        |
// | it under the terms of the GNU General Public License as published by      |
// | the Free Software Foundation, either version 3 of the License, or         |
// | (at your option) any later version.                                       |
// |                                                                           |
// | SnowFrame™ is distributed in the hope that it will be useful,             |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | Please visit http://www.gnu.org/licenses for a copy of the license        |
// +---------------------------------------------------------------------------+

//	Require the recordset object...
require_once( "SF_Snob.php" );
require_once( "SF_Recordset.php" );

//	The database class
class SF_Database extends SF_Snob
{
	/**
	 * Enter description here...
	 *
	 * @protected unknown_type
	 */
	protected $m_dbConn = null;
	/**
	 * Enter description here...
	 *
	 * @protected unknown_type
	 */
	protected $m_dbHost = null;
	/**
	 * Enter description here...
	 *
	 * @protected unknown_type
	 */
	protected $m_dbUser = null;
	/**
	 * Enter description here...
	 *
	 * @protected unknown_type
	 */
	protected $m_dbPass = null;
	/**
	 * Enter description here...
	 *
	 * @protected unknown_type
	 */
	protected $m_dbName = null;
	/**
	*@desc Rows per page for pager
	*/
	protected $m_iPerPage = 25;
	/**
	*@desc Current page of pager
	*/
	protected $m_iCurPage = 1;
	/**
	*@desc Pager results
	*/
	protected $m_sPager = "";

	/**
	 * Constructs a database object
	 *
	 * @param unknown_type $db_host
	 * @param unknown_type $db_user
	 * @param unknown_type $db_pass
	 * @param unknown_type $db_name
	 * @param unknown_type $bOpen
	 */
	public function __construct( $db_host, $db_user, $db_pass, $db_name, $bOpen = true )
	{
		$this->m_dbConn = null;
		$this->m_dbHost = $db_host;
		$this->m_dbUser = $db_user;
		$this->m_dbPass = $db_pass;
		$this->m_dbName = $db_name;

		//	Open up if wanted
		if ( $bOpen )
		   $this->openDatabase();
	}

	/**
	*@desc Property getter
	*/
	public function __get( $sProp )
	{
		if ( $sProp == "Pager" )
			return( $this->m_sPager );
			
		//	Pass along to parent...
		return( parent::__get( $sProp ) );
	}

	/***
	*@desc Opens the database connection
	*/
	public function openDatabase()
	{
		if ( !$this->m_dbConn )
		{
			$this->m_dbConn = mysql_connect( $this->m_dbHost, $this->m_dbUser, $this->m_dbPass );

			if ( $this->m_dbConn && $this->m_dbName )
				@mysql_select_db( $this->m_dbName, $this->m_dbConn );
		}

		return( $this->m_dbConn != null );
	}

	/**
	*@desc Closes an open database connection
	*/
	public function closeDatabase()
	{
		if ( $this->m_dbConn )
			mysql_close( $this->m_dbConn );

		$this->m_dbConn = null;
	}

	/**
	 * Retrieves a single column (or null if not found) from a SQL statement
	 *
	 * @param unknown_type $sSQL
	 * @param unknown_type $sColName
	 * @return unknown
	 */
	public function getColumn( $sSQL, $sColName )
	{
		$rs = mysql_query( $sSQL, $this->m_dbConn );

		$iRows = 0;

		if ( $rs )
			$iRows = mysql_numrows( $rs );

		if ( $iRows > 0 )
			return( mysql_result( $rs, 0, $sColName ) );

		return( null );
	}

	/**
	 * Returns a recordset with an optional fetch
	 *
	 * @param unknown_type $sSQL
	 * @return unknown
	 */
	public function getRecordset( $sSQL, $bAutoFetch = true )
	{
		$rs = null;

		$this->debug( "getRecordset: " . $sSQL );
		
		$rs = mysql_query( $sSQL, $this->m_dbConn );

		if ( $rs && $bAutoFetch )
			return( mysql_fetch_array( $rs ) );

		return( $rs );
	}

	/**
	 * Executes a query and returns the number of affected rows.
	 *
	 * @param unknown_type $sSQL
	 * @return unknown
	 */
	public function execSQL( $sSQL )
	{
		$this->debug( "execSQL: " . $sSQL );
		$rs = mysql_query( $sSQL, $this->m_dbConn );
		$rc = mysql_affected_rows( $this->m_dbConn );
		return( $rc );
	}

	/**
	 * Executes a query and returns the result
	 *
	 * @param unknown_type $sSQL
	 * @return unknown
	 */
	public function execute( $sSQL )
	{
		return( $this->getRecordset( $sSQL, false ) );
	}

	/**
	 * Returns the last auto_increment value
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		return( mysql_insert_id( $this->m_dbConn ) );
	}

	/**
	*@desc Get a paged recordset returning a pager string...
	*/
	public function getPagedRecordset( $sSQL, $bFetch, $iCurPage, $sTable, $sLink, $sQueryString = "" )
	{
		//	Counting the offset
		$iOffset = ( $this->m_iCurPage - 1 ) * $this->m_iPerPage;
		$sSQL .= " limit $iOffset, {$this->m_iPerPage}";
		$rs = $this->getRecordset( $sSQL, $bFetch );

		$iCount = $this->getColumn( "select count(1) as num_rows from $sTable", "num_rows" );

		//	How many pages we have when using paging?
		$iMaxPage = ceil( $iCount / $this->m_iPerPage );

		$self = $_SERVER['PHP_SELF'];

		if ( $this->m_iCurPage > 1 )
		{
			$iPage = $this->m_iCurPage - 1;
			$sPrevLink = "page=$iPage";
			$sFirstLink = "page=1";
		}
		else
		{
			$sPrevLink = "";
			$sFirstLink = "";
		}

		if ( $this->m_iCurPage < $iMaxPage )
		{
			$iPage = $this->m_iCurPage + 1;
			$sNextLink = "page=$iPage";
			$sLastLink = "page=$iMaxPage";
		}
		else
		{
			$sNextLink = "";
			$sLastLink = "";
		}

		$sOut = "";

		if ( $sFirstLink != "" )
			$sOut .= "<a href=\"$sLink" . ( $sQueryString != "" ? $sQueryString . "&" : "?" ) . $sFirstLink . "\"><<</a>&nbsp;";
		else
			$sOut .= "<<&nbsp;";

		if ( $sPrevLink != "" )
			$sOut .= "<a href=\"$sLink" . ( $sQueryString != "" ? $sQueryString . "&" : "?" ) . $sPrevLink . "\"><</a>&nbsp;";
		else
			$sOut .= "<&nbsp;";

		$sOut .= "&nbsp;{$this->m_iCurPage} of {$iMaxPage}&nbsp;";

		if ( $sNextLink != "" )
			$sOut .= "<a href=\"$sLink" . ( $sQueryString != "" ? $sQueryString . "&" : "?" ) . $sNextLink . "\">></a>&nbsp;";
		else
			$sOut .= ">&nbsp;";

		if ( $sLastLink != "" )
			$sOut .= "<a href=\"$sLink" . ( $sQueryString != "" ? $sQueryString . "&" : "?" ) . $sLastLink . "\">>></a>&nbsp;";
		else
			$sOut .= ">>&nbsp;";

		$this->m_sPager = $sOut;

		return( $rs );
	}

	/**
	*@desc Gets a code description
	*/
	public function getCodeDescription( $lCodeUID, $sTable = "code_t" )
	{
		$rs = $this->getRecordset( "select code_desc_text from $sTable where code_uid = $lCodeUID" );
		if ( $rs )
		{
			return( $rs['code_desc_text'] );
		}

		return( '' );
	}
}
?>