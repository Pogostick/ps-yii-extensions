<?php
/**
 * CPSConsoleCommand class file.
 *
 * @filesource
 * @author Jerry Ablan <jablan@pogostick.com>
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage commands
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 * 
 * @property-read name The name of the command
 * @property-read commandRunner The command runner
 */
abstract class CPSConsoleCommand extends CPSComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* Minimum column width for display
	*/
	const	MIN_COL_WIDTH = 20;
	/**
	* How much space to leave before the text of the labels
	*/
	const	COL_PADDING = 2;
	/**
	* Our version
	*/
	const	VERSION = '1.0.6';
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* This command's name
	* 
	* @var string
	*/
	protected $m_sName;
	public function getName() { return $this->m_sName; }
	/**
	* The command runner
	* 
	* @var CConsoleCommandRunner
	*/
	protected $m_oRunner;
	public function getCommandRunner() { return $this->m_oRunner; }

	//********************************************************************************
	//* Abstract Methods
	//********************************************************************************

	/**
	* Executes the command.
	* @param array command line parameters for this command.
	*/
	public abstract function run( $arArguments );

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	* 
	* @param string $sName name of the command
	* @param CConsoleCommandRunner $oRunner the command runner
	*/
	public function __construct( $sName, $oRunner )
	{
		//	Phone home
		parent::__construct();
		
		//	Note settings
		$this->m_sName = $sName;
		$this->m_oRunner = $oRunner;
		
		//	Initialize!
		$this->init();
	}
	
	/***
	* Initialize
	* 
	*/
	public function init()
	{
		//	Phone home
		parent::init();
		
		//	Add our options
		$this->addOptions( self::getBaseOptions() );
	}

	/**
	* Provides the command description.
	* 
	* This method may be overridden to return the actual command description.
	* 
	* @return string the command description. Defaults to 'Usage: php entry-script.php command-name'.
	*/
	public function getHelp()
	{
		return 'Usage: ' . $this->m_oRunner->getScriptName() . ' ' . $this->m_sName;
	}

	/**
	* Displays a usage error.
	* 
	* This method will then terminate the execution of the current application.
	* 
	* @param string the error message
	*/
	public function usageError( $sMessage )
	{
		die( "Error: $sMessage" . PHP_EOL . PHP_EOL . $this->getHelp() . PHP_EOL );
	}

	/**
	* Copies a list of files from one place to another.
	* 
	* @param array the list of files to be copied (name=>spec).
	* The array keys are names displayed during the copy process, and array values are specifications
	* for files to be copied. Each array value must be an array of the following structure:
	* <ul>
	* <li>source: required, the full path of the file/directory to be copied from</li>
	* <li>target: required, the full path of the file/directory to be copied to</li>
	* <li>callback: optional, the callback to be invoked when copying a file. The callback function
	*   should be declared as follows:
	*   <pre>
	*   function foo($_sSource,$_arParams)
	*   </pre>
	*   where $_sSource parameter is the source file path, and the content returned
	*   by the function will be saved into the target file.</li>
	* <li>params: optional, the parameters to be passed to the callback</li>
	* </ul>
	* @see buildFileList
	* @returns array
	*/
	public function copyFiles( $arFileList = array() )
	{
		$_bOverwriteAll = false;

		echo "Results" . PHP_EOL;
		echo "============================================================" . PHP_EOL;
		
		foreach ( $arFileList as $_sName => $_arFile )
		{
			$_sSource = strtr( $_arFile['source'], '/\\', DIRECTORY_SEPARATOR );
			$_sTarget = strtr( $_arFile['target'], '/\\', DIRECTORY_SEPARATOR );
			$_oCallback = $_arFile['callback'];
			$_arParams = $_arFile['params'];
			$_sContent = null;

			$this->ensureDirectory( $_sTarget );

			//	Get the content...
			if ( $_oCallback && is_array( $_oCallback ) )
				$_sContent = call_user_func( $_oCallback, $_sSource, $_arParams );
			else
				$_sContent = file_get_contents( $_sSource );

			if ( is_file( $_sTarget ) )
			{
				if ( ! $this->force && $_sContent === file_get_contents( $_sTarget ) )
				{
					$arFileList[ $_sName ][ '_status' ] = 0;
					$this->boldEchoString( $_sName, 'Unchanged' );
					continue;
				}
				
				if ( $this->force || $_bOverwriteAll )
				{
					$arFileList[ $_sName ][ '_status' ] = 1;
					$this->boldEchoString( $_sName, ( $this->force ? 'Force ' : '' ) . 'Overwrite' );
				}
				else
				{
					$this->boldEchoString( $_sName, 'Existing' );
					$this->echoString( "[\033[1my\033[0mes|\033[1mn\033[0mo|\033[1ma\033[0mll|\033[1mq\033[0muit] ", '--> Overwrite? ', false, ' ', false, 8 );
					
					switch ( substr( strtolower( trim( fgets( STDIN ) ) ), 0, 1 ) )
					{
						case 'q':
							return;
							
						case 'a':
							$_bOverwriteAll = true;

						case 'y':
							$arFileList[ $_sName ][ '_status' ] = 1;
							$this->boldEchoString( $_sName, 'Overwriting' );
							break;
						
						case 'n':
							$arFileList[ $_sName ][ '_status' ] = 0;
							$this->boldEchoString( $_sName, 'Skipping' );
							break;
					}
				}
			}
			else
			{
				$arFileList[ $_sName ][ '_status' ] = 1;
				$this->ensureDirectory( dirname( $_sTarget ) );
				$this->boldEchoString( $_sName, 'Generating' );
			}
			
			@file_put_contents( $_sTarget, $_sContent );
		}
		
		//	Return array with statuses
		return $arFileList;
	}

	/**
	* Converts a word to its plural form.
	* 
	* @param string the word to be pluralized
	* @return string the pluralized word
	*/
	public function pluralize( $sString )
	{
		$_arRules = array(
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(m)an$/i' => '\1en',
			'/(child)$/i' => '\1ren',
			'/(r|t|b|d)y$/i' => '\1ies',
			'/s$/' => 's',
		);
		
		//	Check our pattern list
		foreach ( $_arRules as $_sRule => $_sReplace )
			if ( preg_match( $_sRule, $sString ) ) return preg_replace( $_sRule, $_sReplace, $sString );
		
		return $sString . 's';
	}

	/**
	* Builds the file list of a directory.
	* 
	* This method traverses through the specified directory and builds
	* a list of files and subdirectories that the directory contains.
	* The result of this function can be passed to {@link copyFiles}.
	* 
	* @param string the source directory
	* @param string the target directory
	* @param string base directory
	* @return array the file list (see {@link copyFiles})
	*/
	public function buildFileList( $sSourceDir, $sTargetDir, $sBaseDir = '' )
	{
		$_arList = array();
		$_iDir = opendir( $sSourceDir );

		while ( $_sFile = readdir( $_iDir ) )
		{
			//	Ignore parents and svn...
			if ( $_sFile === '.' || $_sFile === '..' || $_sFile === '.svn' )
				continue;

			$_sSourcePath = $sSourceDir . DIRECTORY_SEPARATOR . $_sFile;
			$_sTargetPath = $sTargetDir . DIRECTORY_SEPARATOR . $_sFile;
			$_sName = $sBaseDir === '' ? $_sFile : $sBaseDir . DIRECTORY_SEPARATOR . $_sFile;

			if ( is_dir( $_sSourcePath ) ) 
				$_arList = array_merge( $_arList, $this->buildFileList( $_sSourcePath, $_sTargetPath, $_sName ) );
			else                                      
			{
				$_arList[ $_sName ] = array(
					'source' => $_sSourcePath,
					'target' => $_sTargetPath,
					'callback' => array( $this, 'generateFile' ),
					'params' => array(),
				);
			}
		}
		
		closedir( $_iDir );

		return $_arList;
	}

	/**
	* Creates all parent directories if they do not exist.
	* 
	* @param string the directory to be checked
	* @param integer The directory mode
	*/
	public function ensureDirectory( $sTarget )
	{
		$_bResult = true;
		$_arInfo = pathinfo( $sTarget );
		
		if ( ! is_dir( $_arInfo['dirname'] ) )
		{
			$this->boldEchoString( strtr( $_arInfo['dirname'], '\\', '/' ), 'Created Directory' );
			$_bResult = @mkdir( $_arInfo['dirname'] );
		}
		
		return $_bResult;
	}

    /**
    * Generates the file
    * 
    * @param string $sSource
    * @param array $arParams
    * @return string
    */
	public function generateFile( $sSource, $arParams )
	{
		return $this->renderFile( $sSource, $arParams, true );
	}
	
	/**
	* Renders a view file.
	* 
	* @param string view file path
	* @param array optional data to be extracted as local view variables
	* @param boolean whether to return the rendering result instead of displaying it
	* 
	* @return mixed the rendering result if required. Null otherwise.
	*/
	public function renderFile( $sViewFile, $oData = null, $bReturn = false )
	{
		if ( is_array( $oData ) ) 
			extract( $oData, EXTR_PREFIX_SAME, 'data' );
		else
			$data = $oData;
			
		if ( $bReturn )
		{
			ob_start();
			ob_implicit_flush( false );
			require( $sViewFile );
			return ob_get_clean();
		}

		require( $sViewFile );
	}

	/**
	* Writes a neat string to the console
	* 
	* @param string $sStr
	* @param mixed $sLabel
	* @param mixed $bReturnValue
	* @param mixed $sSuffix
	* @param mixed $bNewLine
	* @param mixed $iExtraSpace
	*/
	public function echoString( $sStr, $sLabel = null, $bReturnValue = false, $sSuffix = ' : ', $bNewLine = true, $iExtraSpace = 0, $bBold = false )
	{
		$_sVal = null;
		
		if ( $sLabel == ' ' )
			$sSuffix = null;
		else
			$sLabel = trim( $sLabel );

		if ( null !== $sLabel ) 
		{
			if ( strlen( $sLabel ) > $this->colWidth ) $this->colWidth = strlen( $sLabel );
			$_sVal = str_pad( $sLabel . $sSuffix, $this->colWidth + self::COL_PADDING + $iExtraSpace, ' ', STR_PAD_LEFT );
		}

		$_sVal .= ( is_object( $sStr ) ? get_class( $sStr ) : $sStr ) . ( $bNewLine ?  PHP_EOL : '' );
		
		if ( $bBold ) $_sVal = str_replace( $sLabel, $this->bold( $sLabel ), $_sVal );

		if ( $bReturnValue ) return $_sVal;
		
		//	Otherwise echo!
		echo $_sVal;
	}
	
	/**
	* Writes neat output to console, applying bold codes to label if provided
	* 
	* @param mixed $sStr
	* @param mixed $sLabel
	* @param mixed $bReturnValue
	* @param mixed $sSuffix
	* @param mixed $bNewLine
	* @param mixed $iExtraSpace
	* @return mixed
	*/
	public function boldEchoString( $sStr, $sLabel = null, $bReturnValue = false, $sSuffix = ' : ', $bNewLine = true, $iExtraSpace = 0 )
	{
		return $this->echoString( $sStr, $sLabel, $bReturnValue, $sSuffix, $bNewLine, $iExtraSpace, true );
	}
	
	/**
	* Wraps a string bold codes for console display
	* 
	* @param string $sStr
	*/
	public function bold( $sStr )
	{
		return "\033[1m{$sStr}\033[0m";
	}

	/**
	* Processes the command line arguments
	* 
	* @param array $arArgs
	* @return array
	*/
	public function getopts( $arArgs = array() )
	{
		$_arResults = array(
			'original' => $arArgs,
			'rebuilt' => array(),
			'options' => array(),
		);
		
		//	Our return options array...
		$_arOptions = array();
		
		//	Rebuild args...
		for ( $_i = 0, $_iCount = count( $arArgs ); $_i < $_iCount; $_i++ )
		{
			$_sArg = $arArgs[ $_i ];
			$_sOpt = trim( substr( $_sArg, 0, strpos( $_sArg, '=' ) ) );

			if ( $_sOpt && $_sOpt[0] == '-' && $_sOpt[1] == '-' )
				$_arOptions[ substr( $_sOpt, 2 ) ] = str_replace( $_sOpt . '=', '', $_sArg );
			elseif ( $_sOpt && $_sOpt[0] == '-' )
				$_arOptions[ substr( $_sOpt, 1 ) ] = str_replace( $_sOpt . '=', '', $_sArg );
			else
				$_arResults['rebuilt'][] = $arArgs[ $_i ];
		}
		
		$_arResults['options'] = $_arOptions;
		
		//	Return the processed results...
		return $_arResults;		
	}

	/**
	* Ensures the db connection is valid
	* 
	*/
	public function getDbConnection()
	{
		$_oDB = ( $this->databaseName != 'db' ) ? Yii::app()->{$this->databaseName} : Yii::app()->getDb();

		if ( $_oDB === null )
		{
			echo "\033[1mError\033[0m: an active '{$this->databaseName}' connection is required." . PHP_EOL;
			echo "If you already added '{$this->databaseName}' component in application configuration," . PHP_EOL;
			echo "please quit and re-enter the yiic shell." . PHP_EOL;
			return false;
		}

		$_oDB->active = true;
		$this->schema = $_oDB->schema;
		
		return true;
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Based on a class name, the module is selected
	* 
	* @param string $sClassName
	* @return CModule
	*/
	protected function loadModule( $sClassName )
	{
		$_oModule = Yii::app();
		$_sModelClass = $sClassName;
		
		if ( false === ( $_iPos = strpos( $_sModelClass, '.' ) ) )
			$_sModelClass = 'application.models.' . $_sModelClass;
		else
		{
			$_sId = substr( $_sModelClass, 0, $_iPos );
			
			if ( null !== ( $_oNewModule = Yii::app()->getModule( $_sId ) ) )
				$_oModule = $_oNewModule;
		}
		
		//	Return module
		return $_oModule;
	}

	/**
	* Process arguments passed in
	* 
	* @param array $arArgs
	* @return array
	*/
	protected function processArguments( $arArgs )
	{
		//	Set some defaults...
		$this->templatePath = PS::nvl( $this->templatePath, YII_PATH . '/cli/views/shell/model' );

		//	Process command line arguments
		$_sClassName = array_shift( $arArgs );
		$_arOptions = $this->getopts( $arArgs );
		$arArgs = array_merge( array( $_sClassName ), $_arOptions['rebuilt'] );

		//	Set our values based on options...
		foreach ( $_arOptions['options'] as $_sKey => $_sValue )
		{
			switch ( strtolower( $_sKey ) )
			{
				case 'n':
				case 'template-name':
					$this->templateName = $_sValue;
					break;
					
				case 'force':
				case 'f':
					$this->force = true;
					break;
					
				case 'd':
				case 'database':
					$this->databaseName = $_sValue;
					break;
					
				case 'b':
				case 'base-class':
					$this->baseClass = $_sValue;
					break;
					
				case 't':
				case 'template-path':
					$this->templatePath = $_sValue;
					break;
					
				default:
					//	Look through options..
					foreach ( $this->makeOptions( true, PS::OF_ASSOC_ARRAY, true ) as $_sOptKey => $_sOptValue )
					{
						if ( $_sKey == $_sOptKey || $_sKey == CPSTransform::underscorize( $_sOptKey, '-' ) )
						{
							$this->{$_sOptKey} = $_sValue;
							break;
						}
					}
					break;
			}
		}
		
		return $arArgs;
	}
	
	/**
	* Get options to display
	* @returns string
	*/
	protected function getDisplayOptions()
	{
		$_sOptions = null;
		
		//	Look through options..
		foreach ( $this->makeOptions( true, PS::OF_ASSOC_ARRAY ) as $_sOptKey => $_sOptValue )
		{
			$_sOptions .= str_pad( $this->bold( '  --' . $_sOptKey ), 39, ' ', STR_PAD_RIGHT );
			if ( $_sOptValue ) $_sOptions .= 'Default value is "' . ( is_array( $_sOptValue ) ? implode( ', ', $_sOptValue ) : $_sOptValue ) . '"';
			$_sOptions .= PHP_EOL;
		}
		
		if ( $_sOptions ) $_sOptions = "MORE OPTIONS" . PHP_EOL . "$_sOptions" ;	
		
		return $_sOptions;
	}
	
	/**
	* Returns a pretty list of generated files
	* 
	* @param array $arFileList
	* @param boolean $bInclude
	* @param integer $iNew Returns the number of new files created
	*/
	protected function getResultDisplay( $arFileList = array(), $bInclude = false, &$iNew = 0 )
	{
		$_iGen = 0;
		$_sMsg = PHP_EOL . "No files were created or destroyed." . PHP_EOL;
		
		foreach ( $arFileList as $_sFile => $_arParams )
		{
			if ( PS::o( $_arParams, '_status', 0 ) )
			{
				$_sClasses .= $this->boldEchoString( '(' . $_arParams[ 'target' ] . ')', $_sFile, true );
				$_iGen++;
			}
				
			if ( $bInclude ) @include_once( $_arParams[ 'target' ] );
		}
		
		if ( $_iGen ) 
		{
			$_sMsg = PHP_EOL . 'The following file' . ( $_iGen > 1 ? 's were generated:' : ' was generated:' ) . PHP_EOL . PHP_EOL .
				$this->echoString( '(File Name)', 'Class Name', true ) .
				"============================================================" . PHP_EOL .
				$_sClasses;
		}
		
		$iNew = $_iGen;

		return $_sMsg;
	}
	
	/**
	* Display command header and parameters
	* 
	* @param string $sName
	* @param array $arExtra
	*/
	protected function displayParameters( $sName, $arExtra = array() )
	{
		$_arOptions = array_merge( $arExtra, $this->makeOptions( true, PS::OF_ASSOC_ARRAY, true ) );
		$_iColWidth = $this->colWidth;
		
		//	Update column width based on option keys...
		foreach ( $_arOptions as $_sKey => $_sVal ) 
			$_iColWidth = max( $_iColWidth, strlen( $_sKey ) + self::COL_PADDING );
		
		$this->colWidth = $_iColWidth;

		echo PHP_EOL;
		echo $this->bold( "Pogostick Yii Extensions {$sName} v" . self::VERSION ) . PHP_EOL;
		echo PHP_EOL;

		echo "Working Parameters" . PHP_EOL;
		echo "============================================================" . PHP_EOL . PHP_EOL;

		foreach ( $_arOptions as $_sKey => $_sValue )
		{
			if ( ! is_array( $_sValue ) )
				echo $this->boldEchoString( $_sValue, $_sKey, true );
			else
			{
				if ( count( $_sValue ) && is_array( current( $_sValue ) ) )
				{
					$_arOut = array();
					
					foreach ( $_sValue as $_sSubKey => $_sSubValue )
						$_arOut[] = $_sSubKey;

					$_sValue = $_arOut;
				}
				
				echo $this->boldEchoString( implode( ', ', $_sValue ), $_sKey, true );				
			}
		}
			                  
		echo PHP_EOL;
	}
	
	/**
	* Easier on the eyes
	*
	* @access private
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'force_' => 'bool:false',
				'databaseName_' => 'string:db',
				'basePath_' => 'string:' . Yii::getPathOfAlias( 'application.models' ),
				'templatePath_' => 'string',
				'templateName_' => 'string',
				'baseClass_' => 'string:CPSModel',
				'colWidth' => 'int:' . self::MIN_COL_WIDTH,
				'schema' => 'object',
			)
		);
	}
	
}
