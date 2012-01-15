<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSModel provides base functionality for models
 *
 * @package 	psYiiExtensions
 * @subpackage 	models
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSModel.php 401 2010-08-31 21:04:18Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *
 * @filesourcet
 *
 * @property-read string $modelName The class name of the model
 */
class CPSModel extends CActiveRecord implements IPSBase
{
	//*******************************************************************************
	//* Members
	//*******************************************************************************

	/**
	 * Our schema, cached for speed
	 * @property-read array $schema
	 */
	protected $_schema;
	/**
	 * The associated database table name prefix.
	 * If Yii version is greater than 1.0, the dbConnection's table prefix for this model will be set.
	 * @property string $tablePrefix
	 */
	protected $_tablePrefix = null;
	/**
	 * Attribute labels cache
	 * @property array $attributeLabels
	 */
	protected $_attributeLabels = array( );
	/**
	 * Get's the name of the model class
	 * @property string $modelClass
	 */
	protected $_modelClass = null;
	/**
	 * @property array $transactionStack Our private little transaction stack
	 */
	protected static $_transactionStack = array( );

	//*******************************************************************************
	//* Properties
	//*******************************************************************************

	/**
	 * Get this model's name.
	 * @return string
	 */
	public function getModelName()
	{
		return $this->_modelClass;
	}

	/**
	 * Get this model's class name.
	 * @return string
	 */
	public function getModelClass()
	{
		return $this->_modelClass;
	}

	/**
	 * Set this model's name
	 * @param string $sValue
	 */
	public function setModelName( $sValue )
	{
		$this->_modelClass = $sValue;
	}

	/**
	 * Sets this model's name.
	 * @param string $modelClass
	 */
	public function setModelClass( $modelClass )
	{
		$this->_modelClass = $modelClass;
	}

	/**
	 * Returns this model's schema
	 * @return array()
	 */
	public function getSchema()
	{
		return $this->_schema ? $this->_schema : $this->_schema = $this->getMetaData()->columns;
	}

	/**
	 * @return string $tablePrefix The prefix for the table
	 */
	public function getTablePrefix()
	{
		//	Does db have one?
		if ( version_compare( YiiBase::getVersion(), '1.1.0' ) > 0 )
		{
			if ( null !== ( $_db = $this->getDbConnection() ) ) return $_db->tablePrefix;

			return null;
		}

		//	Return ours...
		return $this->_tablePrefix;
	}

	/**
	 *
	 * @param string $prefix The prefix for the table
	 * @param boolean $thisTableOnly If true, the prefix will be set for this table, not all tables
	 * @return string The new prefix
	 */
	public function setTablePrefix( $prefix, $thisTableOnly = false )
	{
		if ( ! $thisTableOnly && version_compare( YiiBase::getVersion(), '1.1.0' ) > 0 && null !== ( $_db = $this->getDbConnection() ) )
			return $_db->tablePrefix = $prefix;

		return $this->_tablePrefix = $prefix;
	}

	/**
	 * Returns all attribute latbels and populates cache
	 * @return array
	 * @see CPSModel::attributeLabels
	 */
	public function getAttributeLabels()
	{
		return $this->_attributeLabels ? $this->_attributeLabels : $this->_attributeLabels = $this->attributeLabels();
	}

	/**
	 * Returns the text label for the specified attribute.
	 * @param string $attribute The attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 * @see getAttributeLabels
	 */
	public function getAttributeLabel( $attribute )
	{
		$_labels = $this->getAttributeLabels();
		$_defaultLabel = $this->generateAttributeLabel( $attribute );

		return PS::o( $_labels, $attribute, $_defaultLabel );
	}

	/**
	 * Override of CModel::setAttributes
	 * Populates member variables as well.
	 * Aware of Yii 1.1.0+
	 *
	 * @param array $attributes
	 */
	public function setAttributes( $attributes, $safeOnly = true )
	{
		if ( version_compare( Yii::getVersion(), '1.1.0', '>=' ) )
		{
			if ( ! is_array( $attributes ) )
				return;

			$_attributes = array_flip( $safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames()  );

			foreach ( $attributes as $_column => $_value )
			{
				if ( isset( $_attributes[$_column] ) )
					$this->setAttribute( $_column, $_value );
				else
				{
					if ( $this->hasProperty( $_column ) && $this->canSetProperty( $_column ) )
						$this->{$_column} = $_value;
				}
			}
		}
		else
		{
			$_scenario = ( $safeOnly ? $this->getScenario() : $safeOnly );

			if ( is_array( $attributes ) )
			{
				$_attributes = array_flip( $this->getSafeAttributeNames( $_scenario ) );

				foreach ( $attributes as $_column => $_value )
				{
					if ( isset( $_attributes[$_column] ) || ( $this->hasProperty( $_column ) && $this->canSetProperty( $_column ) ) ) $this->setAttribute( $_column, $_value );
				}
			}
		}
	}

	//*******************************************************************************
	//* Public Methods
	//*******************************************************************************

	/**
	 * Builds a CPSModel and sets the model name
	 *
	 * @param string $_scenario
	 * @return CPSModel
	 */
	public function __construct( $_scenario = 'insert' )
	{
		parent::__construct( $_scenario );
		$this->_modelClass = get_class( $this );
	}

	/**
	 * Checks if a component has an attached behavior
	 * @param string $className
	 * @return boolean
	 */
	public function hasBehavior( $className )
	{
		//	Look for behaviors
		foreach ( $this->behaviors() as $_column => $_behaviors )
		{
			if ( null !== ( $_class = PS::o( $_behaviors, 'class' ) ) )
				$_class = Yii::import( $_class );

			//	Check...
			if ( $className == $_column || $className == $_class )
				return true;
		}

		//	Nope!
		return false;
	}

	/**
	 * Sets our default behaviors.
	 * All CPSModel's have the DataFormat and Utility behaviors added by default.
	 * @return array
	 * @see CModel::behaviors
	 */
	public function behaviors()
	{
		return array_merge(
			parent::behaviors(),
			array(
				//	Date/time formatter
				'psDataFormat' => array(
					'class' => 'pogostick.behaviors.CPSDataFormatBehavior',
				),
			)
		);
	}

	/**
	 * Returns the errors on this model in a single string suitable for logging.
	 * @param string $attribute Attribute name. Use null to retrieve errors for all attributes.
	 * @return string
	 */
	public function getErrorsForLogging( $attribute = null )
	{
		$_result = null;
		$_i = 1;

		$_errors = $this->getErrors( $attribute );

		if ( ! empty( $_errors ) )
		{
			foreach ( $_errors as $_attribute => $_error )
				$_result .= $_i++ . '. [' . $_attribute . '] : ' . implode( '|', $_error );
		}

		return $_result;
	}

	/**
	 * PHP sleep magic method.
	 * Take opportunity to flush schema cache...
	 * @return array
	 */
	public function __sleep()
	{
		//	Clean up and phone home...
		$this->_schema = null;
		return parent::__sleep();
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions. Override for more specific search criteria.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria();

		foreach ( $this->getTableSchema()->columns as $_column )
		{
			if ( 'string' == $_column->type )
				$_criteria->compare( $_column->name, $this->{$_column->name}, true );
		}

		return new CActiveDataProvider( $this->_modelClass, array( 'criteria' => $_criteria ) );
	}

	public function generateAdvancedSearchForm( $addHeader = true, $returnOutput = false )
	{
		$_header = ( $addHeader ? '<?php' : null );
		$_searchFields = null;

		$_criteria = $this->search()->getCriteria();

		foreach ( $this->getTableSchema()->getColumnNames() as $_columnName )
		{
			if ( false !== ( stripos( $_criteria->condition, $_columnName ) ) )
				$_searchFields .= '$_fieldList[] = array( PS::TEXT, \'' . $_columnName . '\' );' . PHP_EOL;
		}

		$_output = <<<HTML
{$_header}
/**
 * Advanced Search Partial Form
 */

PS::setShowRequiredLabel( false );

\$_formOptions = array(
	'formModel' => \$model,
	'formClass' => 'wide form ui-widget',
	'action' => PS::_cu( \$this->route ),
	'method' => 'GET',
	'showLegend' => false,
	'uiStyle' => PS::UI_JQUERY,
);

\$_fieldList = array();

\$_fieldList[] = array( 'html', '<fieldset><legend>Advanced Search</legend>' );
{$_searchFields}
\$_fieldList[] = array( 'html', PS::submitButton( 'Search', array( 'style' => 'float:right;margin-top:5px;' ) ) );
\$_fieldList[] = array( 'html', '</fieldset>' );

\$_formOptions['fields'] = \$_fieldList;

CPSForm::create( \$_formOptions );

HTML;

		if ( $returnOutput )
			return $_output;

		echo $_output;
	}

	/**
	 * Executes the SQL statement and returns all rows. (static version)
	 * @param mixed $criteria The criteria for the query
	 * @param boolean $fetchAssociative Whether each row should be returned as an associated array with column names as the keys or the array keys are column indexes (0-based).
	 * @param array $parameters input parameters (name=>value) for the SQL execution. This is an alternative to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing them in this way can improve the performance. Note that you pass parameters in this way, you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa. binding methods and  the input parameters this way can improve the performance. This parameter has been available since version 1.0.10.
	 * @return array All rows of the query result. Each array element is an array representing a row. An empty array is returned if the query results in nothing.
	 * @throws CException execution failed
	 * @static
	 */
	public static function queryAll( $criteria, $fetchAssociative = true, $parameters = array() )
	{
		if ( null !== ( $_builder = self::getDb()->getCommandBuilder() ) )
		{
			if ( null !== ( $_command = $_builder->createFindCommand( self::model()->getTableSchema(), $criteria ) ) )
				return $_command->queryAll( $fetchAssociative, $parameters );
		}

		return null;
	}

	/**
	 * Convenience method to execute a query (static version)
	 * @return integer The number of rows affected by the operation
	 * @throws CException Execution failed
	 * @static
	 */
	public static function execute( $sql, $parameters = array() )
	{
		return self::createCommand( $sql )->execute( $parameters );
	}

	/**
	 * Convenience method to get a database connection to a model's database
	 * @return CDbConnection
	 */
	public static function getDb()
	{
		return self::model()->getDbConnection();
	}

	/**
	 * Convenience method to get a database command model's database
	 * @return CDbCommand
	 */
	public static function createCommand( $sql )
	{
		return self::getDb()->createCommand( $sql );
	}

	//*******************************************************************************
	//* Transaction Management
	//*******************************************************************************

	/**
	 * Checks to see if there are any transactions going...
	 * @return boolean
	 */
	public function hasTransaction()
	{
		return ( 0 != count( self::$_transactionStack ) );
	}

	/**
	 * Begins a database transaction (PHP 5.3+ only)
	 * @throws CDbException
	 * @static
	 */
	public static function beginTransaction()
	{
		$_transaction = null;

		if ( version_compare( PHP_VERSION, '5.3.0' ) > 0 )
		{
			$_modelClass = get_called_class();
			$_model = new $_modelClass;
			$_transaction = $_model->getDbConnection()->beginTransaction();
			array_push( self::$_transactionStack, $_transaction );
		}

		return $_transaction;
	}

	/**
	 * Commits the transaction at the top of the stack, if any.
	 * @throws CDbException
	 * @static
	 */
	public static function commitTransaction()
	{
		/** @var $_transaction CDbTransaction */
		if ( null !== ( $_transaction = array_pop( self::$_transactionStack ) ) )
			$_transaction->commit();
	}

	/**
	 * Rolls back the current transaction, if any...
	 * @throws CDbException
	 * @static
	 */
	public static function rollbackTransaction( Exception $exception = null )
	{
		if ( null !== ( $_transaction = array_pop( self::$_transactionStack ) ) )
			$_transaction->rollback();

		//	Throw it if given
		if ( null !== $exception )
			throw $exception;
	}

	//*******************************************************************************
	//* REST Methods
	//*******************************************************************************

	/**
	 * If a model has a REST mapping, attributes are mapped an returned in an array.
	 * @return array|null The resulting view
	 */
	public function getRestAttributes()
	{
		if ( method_exists( $this, 'attributeRestMap' ) )
		{
			$_resultList = array( );
			$_columnList = $this->getSchema();

			foreach ( $this->attributeRestMap() as $_key => $_value )
			{
				$_attributeValue = $this->getAttribute( $_key );

				//	Apply formats
				switch ( $_columnList[$_key]->dbType )
				{
					case 'date':
					case 'datetime':
					case 'timestamp':
						//	Handle blanks
						if ( null !== $_attributeValue && $_attributeValue != '0000-00-00' && $_attributeValue != '0000-00-00 00:00:00' ) $_attributeValue = date( 'c', strtotime( $_attributeValue ) );
						break;
				}

				$_resultList[$_value] = $_attributeValue;
			}

			return $_resultList;
		}

		return null;
	}

	/**
	 * A more-better CActiveRecord update method. Pass in column => value to update.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * @param array $attributes list of attributes and values that need to be saved. Defaults to null, meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the update is successful
	 * @throws CException if the record is new
	 */
	public function update( $attributes = null )
	{
		$_columns = array();

		if ( null === $attributes )
		{
			return parent::update( $attributes );
		}

		foreach ( $attributes as $_column => $_value )
		{
			//	column => value specified
			if ( ! is_numeric( $_column ) )
			{
				$this->{$_column} = $_value;
			}
			else
			{
				//	n => column specified
				$_column = $_value;
			}

			$_columns[] = $_column;
		}

		return parent::update( $_columns );
	}

	/**
	 * Sets the values in the model based on REST attribute names
	 * @param array $attributeList
	 */
	public function setRestAttributes( $attributeList )
	{
		if ( method_exists( $this, 'attributeRestMap' ) )
		{
			CPSLog::trace( __METHOD__, '  - Setting REST attributes' );

			$_map = $this->attributeRestMap();

			foreach ( $attributeList as $_key => $_value )
			{
				if ( false !== ( $_mapKey = array_search( $_key, $_map ) ) ) $this->setAttribute( $_mapKey, $_value );
			}

			CPSLog::trace( __METHOD__, '  - REST attributes set' );
		}
	}

	//*******************************************************************************
	//* Event Handlers
	//*******************************************************************************

	/**
	 * Grab our name
	 */
	public function afterConstruct()
	{
		$this->_modelClass = get_class( $this );
		parent::afterConstruct();
	}

//	//*******************************************************************************
//	//* PHP 5.3+ Awesomeness!
//	//*******************************************************************************
//
//	/**
//	 * Returns a model for the currently called class
//	 * @static
//	 * @return CActiveRecord
//	 */
//	protected static function _model()
//	{
//		$_class = get_called_class();
//		return $_class::model();
//	}
//
//	/**
//	 * Finds a single active record with the specified condition.
//	 * @param mixed $condition query condition or criteria.
//	 * If a string, it is treated as query condition (the WHERE clause);
//	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria} object;
//	 * Otherwise, it should be an instance of {@link CDbCriteria}.
//	 * @param array $params parameters to be bound to an SQL statement.
//	 * This is only used when the first parameter is a string (query condition).
//	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
//	 * @return CActiveRecord the record found. Null if no record is found.
//	 */
//	public static function find( $condition = '', $params = array() )
//	{
//		return self::_model()->find( $condition, $params );
//	}
//
//	/**
//	 * Finds all active records satisfying the specified condition.
//	 * See {@link find()} for detailed explanation about $condition and $params.
//	 * @param mixed $condition query condition or criteria.
//	 * @param array $params parameters to be bound to an SQL statement.
//	 * @return array list of active records satisfying the specified condition. An empty array is returned if none is found.
//	 */
//	public static function findAll( $condition = '', $params = array() )
//	{
//		return self::_model()->findAll( $condition, $params );
//	}
//
//	/**
//	 * Finds a single active record with the specified primary key.
//	 * See {@link find()} for detailed explanation about $condition and $params.
//	 * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
//	 * @param mixed $condition query condition or criteria.
//	 * @param array $params parameters to be bound to an SQL statement.
//	 * @return CActiveRecord the record found. Null if none is found.
//	 */
//	public static function findByPk( $condition = '', $params = array() )
//	{
//		return self::_model()->findByPk( $condition, $params );
//	}
//
//	/**
//	 * Finds all active records with the specified primary keys.
//	 * See {@link find()} for detailed explanation about $condition and $params.
//	 * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
//	 * @param mixed $condition query condition or criteria.
//	 * @param array $params parameters to be bound to an SQL statement.
//	 * @return array the records found. An empty array is returned if none is found.
//	 */
//	public static function findAllByPk( $key, $condition = '', $params = array() )
//	{
//		return self::_model()->findAllByPk( $key, $condition, $params );
//	}
//
//	/**
//	 * Finds a single active record that has the specified attribute values.
//	 * See {@link find()} for detailed explanation about $condition and $params.
//	 * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
//	 * Since version 1.0.8, an attribute value can be an array which will be used to generate an IN condition.
//	 * @param mixed $condition query condition or criteria.
//	 * @param array $params parameters to be bound to an SQL statement.
//	 * @return CActiveRecord the record found. Null if none is found.
//	 */
//	public static function findByAttributes( $attributes, $condition = '', $params = array() )
//	{
//		return self::_model()->findByAttributes( $attributes, $condition, $params );
//	}
//
//	/**
//	 * Finds all active records that have the specified attribute values.
//	 * See {@link find()} for detailed explanation about $condition and $params.
//	 * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
//	 * Since version 1.0.8, an attribute value can be an array which will be used to generate an IN condition.
//	 * @param mixed $condition query condition or criteria.
//	 * @param array $params parameters to be bound to an SQL statement.
//	 * @return array the records found. An empty array is returned if none is found.
//	 */
//	public static function findAllByAttributes( $attributes, $condition = '', $params = array() )
//	{
//		return self::_model()->findAllByAttributes( $attributes, $condition, $params );
//	}
//
//	/**
//	 * Finds a single active record with the specified SQL statement.
//	 * @param string $sql the SQL statement
//	 * @param array $params parameters to be bound to the SQL statement
//	 * @return CActiveRecord the record found. Null if none is found.
//	 */
//	public static function findBySql( $sql, $params = array() )
//	{
//		return self::_model()->findBySql( $sql, $params );
//	}
//
//	/**
//	 * Finds all active records using the specified SQL statement.
//	 * @param string $sql the SQL statement
//	 * @param array $params parameters to be bound to the SQL statement
//	 * @return array the records found. An empty array is returned if none is found.
//	 */
//	public static function findAllBySql( $sql, $params = array() )
//	{
//		return self::_model()->findAllBySql( $sql, $params );
//	}
}
