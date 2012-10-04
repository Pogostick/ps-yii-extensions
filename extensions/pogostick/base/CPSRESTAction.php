<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * CPSRESTAction represents a REST action that is defined as a CPSRESTController method.
 * The method name is like 'restXYZ' where 'XYZ' stands for the action name.
 *
 * @package        psYiiExtensions
 * @subpackage     base
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN $Id: CPSRESTAction.php 395 2010-07-15 21:34:48Z jerryablan@gmail.com $
 * @since          v1.0.6
 *
 * @filesource
 */
class CPSRESTAction extends CAction implements IPSBase
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * The inbound payload for non-GET/POST requests
	 *
	 * @var mixed
	 */
	protected $_payload;

	/**
	 * @var string The request method
	 */
	protected $_method;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @param CController $controller
	 * @param string      $id
	 * @param string      $method
	 */
	public function __construct( $controller, $id, $method = 'GET' )
	{
		parent::__construct( $controller, $id );
		$this->_method = strtoupper( $method );

		if ( 'GET' == $this->_method || 'POST' == $this->_method )
		{
			return;
		}

		//	Get the payload...
		$this->_populatePayload();
	}

	/**
	 * Runs the REST action.
	 *
	 * @throws CHttpException
	 */
	public function run()
	{
		/** @var $_controller CPSRESTController */
		$_controller = $this->getController();

		if ( !( $_controller instanceof IPSRest ) )
		{
			$_controller->missingAction( $this->getId() );

			return;
		}

		//	Call the controllers dispatch method...
		$_controller->dispatchRequest( $this );
	}

	//*************************************************************************
	//* Private Methods
	//*************************************************************************

	/**
	 * Retrieves the content from the request
	 *
	 * @return void
	 */
	protected function _populatePayload()
	{
		$this->_payload = null;

		//	Pull out the payload
		if ( PS::osvr( 'CONTENT_LENGTH', 0 ) > 0 )
		{
			try
			{
				$_stream = fopen( 'php://input', 'r' );

				while ( false !== ( $_chunk = fread( $_stream, 1024 ) ) )
				{
					$this->_payload .= $_chunk;
				}

				fclose( $_stream );
			}
			catch ( Exception $_ex )
			{
				//	@todo Should really log this error... :(
			}
		}
	}

	//*************************************************************************
	//* Properties
	//*************************************************************************

	/**
	 * @param mixed $payload
	 *
	 * @return
	 */
	public function setPayload( $payload )
	{
		$this->_payload = $payload;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPayload()
	{
		return $this->_payload;
	}

}