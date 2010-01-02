<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSJobProcess encapulates a work unit.
 * 
 * Work unit lifecycle is as follows:
 * 
 * 1. __construct()
 * 2. run()
 * 3. process()
 * 
 * Job can be run during construction by setting $bRun to true. Otherwise the run() method must be called by the consumer.
 * 
 * When overriding this class, you should only need to create the process() method with your work details.
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
 * 
 * @property integer $resultCode The result code of the processing
 * @property string $status The status of the processing
 * @property mixed $jobData The job data
 * @property-read string $processingTime The amount of time processing took formated in seconds (i.e. 1.23s)
 * 
 */
abstract class CPSJobProcess extends CPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Start time
	* @var float
	*/
	protected $m_fStart = null;

	/**
	* End time
	* @var float
	*/

	protected $m_fEnd = null;

	/**
	* The result code of the ran job
	* @var integer
	*/
	protected $m_iResultCode = null;
	public function getResultCode() { return $this->m_iResultCode; }
	public function setResultCode( $iValue ) { $this->m_iResultCode = $iValue; }
	
	/**
	* The result status of job
	* @var string
	*/
	protected $m_sStatus = null;
	public function getStatus() { return $this->m_sStatus; }
	public function setStatus( $sValue ) { $this->m_sStatus = $sValue; }
	
	/**
	* The data for this job
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
	* @param mixed $oJob Either a row from a job queue or data to process
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
		$this->startTimer();
		$_bResult = $this->process();
		$this->stopTimer();

		return $_bResult;
	}
	
	/**
	* Returns the amount of time since the timer was started
	* @returns float
	*/
	public function getProcessingTime( $bRaw = false )
	{
		$_fSpan = PS::nvl( $this->m_fEnd, PS::currentTimeMillis() ) - $this->m_fStart;
		return $bRaw ? $_fSpan : number_format( $_fSpan, 2 ) . 's';
	}
	
	/**
	* Stops the internal job timer
	* 
	*/
	public function stopTimer()
	{
		$this->m_fEnd = PS::currentTimeMillis();
	}
	
	/**
	* Starts the internal job timer
	* 
	*/
	public function startTimer()
	{
		$this->m_fStart = PS::currentTimeMillis();
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