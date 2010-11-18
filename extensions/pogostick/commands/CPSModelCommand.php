<?php
/**
* CPSModelCommand class file.
*
* @filesource
* @author Qiang Xue <qiang.xue@gmail.com>
* @link http://www.yiiframework.com/
* @copyright Copyright &copy; 2008-2009 Yii Software LLC
* @license http://www.yiiframework.com/license/
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://www.pogostick.com Pogostick, LLC.
* @package psYiiExtensions
* @subpackage commands
* @since v1.0.6
* @version SVN: $Revision: 368 $
* @modifiedby $LastChangedBy: jerryablan@gmail.com $
* @lastmodified  $Date: 2010-01-17 20:55:44 -0500 (Sun, 17 Jan 2010) $
*/

//	Imports for me...
Yii::import( 'pogostick.commands.CPSConsoleCommand' );
 
/**
* Command that generates a Pogostick Yii Model
*/
class CPSModelCommand extends CPSConsoleCommand
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* Our version
	*/
	const VERSION = '1.0.6';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize the command
	* 
	*/
	public function init()
	{
		parent::init();
		
		//	Add our options
		$this->addOptions( self::getBaseOptions() );

		//	Set base values
		$this->baseClass = 'CPSModel';
		$this->templatePath = Yii::getPathOfAlias( 'pogostick.templates' );
		$this->templateName = 'model.php';
//		$this->name = 'psmodel';
	}

	/**
	* Execute the action.
	* @param array $arArgs command line parameters specific for this command
	*/
	public function run( $arArgs )
	{
		//	Our base path...
		$_sBasePath = Yii::getPathOfAlias( 'application.models' );
		
		//	Process arguments...
		$arArgs = $this->processArguments( $arArgs );
		
		//	Check args...
		if ( ! ( $_sClassName = PS::o( $arArgs, 0, null ) ) )
		{
			echo "\033[1mError\033[0m: model class name is required.\n";
			echo $this->getHelp();
			return;
		}

		//	Process the model name...
		if ( ! preg_match('/^[\w\.\-\*]*(.*?)$/', $_sClassName, $_arMatches ) )
		{
			echo "\033[1mError\033[0m: model class \"" . $_sClassName . "\" is invalid.\n";
			return;
		}

		//	Get database set up
		if ( ! $this->getDbConnection() )
			return;

		//	Without regular expressions
		if ( empty( $_arMatches[ 1 ] ) )
		{
			$this->generateClassNames( $this->schema );
			
			if ( ( $_iPos = strrpos( $_sClassName, '.' ) ) !== false )
			{
				//	i.e. module.models.* becomes base=module.models, class=*
				$_sBasePath = Yii::getPathOfAlias( substr( $_sClassName, 0, $_iPos ) );
				$_sClassName = substr( $_sClassName, $_iPos + 1 );
			}

			//	Generate all models
			if ( $_sClassName == '*' ) 
				$this->generateRelations();
			else
			{
				$_sTableName = PS::nvl( $arArgs[ 1 ], $_sClassName );
				$_arTables = $this->tables;
				$_arTables[ $_sTableName ] = $_sClassName;
				$this->tables = $_arTables;
				$this->generateRelations();
				$this->classes = array( $_sTableName => $_sClassName );
			}
		}
		else  // with regular expressions
		{
			$_sPattern = $_arMatches[ 1 ];
			
			if ( false !== ( $_iPos = strrpos( $_sClassFile, $_sPattern ) ) )
				$_sBasePath = Yii::getPathOfAlias( rtrim( substr( $_sClassName, 0, $_iPos ), '.' ) );
			else
				$_sBasePath = Yii::getPathOfAlias( 'application.models' );
			
			// only regexp is given
			$this->generateClassNames( $this->schema, $_sPattern );
			$_arClasses = $this->tables;
			$this->generateRelations();
			$this->classes = $_arClasses;
		}

		$this->displayParameters( 'Model Generator', array( 'Output Path' => $_sBasePath ) );
			
		if ( count( $this->classes ) > 1 )
		{
			$_i = 1;
			
			echo "\nModel classes matching your criteria:\n";
			echo "============================================================\n\n";
			
			foreach ( $this->classes as $_sTableName => $_sClassName )
				echo "    " . ( $_i++ ) . ". " . $this->bold( $_sClassName ) . " ({$_sTableName})\n";
			
			$_sPrompt = "\nDo you want to generate these classes? [\033[1mY\033[0mes|\033[1mN\033[0mo] ";
			
			while ( $_sPrompt )
			{
				echo "$_sPrompt";
				
				switch ( substr( strtolower( trim( fgets( STDIN ) ) ), 0, 1 ) )
				{
					case 'y':
						//	We got the answer we want!
						break 2;
						
					case 'n':
					case 'q':
						return;
				}
			}
			
			echo "\n";
		}

		$_arParams = $_arFiles = array();
		$_arClasses = $this->classes;
		$_sModelTemplate = $this->templatePath . DIRECTORY_SEPARATOR . $this->templateName;
		
		foreach ( $this->classes as $_sTableName => $_sClassName )
		{
			$_sClassFile = $_sBasePath . DIRECTORY_SEPARATOR . $_sClassName . '.php';
			
			$_arParams[ $_sClassName . '.php' ] = array(
				'source' => $_sModelTemplate,
				'target' => $_sClassFile,
				'callback' => array( $this, 'generateModel' ),
				'params' => array( $_sClassName, $_sTableName, $this->baseClass, $this->databaseName ),
			);

			if ( strlen( $_sClassName ) > $this->colWidth ) $this->colWidth = strlen( $_sClassName );
		}

		//	Load them all up
		$_arParams = $this->copyFiles( $_arParams );
		
		echo $this->getResultDisplay( $_arParams, true, $_iGen );

		if ( $_iGen )
		{
			echo <<<EOD
============================================================

If you have a '{$this->databaseName}' database connection, you 
may test any generated models now with the following commands:

    \$_oModel = {$_sClassName}::model()->find();
    print_r( \$_oModel );

EOD;
		}
	}

	/**
	* Generate a model
	* 
	* @param string $sView
	* @param array $arParams
	* @return string
	*/
	public function generateModel( $sView, $arParams )
	{
		$_arRules = $_arLabels = $_arRelations = array();
		list( $_sClassName, $_sTableName, $_sBaseClass, $this->databaseName ) = $arParams;

		$_sContents = file_get_contents( $sView );

		//	No table? Try view...
		if ( null !== ( $_oTable = $this->schema->getTable( $_sTableName ) ) )
		{
			$_arRequired = $_arIntegers = $_arNumerical = array();
			
			foreach ( $_oTable->columns as $_oColumn )
			{
				$label = ucwords( trim( strtolower( str_replace( array( '-', '_' ), ' ', preg_replace( '/(?<![A-Z])[A-Z]/', ' \0', $_oColumn->name ) ) ) ) );
				$label = preg_replace( '/\s+/', ' ', $label );
				
				if ( strcasecmp( substr( $label, -3 ),' id' ) === 0 ) $label = substr( $label, 0, -3 );
				$_arLabels[ $_oColumn->name ] = $label;
				
				if ( $_oColumn->isPrimaryKey && $_oTable->sequenceName !== null || $_oColumn->isForeignKey )
					continue;
					
				if ( ! $_oColumn->allowNull && $_oColumn->defaultValue === null ) $_arRequired[] = $_oColumn->name;
				
				switch ( $_oColumn->type )
				{
					case 'integer':
						$_arIntegers[] = $_oColumn->name;
						break;
						
					case 'double':
						$_arNumerical[] = $_oColumn->name;
						break;
						
					case 'string':
						if ( $_oColumn->size > 0 )
							$_arRules[] = "array( '{$_oColumn->name}', 'length', 'max' => {$_oColumn->size} )";
						 break;
				}
			}
			
			if ( $_arRequired !== array() ) $_arRules[] = "array( '" . implode( ', ', $_arRequired ) . "', 'required' )";
			if ( $_arIntegers !== array() ) $_arRules[] = "array( '" . implode( ', ', $_arIntegers ) . "', 'numerical', 'integerOnly' => true )";
			if ( $_arNumerical !== array() ) $_arRules[] = "array( '" . implode( ', ', $_arNumerical ) . "', 'numerical' )";

			if ( isset( $this->relations[ $_sClassName ] ) && is_array( $this->relations[ $_sClassName ] ) )
				$_arRelations = $this->relations[ $_sClassName ];
		}
		else
			$this->echoString( "The table \"{$_sTableName}\" cannot be accessed", 'Warning' );

		// fall back to default ones
		if ( ! is_file( $sView ) ) $sView = YII_PATH . '/cli/views/shell/model/' . basename( $sView );
		
		return $this->renderFile( $sView, 
			array(
				'className' => $_sClassName,
				'tableName' => $_sTableName,
				'baseClass' => $_sBaseClass,
				'dbToUse' => $this->databaseName,
				'columns'=> PS::nvl( $_oTable->columns, array() ),
				'rules' => $_arRules,
				'labels' => $_arLabels,
				'relations' => $_arRelations,
			),
			true
		);
	}
	
	/**
	* Shows the usage
	* 
	*/
	public function getHelp()
	{
		$_sName = $this->bold( $this->name );
		$_sOptions = $this->getDisplayOptions();
		
		return <<<EOD
USAGE
	\033[1mpsmodel\033[0m <\033[1mclass-name\033[0m> [\033[1mtable-name\033[0m] [options]
	
This command generates a model class with the specified class name.

PARAMETERS
 * class-name: required, model class name. By default, the generated
   model class file will be placed under the directory aliased as
   'application.models'. To override this default, specify the class
   name in terms of a path alias, e.g., 'application.somewhere.ClassName'.

   If the model class belongs to a module, it should be specified
   as 'ModuleID.models.ClassName'.

   If the class name ends with '*', then a model class will be generated
   for EVERY table in the database.

   If the class name contains a regular expression deliminated by slashes,
   then a model class will be generated for those tables whose name
   matches the regular expression. If the regular expression contains
   sub-patterns, the first sub-pattern will be used to generate the model
   class name.

 * table-name: optional, the associated database table name. If not given,
   it is assumed to be the model class name.

   Note, when the class name ends with '*', this parameter will be
   ignored.

OPTIONS

  -f, --force                  if the model class exists, you will be able to 
                                   optionally overwrite it.
                                   
  -d, --database               The database component to use. 
                                   Defaults to 'db'
                                   
  -b, --base-class             The base class to use for generated models. 
                                   Defaults to 'CPSModel'
                                   
  -n, --template-name          The name of the template to use. 
                                   Defaults to 'model.php'
                                   
  -t, --template-path          The template path to use.

{$_sOptions}
EXAMPLES
 * Generates the Post model:
        {$_sName} Post

 * Generates the Post model using component db_posts:
        {$_sName} Post -d=db_posts

 * Generates the Post model which is associated with table 'posts':
        {$_sName} Post posts

 * Generates the Post model which should belong to module 'admin':
        {$_sName} admin.models.Post

 * Generates a model class for every table in the current database:
        {$_sName} *

 * Generates a model class for every table based on CSubClass:
        {$_sName} * -b=CSubClass

 * Same as above, but the model class files should be generated
   under 'protected/models2':
        {$_sName} application.models2.*

 * Generates a model class for every table whose name is prefixed
   with 'tbl_' in the current database. The model class will not
   contain the table prefix.
        {$_sName} /^tbl_(.*)$/

 * Same as above, but the model class files should be generated
   under 'protected/models2':
        {$_sName} application.models2./^tbl_(.*)$/

EOD;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Gets the name of class given a table name
	* 
	* @param string $sTable
	*/
	protected function getClassName( $sTable )
	{
		return PS::nvl( $this->tables[ $sTable ], $this->generateClassName( $sTable ) );
	}
	
	/**
	* Checks if the given table is a "many to many" helper table.
	* 
	* Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	* 
	* @param CDbTableSchema table to inspect
	* @return boolean true if table matches description of helpter table.
	*/
	protected function isRelationTable( $oTable )
	{
		$_sPK = $oTable->primaryKey;
		
		return ( count( $_sPK ) === 2 // we want 2 columns
			&& isset( $oTable->foreignKeys[ $_sPK[ 0 ] ], $oTable->foreignKeys[ $_sPK[ 1 ] ] )	// Both PKs there...
			&& $oTable->foreignKeys[ $_sPK[ 0 ] ][ 0 ] !== $oTable->foreignKeys[ $_sPK[ 1 ]][ 0 ] ); // and the foreign keys point different tables
	}

	/**
	* Generate code to put in ActiveRecord class's relations() function.
	* 
	* @return array indexed by table names, each entry contains array of 
	* php code to go in appropriate ActiveRecord class. Empty array is returned 
	* if database couldn't be connected.
	*/
	protected function generateRelations()
	{
		$_arRelations = $_arClasses = array();
		
		foreach ( $this->schema->getTables() as $_oTable )
		{
			$_sTableName = $_oTable->name;

			if ( $this->isRelationTable( $_oTable ) )
			{
				$_arPK = $_oTable->primaryKey;
				$_arFK = $_oTable->foreignKeys;

				$_sTableParent = $_arFK[ $_arPK[ 1 ]][ 0 ];
				$_sTableChild = $_arFK[ $_arPK[ 0 ]][ 0 ];
				
				$_sParentClassName = $this->getClassName( $_sTableParent );
				$_sChildClassName = $this->getClassName( $_sTableChild );

				$_sRelationName = $this->generateRelationName( $_sTableParent, $_sTableChild, true );
				$_arRelations[ $_sParentClassName ][ $_sRelationName ] = "array( self::MANY_MANY, '$_sChildClassName', '$_sTableName( $_arPK[0], $_arPK[1] )' )";

				$_sRelationName  =$this->generateRelationName( $_sTableChild, $_sTableParent, true );
				$_arRelations[ $_sChildClassName ][ $_sRelationName ] = "array( self::MANY_MANY, '$_sParentClassName', '$_sTableName( $_arPK[0], $_arPK[1] )' )";
			}
			else
			{
				$_arClasses[ $_sTableName ] = $_sClassName = $this->getClassName( $_sTableName );
				
				foreach ( $_oTable->foreignKeys as $_sFKName => $_arFKEntry )
				{
					//	Put table and key name in variables for easier reading
					$_sParentTableName = $_arFKEntry[ 0 ]; // Table name that current fk references to
					$_sParentTableKey = $_arFKEntry[ 1 ];   // Key in that table being referenced
					$_sParentClassName = $this->getClassName( $_sParentTableName );

					//	Add relation for this table
					$_sRelationName = $this->generateRelationName( $_sTableName, $_sFKName, false );
					$_arRelations[ $_sClassName ][ $_sRelationName ] = "array( self::BELONGS_TO, '$_sParentClassName', '$_sFKName' )";

					//	Add relation for the referenced table
					$_sRelationType = $_oTable->primaryKey === $_sFKName ? 'HAS_ONE' : 'HAS_MANY';
					$_sRelationName = $this->generateRelationName( $_sParentTableName, $_sTableName, $_sRelationType === 'HAS_MANY' );
					$_arRelations[ $_sParentClassName ][ $_sRelationName ] = "array( self::$_sRelationType, '$_sClassName', '$_sFKName' )";
				}
			}
		}
		
		$this->relations = $_arRelations;
		$this->classes = $_arClasses;
	}

	/**
	 * Generates model class name based on a table name
	 * 
	 * @param string the table name
	 * @return string the generated model class name
	 */
	protected function generateClassName( $sTable )
	{
		return str_replace( ' ', '',
			ucwords(
				trim(
					strtolower(
						str_replace( array( '-', '_' ), ' ',
							preg_replace( '/(?<![A-Z])[A-Z]/', ' \0', $sTable )
						)
					)
				)
			)
		);
	}

	/**
	* Generates the mapping table between table names and class names.
	* 
	* @param CDbSchema the database schema
	* @param string a regular expression that may be used to filter table names
	*/
	protected function generateClassNames( $oSchema, $sPattern = null )
	{
		$_arTables = array();
		
		foreach ( $oSchema->getTableNames() as $_sTableName )
		{
			if ( $sPattern === null ) 
				$_arTables[ $_sTableName ] = $this->generateClassName( $_sTableName );
			else if ( preg_match( $sPattern, $_sTableName, $_arMatches ) )
			{
				if ( count( $_arMatches ) > 1 && ! empty( $_arMatches[ 1 ] ) )
					$_sClassName = $this->generateClassName( $_arMatches[ 1 ] );
				else
					$_sClassName = $this->generateClassName( $_arMatches[ 0 ] );
					
				$_arTables[ $_sTableName ] = PS::nvl( $_sClassName, $_sTableName );
			}
		}
		
		$this->tables = $_arTables;
	}

	/**
	* Generate a name for use as a relation name (inside relations() function in a model).
	* 
	* @param string the name of the table to hold the relation
	* @param string the foreign key name
	* @param boolean whether the relation would contain multiple objects
	*/
	protected function generateRelationName( $sTableName, $sFKName, $bMultiple )
	{
		if ( strcasecmp( substr( $sFKName, -2 ), 'id' ) === 0 && strcasecmp( $sFKName, 'id' ) )
			$_sRelationName = rtrim( substr( $sFKName, 0, -2 ), '_' );
		else
			$_sRelationName = $sFKName;
			
		$_sRelationName[ 0 ] = strtolower( $_sRelationName );

		$_sRawName = $_sRelationName;
		
		if ( $bMultiple ) $_sRelationName = $this->pluralize( $_sRelationName );

		$_oTable = $this->schema->getTable( $sTableName );
		
		$_i = 0;
		
		while ( isset( $_oTable->columns[ $_sRelationName ] ) )
			$_sRelationName = $_sRawName . ( $_i++ );
			
		return $_sRelationName;
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
				'relations' => 'array:array()',
				'tables' => 'array:array()',
				'classes' => 'array:array()',
			)
		);
	}
	
}
