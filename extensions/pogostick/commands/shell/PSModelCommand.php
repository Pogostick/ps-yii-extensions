<?php
/**
 * CPSCrudCommand class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Commands
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
 
Yii::import( 'system.cli.commands.shell.ModelCommand' );

define( 'POGOSTICK_MODEL_TEMPLATES', Yii::getPathOfAlias('pogostick') . '/templates' );

class PSModelCommand extends ModelCommand
{
	public function __construct()
	{
		$this->templatePath = POGOSTICK_MODEL_TEMPLATES;
	}

	public function getHelp()
	{
		return <<<EOD
USAGE
  psmodel <class-name> [table-name] [bc=baseClass] [db=databaseName]

DESCRIPTION
  This command generates a Pogostick-based model class with the specified class name.

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

 * baseClass: optional, the base class to use when generating the model

 * databaseName: optional, the database component to use when generating
   the model
 
EXAMPLES
 * Generates the Post model:
        psmodel Post

 * Generates the Post model which is associated with table 'posts':
        psmodel Post posts

 * Generates the Post model which should belong to module 'admin':
        psmodel admin.models.Post

 * Generates a model class for every table in the database 'db3':
        psmodel * db=db3

 * Same as above, but the model class files should be generated
   under 'protected/models2':
        model application.models2.*

 * Generates a model class for every table whose name is prefixed
   with 'tbl_' in the current database. The model class will not
   contain the table prefix.
        model /^tbl_(.*)$/

 * Same as above, but the model class files should be generated
   under 'protected/models2':
        model application.models2./^tbl_(.*)$/

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run( $arArgs )
	{
		$_sDBToUse = 'db';
		$_sBCToUse = 'CActiveRecord';

		if ( ! isset( $arArgs[0] ) )
		{
			echo "Error: model class name is required.\n";
			echo $this->getHelp();
			return;
		}
		
		$className = $arArgs[0];

		foreach ( $arArgs as $_sArg )
		{
			//	Database to use
			if ( stripos( $_sArg, 'db=', 0 ) !== false ) $_sDBToUse = str_ireplace( 'db=', '', $_sArg );
		}
		
		//	Get database
		$db = ( $_sDBToUse != 'db' ) ? Yii::app()->{$_sDBToUse} : Yii::app()->getDb();

		if ( $db === null )
		{
			echo "Error: an active '{$_sDBToUse}' connection is required.\n";
			echo "If you already added '{$_sDBToUse}' component in application configuration,\n";
			echo "please quit and re-enter the yiic shell.\n";
			return;
		}

		$db->active = true;
		$this->_schema = $db->schema;

		if ( ! preg_match('/^[\w\.\-\*]*(.*?)$/',$className,$matches ) )
		{
			echo "Error: model class name is invalid.\n";
			return;
		}

		if(empty($matches[1]))  // without regular expression
		{
			$this->generateClassNames($this->_schema);
			if(($pos=strrpos($className,'.'))===false)
				$basePath=Yii::getPathOfAlias('application.models');
			else
			{
				$basePath=Yii::getPathOfAlias(substr($className,0,$pos));
				$className=substr($className,$pos+1);
			}
			if($className==='*') // generate all models
				$this->generateRelations();
			else
			{
				$tableName=isset($arArgs[1])?$arArgs[1]:$className;
				$this->_tables[$tableName]=$className;
				$this->generateRelations();
				$this->_classes=array($tableName=>$className);
			}
		}
		else  // with regular expression
		{
			$pattern=$matches[1];
			$pos=strrpos($className,$pattern);
			if($pos>0)  // only regexp is given
				$basePath=Yii::getPathOfAlias(rtrim(substr($className,0,$pos),'.'));
			else
				$basePath=Yii::getPathOfAlias('application.models');
			$this->generateClassNames($this->_schema,$pattern);
			$classes=$this->_tables;
			$this->generateRelations();
			$this->_classes=$classes;
		}

		if(count($this->_classes)>1)
		{
			$entries=array();
			$count=0;
			foreach($this->_classes as $tableName=>$className)
				$entries[]=++$count.". $className ($tableName)";
			echo "The following model classes (tables) match your criteria:\n";
			echo implode("\n",$entries);
			echo "\n\nDo you want to generate the above classes? [Yes|No] ";
			if(strncasecmp(trim(fgets(STDIN)),'y',1))
				return;
		}

		$templatePath=$this->templatePath===null?YII_PATH.'/cli/views/shell/model':$this->templatePath;

		$list=array();
		foreach ($this->_classes as $tableName=>$className)
		{
			$files[$className]=$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';
			$list[$className.'.php']=array(
				'source'=>$templatePath.DIRECTORY_SEPARATOR.'model.php',
				'target'=>$classFile,
				'callback'=>array($this,'generateModel'),
				'params'=>array($className,$tableName,$_sBCToUse,$_sDBToUse),
			);
		}

		$this->copyFiles($list);

		foreach($files as $className=>$file)
		{
			if(!class_exists($className,false))
				include_once($file);
		}

		$classes=implode(", ", $this->_classes);

		echo <<<EOD

The following model classes are successfully generated:
    $classes

If you have a 'db' database connection, you can test these models now with:
    \$model={$className}::model()->find();
    print_r(\$model);

EOD;
	}

	public function generateModel($source,$params)
	{
		list( $className, $tableName, $baseClass, $dbToUse ) = $params;
		$content=file_get_contents($source);
		$rules=array();
		$labels=array();
		$relations=array();
		if(($table=$this->_schema->getTable($tableName))!==null)
		{
			$required=array();
			$integers=array();
			$numerical=array();
			foreach($table->columns as $column)
			{
				$label=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
				$label=preg_replace('/\s+/',' ',$label);
				if(strcasecmp(substr($label,-3),' id')===0)
					$label=substr($label,0,-3);
				$labels[$column->name]=$label;
				if($column->isPrimaryKey && $table->sequenceName!==null || $column->isForeignKey)
					continue;
				if(!$column->allowNull && $column->defaultValue===null)
					$required[]=$column->name;
				if($column->type==='integer')
					$integers[]=$column->name;
				else if($column->type==='double')
					$numerical[]=$column->name;
				else if($column->type==='string' && $column->size>0)
					$rules[]="array( '{$column->name}', 'length', 'max' => {$column->size} )";
			}
			if($required!==array())
				$rules[]="array( '".implode(', ',$required)."', 'required' )";
			if($integers!==array())
				$rules[]="array( '".implode(', ',$integers)."', 'numerical', 'integerOnly' => true )";
			if($numerical!==array())
				$rules[]="array( '".implode(', ',$numerical)."', 'numerical' )";

			if(isset($this->_relations[$className]) && is_array($this->_relations[$className]))
				$relations=$this->_relations[$className];
		}
		else
			echo "Warning: the table '$tableName' does not exist in the database.\n";

		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/model/'.basename($source);

		return $this->renderFile($source,array(
			'className'=>$className,
			'tableName'=>$tableName,
			'baseClass' => $baseClass,
			'dbToUse' => $dbToUse,
			'columns'=>isset($table) ? $table->columns : array(),
			'rules'=>$rules,
			'labels'=>$labels,
			'relations'=>$relations,
		),true);
	}
}
