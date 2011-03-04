<?php
/**
 * CPSOpenCalais.php
 * 
 * Copyright (c) 2009 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * This file is part of Pogostick : Yii Extensions.
 * 
 * We share the same open source ideals as does the jQuery team, and
 * we love them so much we like to quote their license statement:
 * 
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 * 
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 * 
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 * 
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * CPSOpenCalais main class file
 * Provides helper methods to easily access the {@link http://www.opencalais.com/ Open Calais APIs}.
 *
 * @package 	psYiiExtensions
 * @subpackage 	components.openCalais
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @since 		v1.0.3
 *
 * @filesource
 */
class CPSOpenCalais extends CPSApiComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Communication methods with the OC Api
	 */
	const SOAP = 0;
	const REST = 1;
	const HTML = 2;

	//********************************************************************************
	//* Class Members
	//********************************************************************************

	/***
	* Open Calais API Key
	 *
	* @var string
	*/
	public $apiKey = null;

	/***
	* Open Calais API Url
	*
	* @var string
	*/
	public $soapUrl = 'http://api.opencalais.com/enlighten/?wsdl';
	/***
	* Content type of the request
	*
	* @var string
	*/
	public $contentType = 'text/txt';
	/***
	* Output format of the response
	*
	* @var string
	*/
	public $outputFormat = 'XML/RDF';
	/**
	* Base URL to be put in rel-tag microformats
	*
	* @var string
	*/
	public $reltagBaseUrl = '';
	/**
	* Indicates whether the extracted metadata will include relevance score for each unique entity
	*
	* @var string
	*/
	public $calculateRelevanceScore = 'true';
	/**
	* Indicates whether the output (RDF only) will include Generic Relation extractions
	*
	* @var string
	*/
	public $enableMetadataType = 'false';
	/**
	* Indicates whether the output will exclude Entity Disambiguation results
	*
	* @var string true or false
	*/
	public $discardMetadata = 'false';
	/***
	* Indicates whether the extracted metadata can be distributed
	*
	* @var string true or false
	*/
	public $allowDistribution = 'false';
	/***
	* Indicates whether future searches can be performed on the extracted metadata
	*
	* @var string true or false
	*/
	public $allowSearch = 'false';
	/***
	* User-generated ID for the submission
	*
	* @var string
	*/
	public $externalID = '';
	/***
	* Identifier for the content submitter
	*
	* @var string
	*/
	public $submitter = '';
	/***
	* Exclude the original body from the returned RDF output
	*
	* @var string true or false
	*/
	public $omitOutputtingOriginalText = 'true';
	
	/**
	 * How we talk to the OC
	 * @var int
	 */
	public $requestMethod = self::SOAP;

	//********************************************************************************
	//* Class Methods
	//********************************************************************************

	/**
	* Calls through SemanticProxy to get a JSON encoded result set of tags.
	*
	* @param string $url
	*/
	public function semanticProxy( $url )
	{
		$_data = null;

		//	Make the call...
		if ( $_results = PS::makeHttpRequest( 'http://service.semanticproxy.com/processurl/' . $this->apiKey . '/json/' . $url ) )
			$_data = json_decode( $_results, true );

		//	No go? Default to soap
		if ( $_data === null )
		{
			//	Let's try and make a soap call...
			$_sOldOF = $this->outputFormat;
			$this->outputFormat = 'application/json';

			try
			{
				$_oResult = $this->enlighten( '', $url, true );
				$_data = json_decode( $_oResult->EnlightenResult, true );
			}
			catch ( Exception $_ex )
			{
				$this->outputFormat = $_sOldOF;
				Yii::log( "Error getting SOAP request after failed SemanticProxy: " . $_ex->getMessage(), 'error', 'system.components.COpenCalaisApi' );
				return( null );
			}

			$this->outputFormat = $_sOldOF;
		}

//		Yii::log( var_export( $_data, true ), 'info', 'system.components.COpenCalaisApi' );

		//	Return
		return $_data;
	}

	/***
	* Calls the Open Calais API via SOAP and returns the results.
	*
	*/
	public function enlighten( $sExternalID, $sContent, $bFetch = false )
	{
		//	Default content...
		$_sContent = $sContent;

		//	Override external id...
		if ( $sExternalID != null )
			$this->externalID = $sExternalID;

		//	Get content if we are passing in an url...
		if ( $bFetch )
		{
			$this->contentType = 'text/html';
			$_iTryCount = 0;
			$_sDeepLink = $this->getDeepLink( $sContent );

			//	Try up to five times to pull the real data...
			while ( $_iTryCount < 5 )
			{
				$_sContent = PS::makeHttpRequest( $_sDeepLink );

				//	Error ...
				if ( $_sContent === false )
					break;

				if ( $_sContent == '' || $_sContent == null )
				{
					Yii::log( 'Error fetching url:[' . $_sDeepLink . ']', 'error', 'system.components.COpenCalaisApi' );
				}
				else
					break;

				$_iTryCount++;
			}

			if ( strlen( $_sContent ) > 99999 )
			{
				Yii::log( "Content too large, pulling out just the body", 'info', 'system.components.COpenCalaisApi' );

				//	Try and pull the body out...
				$_sContent = PS::suckTag( $_sContent, '<body', '</body>' );

				//	Still too long? Dammit, trim off some characters
				if ( strlen( $_sContent ) > 99999 )
				{
					$_sContent = substr( $_sContent, 0, 99500 );
//					$this->contentType = 'text/raw';
					Yii::log( "Content STILL too large, forced trimmed", 'error', 'system.components.COpenCalaisApi' );
				}
			}
		}

		//	Empty Content? Bail...
		if ( $_sContent == '' || $_sContent == null )
		{
			Yii::log( 'No content to tag, not calling OC url:[' . $_sDeepLink . ']', 'error', 'system.components.COpenCalaisApi' );
			return( null );
		}

		//	Some temps for blank items...
		if ( $this->reltagBaseUrl != '' )
			$_sRelTagBaseUrl = 'c:reltagBaseUrl="' . $this->reltagBaseUrl . '"';

		if ( $this->enableMetadataType != '' )
			$_sEnableMetaDataType = 'c:enableMetadataType="' . $this->enableMetadataType . '"';

		if ( $this->discardMetadata != '' )
			$_sDiscardMetaData = 'c:discardMetaData="' . $this->discardMetadata . '"';

		if ( $this->externalID != '' )
			$_sExternalID = 'c:externalID="' . $this->externalID . '"';

		$_xmlParams = <<<XML
<c:params xmlns:c="http://s.opencalais.com/1/pred/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
	<c:processingDirectives {$_sRelTagBaseUrl} c:calculateRelevanceScore="{$this->calculateRelevanceScore}" c:omitOutputtingOriginalText="{$this->omitOutputtingOriginalText}" c:contentType="{$this->contentType}" {$_sEnableMetaDataType} {$_sDiscardMetaData} c:outputFormat="{$this->outputFormat}">
	</c:processingDirectives>
	<c:userDirectives c:allowDistribution="{$this->allowDistribution}" c:allowSearch="{$this->allowSearch}" {$_sExternalID} c:submitter="{$this->submitter}">
	</c:userDirectives>
	<c:externalMetadata>
	</c:externalMetadata>
</c:params>
XML;

		$_oClient = new SoapClient( $this->soapUrl );

		try
		{
			$_sEncContent = utf8_encode( $_sContent );

			$_oResponse = $_oClient->Enlighten(
				array(
					'licenseID' => $this->apiKey,
					'content' => $_sEncContent,
					'paramsXML' => $_xmlParams,
				)
			);
		}
		catch ( SoapFault $_ex )
		{
			Yii::log( "Error calling Open Calais SOAP method:[" . $_ex->getMessage() . ']', 'error', 'system.components.COpenCalaisApi' );
			$_oResponse = null;
		}

		return( $_oResponse );
	}

	/**
	* Digs into a feed to get the *real* url...
	*
	* @param string $url
	* @return string
	*/
	private function getDeepLink( $url )
	{
		$_sLink = $url;

		//	Is this a digg link?
		if (
			substr( $_sLink, 0, 15 ) == 'http://digg.com' ||
			substr( $_sLink, 0, 21 ) == 'http://feeds.digg.com'
			)
		{
			$_sData = PS::makeHttpRequest( $_sLink );
			if ( $_sData != '' )
			{
				$_sTemp = PS::suckTag( $_sData, '<a href="', '</a>', 0, '<h1 id="title">' );
				if ( $_sTemp != '' )
				{
					$_i = stripos( $_sTemp, '"' );
					if ( $_i !== false )
					{
						$_sLink = substr( $_sTemp, 0, $_i );
						Yii::trace( 'Deeplink pull [' . $url .' / ' . $_sLink . ']', 'system.commands.CheckFeedsCommand');
					}
				}
			}

			unset( $_sData );
		}

		//	How about google news?
		if (
			substr( $_sLink, 0, 32 ) == 'http://news.google.com/news/url?'
			)
		{
			$_i = stripos( $_sLink, 'url=' );
			if ( $_i >= 0 )
			{
				$_sLink = substr( $_sLink, $_i + 4, stripos( $_sLink, '&amp;cid=', $_i + 4 ) - $_i - 4 );
				Yii::trace( 'Deeplink pull [' . $url .' / ' . $_sLink . ']', 'system.commands.CheckFeedsCommand');
			}
		}

		return( $_sLink );
	}
}