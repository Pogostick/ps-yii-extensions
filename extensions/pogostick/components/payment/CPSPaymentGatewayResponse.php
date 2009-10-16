<?php
/**
 * CPSPaymentGatewayResponse class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com Pogostick Yii Extension Library
 * @package psYiiExtensions
 * @subpackage Components
 * @since psYiiExtensions v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 * @license http://www.pogostick.com/license/
 */
/**
 * CPSPaymentGatewayResponse encapsulates a payment gateway response
 *
 * @package psYiiExtensions
 * @subpackage Components
 */
abstract class CPSPaymentGatewayResponse extends CPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The mapping of response items to error parameters
	* 
	* @var array
	*/
	protected $m_arErrorMap = array();
	/**
	* The array of errors, if any
	* 
	* @var array
	*/
	protected $m_arErrorList = array();
	/***
	* A response event map
	* 
	* @var array
	*/
	protected $m_arEventMap = array();
	/**
	* The possible response from the AVS system
	* 	
	* @var array
	*/
	protected $m_arAVSResponse = array();
	/**
	* The possible response from the CAV system
	* 	
	* @var array
	*/
	protected $m_arCAVResponse = array();

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************
	
	/**
	* Get the text of the AVS response
	* 
	* @param string $sCode
	* @return string
	*/
	public function getAVSResponseText( $sCode ) { return CPSHelp::getOption( $this->m_arAVSResponse, $sCode, null ); }
	/**
	* Get the text of the CAV response
	* 
	* @param string $sCode
	* @return string
	*/
	public function getCAVResponseText( $sCode ) { return CPSHelp::getOption( $this->m_arCAVResponse, $sCode, null ); }
	/**
	* Get/set the error mappings
	* 
	* @param array $arMap
	*/
	public function setErrorMap( array $arMap = array() ) { $this->m_arErrorMap = $arMap; }
	public function getErrorMap() { return $this->m_arErrorMap; }
	/**
	* Get/set the event mappings
	* 
	* @param array $arMap
	*/
	public function setEventMap( array $arMap = array() ) { $this->m_arEventMap = $arMap; }
	public function getEventMap() { return $this->m_arEventMap; }

	/**
	* Gets/sets the current error list
	* 
	* @param array $arList
	*/
	public function setErrorList( array $arList = array() ) { $this->m_arErrorList = $arList; }
	public function getErrorList() { return $this->m_arErrorList; }
	
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Build our object
	* 
	* @access public
	* @param mixed $oResponse
	*/
	public function __construct( $oResponse = null )
	{
		//	Phone home...
		parent::__construct();

		//	Add our component options
		$this->addOptions(
			array(
				'rawResponse' => 'string',
				'cookedResponse' => '',
				'apiCallSuccess' => 'boolean:false',
			)
		);
		
		//	Save the raw response...
		$this->rawResponse = $oResponse;
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**                                    
	* Constructs a response object and fills it with data
	* 
	* @param mixed $oResponse
	* @access public
	* @static
	* @returns CPSPaymentGatewayResponse
	*/
	public static function create( $oResponse, $sClass = __CLASS__ )
	{
		//	Override to do stuff here
		return new $sClass( $oResponse );
	}

}
