<?php
/**
 * Property Bag
 */

class KickAssProps
{
	/**
	 * gets a property from a database bag.
	 * If $remove is true, item is deleted after retrieved.
	 * @param string $key
	 * @param mixed $defaultValue
	 * @param bool $remove
	 * @return mixed
	 */
	public function getProperty( $key, $defaultValue = null, $remove = false )
	{
		$_result = $defaultValue;

		//	Lookup key
		if ( null === ( $_model = $this->_loadProperty( $key ) ) )
		{
			$_model = new KickAssData();
			$_model->key_text = $key;
			$_model->value_text = self::_serialize( $_result );

			if ( ! $_model->save() )
			{
				//	bitch alot...
			}
		}
		else
			$_result = self::_unserialize( $_model->value_text );

		if ( $remove )
		{
			if ( ! $_model->delete() )
			{
				//	bitch alot...
			}
		}

		return $_result;
	}

	/**
	 * sets a property in a database bag.
	 * @param string $key
	 * @param mixed $value
	 * @return \KickAssProps $this
	 */
	public function setProperty( $key, $value = null )
	{
		//	Lookup key
		if ( null === ( $_model = $this->_loadProperty( $key ) ) )
		{
			$_model = new KickAssData();
			$_model->key_text = $key;
		}

		$_model->value_text = self::_serialize( $value );

		if ( ! $_model->save() )
		{
			//	bitch alot...
		}

		//	Allow chaining...
		return $this;
	}

	/**
	 * Retrieves a property from the database bag
	 * @param string $key
	 * @return KickAssData
	 */
	protected function _loadProperty( $key )
	{
		//	Example sql
		//	$_sql = 'select * from kick_ass_data_t where key_text = :key_text';

		//	read row from database however you want... this is how I'd do it in Yii (http://www.yiiframework.com)
		$_model = KickAssData::model()->find(
			'key_text = :key_text',
			array(
				 ':key_text' => $key
			)
		);

		return $_model;
	}

	/**
	 * Serializer that can handle SimpleXmlElement objects
	 * @param mixed $value
	 * @return mixed
	 */
	protected static function _serialize( $value )
	{
		try
		{
			if ( $value instanceof SimpleXMLElement )
				return $value->asXML();

			if ( is_object( $value ) )
				return serialize( $value );
		}
		catch ( Exception $_ex )
		{
		}

		return $value;
	}

	/**
	 * Unserializer that can handle SimpleXmlElement objects
	 * @param mixed $value
	 * @return mixed
	 */
	protected static function _unserialize( $value )
	{
		try
		{
			if ( self::_isSerialized( $value ) )
			{
				if ( $value instanceof SimpleXMLElement )
					return simplexml_load_string( $value );

				return @unserialize( $value );
			}
		}
		catch ( Exception $_ex )
		{
		}

		return $value;
	}

	/**
	 * Tests if a value needs unserialization by unserializing the value then 
	 * re-serializing. If both are successful then it's cool. I know this is 
	 * slower but it guarantees data integrity in my database.
	 * @param mixed $value
	 * @return boolean
	 */
	protected static function _isSerialized( $value )
	{
		return !( false === @unserialize( $value ) && $value != @serialize( false ) );
	}
}
