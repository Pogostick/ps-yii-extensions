<?php
class CPSOptionTest extends CTestCase
{
	protected $_oOption;

	public function setUp()
	{
		$this->_oOption = new CPSOption('testName','testValue','bool:true');
	}

	public function tearDown()
	{
		$this->_oOption = null;
	}

	public function testOption()
	{
		$this->assertTrue($this->_oOption->getName() == 'testName', "Option does not have correct name.");
		$this->assertTrue($this->_oOption->getValue() == 'testValue', "Option does not have correct value from construction.");
		
		$this->_oOption->value = 'newValue';
		$this->assertTrue( $this->_oOption->value == 'newValue', "Option did not save new value (via __get#1).");
		$this->assertTrue( $this->_oOption->getValue() == 'newValue', "Option did not save new value  (via getValue).");
		$this->assertTrue( $this->_oOption->value == 'newValue', "Option did not save new value (via __get#2/persistence).");
		
		$this->assertTrue($this->_oOption->getRule(CPSOption::RPT_TYPE) === 'boolean', "Option does not have correct type: " . $this->_oOption->getRule(CPSOption::RPT_TYPE) );
		$this->assertTrue($this->_oOption->isPrivate === false, "Option should be public." );

		$this->_oOption->isPrivate = true;
		$this->assertTrue($this->_oOption->getIsPrivate() === true, "Option should be private (via getIsPrivate)." );
		$this->assertTrue($this->_oOption->isPrivate === true, "Option should be private (via __get)." );
		$this->assertTrue($this->_oOption->defaultValue === true, "Option does not have correct default value (via __get)." );
	}

}
