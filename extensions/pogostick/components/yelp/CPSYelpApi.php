<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSYelpApi provides access to the Yelp Business Reviews API
 * 
 * @package 	psYiiExtensions.components
 * @subpackage 	yelp
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSYelpApi.php 318 2009-12-23 06:16:34Z jerryablan@gmail.com $
 * @since 		v1.0.4
 * 
 * @filesource
 */
class CPSYelpApi extends CPSApiComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const YELP_REVIEW_API = 'review';
	const YELP_PHONE_API = 'phone';
	const YELP_NEIGHBORHOOD_API = 'neighborhood';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	public function init()
	{
		//	Call daddy
		parent::init();

		//	Set Yelp API defaults
		$this->requireApiQueryName = true;
		$this->apiQueryName = 'ywsid';
		$this->apiBaseUrl = 'http://api.yelp.com';

		//	The valid Yelp APIs to call
		$this->apiSubUrls =
			array(
				self::YELP_REVIEW_API => 'business_review_search',
				self::YELP_PHONE_API => 'phone_search',
				self::YELP_NEIGHBORHOOD_API => 'neighborhood_search',
			);

		//	Create the base array
		$this->requestMap = array();

		//	Review API
		$this->addRequestMapping( 'searchTerm', 'term', false, null, self::YELP_REVIEW_API, 'boundingBox' );
		$this->addRequestMapping( 'maxResults', 'num_biz_requested' );
		$this->addRequestMapping( 'topLeftLatitude', 'tl_lat', true );
		$this->addRequestMapping( 'topLeftLongitude', 'tl_long', true );
		$this->addRequestMapping( 'bottomRightLatitude', 'br_lat', true );
		$this->addRequestMapping( 'bottomRightLongitude', 'br_long', true );
		$this->addRequestMapping( 'category' );

		$this->addRequestMapping( 'searchTerm', 'term', false, null, self::YELP_REVIEW_API, 'point' );
		$this->addRequestMapping( 'maxResults', 'num_biz_requested' );
		$this->addRequestMapping( 'latitude', 'lat', true );
		$this->addRequestMapping( 'longitude', 'long', true );
		$this->addRequestMapping( 'radius' );
		$this->addRequestMapping( 'category' );

		$this->addRequestMapping( 'searchTerm', 'term', false, null, self::YELP_REVIEW_API, 'location' );
		$this->addRequestMapping( 'maxResults', 'num_biz_requested' );
		$this->addRequestMapping( 'location', 'location', true );
		$this->addRequestMapping( 'countryCode', 'cc' );
		$this->addRequestMapping( 'radius' );
		$this->addRequestMapping( 'category' );

		//	Phone API
		$this->addRequestMapping( 'phoneNumber', 'phone', false, null, self::YELP_PHONE_API, 'number' );
		$this->addRequestMapping( 'countryCode', 'cc' );
		$this->addRequestMapping( 'category' );

		//	Neighborhood API
		$this->addRequestMapping( 'latitude', 'lat', true, null, self::YELP_NEIGHBORHOOD_API, 'point' );
		$this->addRequestMapping( 'longitude', 'long', true );
		$this->addRequestMapping( 'category' );

		$this->addRequestMapping( 'location', null, true, null, self::YELP_NEIGHBORHOOD_API, 'location' );
		$this->addRequestMapping( 'countryCode', 'cc' );
		$this->addRequestMapping( 'category' );
	}

	/**
	* Calls the Yelp API and retrieves reviews by bounding rectancle.
	*
	* @param mixed $fTLLat
	* @param mixed $fTLLong
	* @param mixed $fBRLat
	* @param mixed $fBRLong
	* @param mixed $sSearchTerm
	* @param mixed $iMaxResults
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getReviewsByBounds( $fTLLat, $fTLLong, $fBRLat, $fBRLong, $sSearchTerm = null, $iMaxResults = 10, $sCategories = null )
	{
		$this->apiToUse = self::YELP_REVIEW_API;

		$this->requestData = array(
			'topLeftLatitude' => $fTLLat,
			'topLeftLongitude' => $fTLLong,
			'bottomRightLatitude' => $fBRLat,
			'bottomRightLongitude' => $fBRLong,
			'maxResults' => $iMaxResults,
		);

		if ( $sSearchTerm != null )
			$this->requestData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'boundingBox' ) );
	}

	/**
	* Calls the Yelp API and retrieves reviews by geo-point
	*
	* @param mixed $fLat
	* @param mixed $fLong
	* @param mixed $iRadius
	* @param mixed $sSearchTerm
	* @param mixed $iMaxResults
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getReviewsByPoint( $fLat, $fLong, $iRadius = null, $sSearchTerm = null, $iMaxResults = 10, $sCategories = null )
	{
		$this->apiToUse = self::YELP_REVIEW_API;

		$this->requestData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
			'maxResults' => $iMaxResults,
		);

		if ( $iRadius != null )
			$this->requestData[ 'radius' ] = $iRadius;

		if ( $sSearchTerm != null )
			$this->requestData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'point' ) );
	}

	/**
	* Calls the Yelp API and retrieves reviews by location
	*
	* @param mixed $sLocation
	* @param mixed $iRadius
	* @param mixed $sSearchTerm
	* @param mixed $iMaxResults
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getReviewsByNeighborhood( $sLocation, $iRadius = null, $sSearchTerm = null, $iMaxResults = 10, $sCountryCode = null, $sCategories = null )
	{
		$this->apiToUse = self::YELP_REVIEW_API;

		$this->requestData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
			'maxResults' => $iMaxResults,
			'location' => $sLocation,
		);

		if ( $iRadius != null )
			$this->requestData[ 'radius' ] = $iRadius;

		if ( $sSearchTerm != null )
			$this->requestData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'location' ) );
	}

	/***
	* Calls the Yelp Phone API and returns results
	*
	* @param mixed $sPhone
	* @param mixed $sCountryCode
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getPhoneByNumber( $sPhone, $sCountryCode = null, $sCategories = null )
	{
 		$this->apiToUse = self::YELP_PHONE_API;

		$this->requestData = array(
			'phone' => $sPhone,
		);

		if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'number' ) );
	}

	/**
	* Gets data from the Yelp Neighborhood API by geo-point
	*
	* @param mixed $fLat
	* @param mixed $fLong
	* @param mixed $sCountryCode
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getNeighborhoodByPoint( $fLat, $fLong, $sCountryCode = null, $sCategories = null )
	{
 		$this->apiToUse = self::YELP_NEIGHBORHOOD_API;

		$this->requestData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
		);

    	if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'phone' ) );
	}

	/**
	* Gets data from the Yelp Neighborhood API
	*
	* @param mixed $sLocation
	* @param mixed $sCountryCode
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getNeighborhoodByLocation( $sLocation, $sCountryCode = null, $sCategories = null )
	{
 		$this->apiToUse = self::YELP_NEIGHBORHOOD_API;

		$this->requestData = array(
			'location' => $sLocation,
		);

    	if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'location' ) );
	}

}