<?php
/**
 * CPSJobProcess class file.
 *
 * CPSJobProcess encapulates a work unit
 * 
 * @filesource
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 * @package psYiiExtensions
 * @subpackage Base
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
abstract class CPSJobProcess extends CPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Start time
	* 
	* @var float
	*/
	protected $m_fStart = null;
	/**
	* End time
	* 
	* @var float
	*/
	protected $m_fEnd = null;
	/**
	* The result code of the ran job
	* 
	* @var integer
	*/
	protected $m_iResultCode = null;
	public function getResultCode() { return $this->m_iResultCode; }
	public function setResultCode( $iValue ) { $this->m_iResultCode = $iValue; }
	/**
	* The result status of job
	* 
	* @var string
	*/
	protected $m_sStatus = null;
	public function getStatus() { return $this->m_sStatus; }
	public function setStatus( $sValue ) { $this->m_sStatus = $sValue; }
	/**
	* The data for this job
	* 
	* @var mixed
	*/
	protected $m_oJobData = null;
	public function getJobData() { return $this->m_oJobData; }
	public function setJobData( $oValue ) { $this->m_oJobData = $oValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Constructor
	* 
	* @param mixed $oJob Either a row from the job queue or data to process
	* @return CPSJobProcess
	*/
	public function __construct( $oJob = null )
	{
		//	Phone home...
		parent::__construct();
		
		//	Store our data...
		$this->m_oJobData = $oJob;
	}
	
	/**
	* Runs the job process with timing
	* @returns boolean
	*/
	public function run()
	{
		$this->m_fStart = CPSHelp::currentTimeMillis();
		$_bResult = $this->process();
		$this->m_fEnd = CPSHelp::currentTimeMillis();
		
		return $_bResult;
	}
	
	/**
	* Returns the total amount of time taken to run process in seconds
	* @returns float
	*/
	public function getProcessTime()
	{
		return $this->m_fEnd - $this->m_fStart;
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Process the job
	*/
	abstract protected function process();
	
	/**
	* Logs a message to the application log
	* 
	* @param string $sMessage
	* @param string $sLevel
	* @param string $sCategory
	* @param boolean $bNoStatus If true, will NOT set status of job with error message
	*/
	protected function log( $sMessage, $sLevel = 'trace', $sCategory = null, $bNoStatus = false )
	{
		//	Auto set status 
		if ( ! $bNoStatus && $sLevel == 'error' ) $this->setStatus( $sMessage );
		Yii::log( $sMessage, $sLevel, CPSHelp::nvl( $sCategory, __CLASS__ ) );
	}
}
