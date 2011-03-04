<?php
/**
 * This file is part of the SnowFrame package.
 * 
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * SnowFrame Helpers
 * 
 * @package 	snowframe
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CSFHelperBase.php 388 2010-06-13 16:26:43Z jerryablan@gmail.com $
 * @since 		v1.1.0
 *  
 * @filesource
 */

class CSFHelperBase extends CPSHelperBase
{
	/**
	 * Converts an array to a string that is safe to pass via a URL
	 *
	 * @param array $array
	 * @return string
	 */
	public static function array_to_string( $array )
	{
		$retval = '';
		$null_value = "^^^";

	   if ( $array != null && is_array( $array ) )
	   {
			foreach ( $array as $index => $val )
			{
				if ( gettype( $val ) == 'array' )
					$value = '^^array^' . SF_Helpers::array_to_string( $val );
				else
					$value = $val;

				if ( !$value )
					$value = $null_value;

			   $retval .= urlencode( base64_encode( $index ) ) . '|' . urlencode( base64_encode( $value ) ) . '||';
		   }
	   }

	   return( urlencode( substr( $retval, 0, -2 ) ) );
	}

	/**
	 * Converts a string created by array_to_string() back into an array.
	 *
	 * @param string $string
	 * @return array
	 */
	public static function string_to_array( $string )
	{
		$retval = array();
		$string = urldecode( $string );
		$tmp_array = explode( '||', $string );
		$null_value = urlencode( base64_encode( "^^^" ) );

		foreach ( $tmp_array as $tmp_val )
		{
			if ( $tmp_val )
			{
				$decoded_index = $index = $value = null;
	            $arTemp = explode( '|', $tmp_val );
	            
	            if ( is_array( $arTemp ) && sizeof( $arTemp ) > 1 )
	            {
				    list( $index, $value ) = $arTemp;
					$decoded_index = base64_decode( urldecode( $index ) );

					if ( $value != $null_value )
					{
						$val = base64_decode( urldecode( $value ) );
						if ( substr( $val, 0, 8 ) == '^^array^' )
							$val = SF_Helpers::string_to_array( substr( $val, 8 ) );

						$retval[ $decoded_index ] = $val;
					}
					else
						$retval[ $decoded_index ] = NULL;
				}
			}
            else
                error_log( "bogus array: [{$tmp_val}]",0);
		}

		return( $retval );
	}

	/**
	* Retrieves an http page
	* 
	* @param mixed $sUrl
	* @param mixed $sQueryString
	* @param mixed $sUserAgent
	* @return string
	*/
	public static function getRequest( $sUrl, $sQueryString = "", $sUserAgent = "SnowFrame Object Framework PHP5 Client 1.00a (pogostick.com)" )
	{
		if ( function_exists( 'curl_init' ) )
		{
			// Use CURL if installed...
			$oConn = curl_init();
			curl_setopt( $oConn, CURLOPT_URL, $sUrl . "?" . $sQueryString  );
			curl_setopt( $oConn, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $oConn, CURLOPT_USERAGENT, $sUserAgent );
			curl_setopt( $oConn, CURLOPT_TIMEOUT, 60 );
//			$_fTime = microtime( true );
			$sResult = curl_exec( $oConn );
//			error_log( "SF_Helpers::getRequest [" . ( microtime( true ) - $_fTime ) . " secs] [" . $sUrl . '?' . $sQueryString . "]", 0 );
			curl_close( $oConn );
		}
		else
		{
			// Non-CURL based version...
			$oContext =
				array('http' =>
					array('method' => 'POST',
						'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
						'User-Agent: ' . $sUserAgent ."\r\n".
						'Content-length: ' . strlen( $sQueryString ),
						'content' => $post_string )
					);

			$oContextId = stream_context_create( $oContext );

			$oSocket = fopen( $sUrl . "?" . $sQueryString, 'r', false, $oContextId );

			if ( $oSocket )
			{
				$sResult = '';

				while ( !feof( $oSocket ) )
					$sResult .= fgets( $oSocket, 4096 );

				fclose( $oSocket );
			}
		}

		return( $sResult );
	}

	/**
	* Removes extra lines
	* 
	* @param string $string
	* @return string
	*/
	public static function trimLines( $string )
	{
		return( preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string) );
	}

	/**
	* Parse HTML field for a tag...
	* 
	* @param mixed $sData
	* @param mixed $sTag
	* @param mixed $sTagEnd
	* @param mixed $iStart
	* @param mixed $sNear
	* @return string
	*/
	public static function suckTag( $sData, $sTag, $sTagEnd, $iStart = 0, $sNear = null )
	{
		$_sResult = "";
		$_l = strlen( $sTag );

		//	If near value given, get position of that as start
		if ( $sNear != null )
		{
			$_iStart = stripos( $sData, $sNear, $iStart );
			if ( $_iStart >= 0 )
				$iStart = $_iStart + strlen( $sNear );
		}

		$_i = stripos( $sData, $sTag, $iStart );
		$_k = strlen( $sTagEnd );

		if ( $_i >= 0 )
		{
			$_j = stripos( $sData, $sTagEnd, $_i + $_l );

			if ( $_j >= 0 )
			{
				$iStart = $_i;
				$_sResult = substr( $sData, $_i + $_l,  $_j - $_i - $_l );
			}

			return( trim( $_sResult ) );
		}

		return( null );
	}

	/**
	* Produces a pager string for paging data
	* 
	* @param mixed $iPage
	* @param mixed $iLimit
	* @param mixed $iTotal
	* @param mixed $sId
	* @param mixed $sForm
	* @param mixed $sUrl
	* @param mixed $sQueryParam
	*/
	public static function getPagerString( $iPage = 1, $iLimit = 15, $iTotal = 0, $sId, $sForm, $sUrl = '/', $sQueryParam = 'p' )
	{
		$iPrev = $iPage - 1;
		$iNext = $iPage + 1;
		$iFirst = 1;
		$iLast = ceil( $iTotal / $iLimit);
		$iFirstItem = ( $iLimit * $iPage ) - $iLimit + 1;
		$iLastItem = min( $iLimit * $iPage, $iTotal );
		$sOut = '';

		$sOut .= "<li><div style=\"display:none\" id=\"{$sId}_spinner\"><img vspace=\"2\" hspace=\"2\" src=\"http://www.appbarn.com/lib/images/spin20.gif\" border=\"0\" alt=\"\" /></div></li>";

		if ( $iLast > 1 )
		{
			$sOut .= '';

			if ( $iPage > 2 ) $sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iFirst\" href=\"#\">First</a></li>";
			if ( $iPage > 1 ) $sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iPrev\" href=\"#\">Prev</a></li>";

			if ( $iPage < 4 )
			{
				for ( $iCount = 1; $iCount <= min( 5, $iLast ); $iCount++ )
				{
					if ( $iCount == $iPage )
						$sOut .= "<li class=\"current\"><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iCount\" href=\"#\">$iCount</a></li>";
					else
						$sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iCount\" href=\"#\">$iCount</a></li>";
				}
			}
			elseif ($iPage > $iLast - 3)
			{
				for ( $iCount = $iLast - min(5, $iLast); $iCount <= $iLast; $iCount++ )
				{
					if ($iCount == $iPage)
						$sOut .= "<li class=\"current\"><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iCount\" href=\"#\">$iCount</a></li>";
					else
						$sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iCount\" href=\"#\">$iCount</a></li>";
				}
			}
			else
			{
				for($iCount = $iPage - 2; $iCount <= $iPage + 2; $iCount++)
				{
					if ($iCount == $iPage)
						$sOut .= "<li class=\"current\"><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iCount\" href=\"#\">$iCount</a></li>";
					else
						$sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iCount\" href=\"#\">$iCount</a></li>";
				}
			}

			//next button
			if ($iPage < $iLast)
				$sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iNext\" href=\"#\">Next</a></li>";

			//last button
			if ($iPage < $iLast - 1)
				$sOut .= "<li><a clicktoshow=\"{$sId}_spinner\" clickrewriteid=\"{$sId}\" clickrewriteform=\"{$sForm}\" clickrewriteurl=\"$sUrl&{$sQueryParam}=$iLast\" href=\"#\">Last</a></li>";

			$sOut .= '';
		}

		$sOut .= '';
		$sOut = "$sOut";

		return( "<ul class=\"pagerpro\" id=\"pag_nav_links_{$sId}\">" . $sOut . "</ul>" );
	}

	/**
	*@desc Validate email address
	*/
	public static function isValidEmail( $sEmail = null )
	{
		return( preg_match( "/^[\d\w\/+!=#|$?%{^&}*`'~-][\d\w\/\.+!=#|$?%{^&}*`'~-]*@[A-Z0-9][A-Z0-9.-]{1,61}[A-Z0-9]\.[A-Z]{2,6}$/ix", $sEmail ) );
	}

	/**
	*@desc Get a string for db updates
	*/
	public static function dbCleanString( $sSource, $bReturnNULL = true )
	{
		if ( $sSource == null && $bReturnNULL )
			return( "null" );

		return( "'" . mysql_real_escape_string( $sSource ) . "'" );
	}
}

?>