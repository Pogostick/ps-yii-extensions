<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSJobProcess encapulates a work unit
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 * @abstract
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
	* @param boolean $bRun If true, initializes and runs the job
	* @return CPSJobProcess
	*/
	public function __construct( $oJob = null, $bRun = false )
	{
		//	Phone home...
		parent::__construct();
		
		//	Store our data...
		$this->m_oJobData = $oJob;
		
		//	Run?
		if ( $bRun )
		{
			$this->init();
			$this->run();
		}
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
		
		if ( $this->m_oJobData ) 
		{
			$this->m_oJobData->proc_ind = $this->m_iResultCode;
			$this->m_oJobData->last_status_text = $this->m_sStatus;
			$this->m_oJobData->save();
		}

		return $_bResult;
	}
	
	/**
	* Returns the amount of time since the timer was started
	* @returns float
	*/
	public function getProcessingTime( $bRaw = false )
	{
		$_fSpan = PS::nvl( $this->m_fEnd, CPSHelp::currentTimeMillis() ) - $this->m_fStart;
		return $bRaw ? $_fSpan : number_format( $_fSpan, 2 ) . 's';
	}
	
	/**
	* Stops the internal job timer
	* 
	*/
	public function stopTimer()
	{
		$this->m_fEnd = CPSHelp::currentTimeMillis();
	}
	
	/**
	* Starts the internal job timer
	* 
	*/
	public function startTimer()
	{
		$this->m_fStart = CPSHelp::currentTimeMillis();
		$this->m_fEnd = null;
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
		Yii::log( $sMessage, $sLevel, PS::nvl( $sCategory, __CLASS__ ) );
	}
}