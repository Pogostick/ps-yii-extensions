<?php
class CPSCrudCode extends CrudCode
{
	/**
	 * @param string $modelClass
	 * @param CDbColumnSchema $column
	 * @return string
	 */
	public function generateInputField( $modelClass, $column )
	{
		$_type = 'TEXT';

		if ( 'boolean' === $column->type )
			$_type = 'CHECK';
		else if ( false !== stripos( $column->dbType, 'text' ) )
			$_type = 'TEXTAREA';

		if ( preg_match( '/^(password|pass|passwd|passcode)$/i', $column->name ) )
			$_type = 'PASSWORD';

		if ( ( $_size = $_maxLength = $column->size ) > 60 )
			$_size = ', array( \'size\' => ' . $_size . ', \'maxlength\' => ' . $_maxLength . ' )';
		else
			$_size = ', array( \'size\' => 60, \'maxlength\' => 50 )';

		return 'array( PS::' . $_type . ', \'' . $column->name . '\', ' . $_size . ' ),';
	}

	/**
	 * @param string $modelClass
	 * @param CDbColumnSchema $column
	 * @return string
	 */
	public function generateActiveField( $modelClass, $column )
	{
		return $this->generateInputField( $modelClass, $column );
	}
}