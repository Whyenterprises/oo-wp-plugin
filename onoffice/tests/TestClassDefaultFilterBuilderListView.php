<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\WP_UnitTest_Localized;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewEnvironment;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\Filter\DefaultFilterBuilderListView
 *
 */

class TestClassDefaultFilterBuilderListView
	extends WP_UnitTest_Localized
{
	/** @var DefaultFilterBuilderListViewEnvironment */
	private $_pEnvironment = null;

	/** @var InputVariableReaderConfigTest */
	private $_pInputVariableReaderConfig = null;

	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$this->switchLocale('en_US');
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepareMock()
	{
		$pEnvironment = $this->getMock(DefaultFilterBuilderListViewEnvironment::class, [
			'getFilterBuilderInputVariables',
			'getInputVariableReader',
			'getRegionController',
		]);

		$this->_pInputVariableReaderConfig = new InputVariableReaderConfigTest();

		$pEnvironment
			->method('getFilterBuilderInputVariables')
			->will($this->returnValue($this->getMock(FilterBuilderInputVariables::class, null, [
				onOfficeSDK::MODULE_ESTATE, true, $this->_pInputVariableReaderConfig])));
		$pEnvironment
			->method('getInputVariableReader')
			->will($this->returnValue
				($this->getMock(InputVariableReader::class, null,
					[onOfficeSDK::MODULE_ESTATE, $this->_pInputVariableReaderConfig])));
		$pRegionControllerMock = $this->getMock(RegionController::class,
				['fetchRegions', 'getSubRegionsByParentRegion'], [false]);
		$pRegionControllerMock
			->method('getSubRegionsByParentRegion')
			->with('OstfriesischeInseln')
			->will($this->returnValue
				(['Norderney', 'Baltrum', 'Borkum', 'Juist', 'Langeoog', 'Spiekeroog', 'Wangerooge']));
		$pEnvironment
			->method('getRegionController')
			->will($this->returnValue($pRegionControllerMock));

		$this->_pEnvironment = $pEnvironment;
	}


	/**
	 *
	 */

	public function testDefaultFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $this->_pEnvironment);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
		];

		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testReferenceViewFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setListType(DataListView::LISTVIEW_TYPE_REFERENCE);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $this->_pEnvironment);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'referenz' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
		];


		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testFavoritesViewFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setListType(DataListView::LISTVIEW_TYPE_FAVORITES);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $this->_pEnvironment);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'Id' => [
				[
					'op' => 'in',
					'val' => [0],
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testRegionForFavorites()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields(['regionaler_zusatz']);
		$pDataListView->setListType(DataListView::LISTVIEW_TYPE_FAVORITES);

		$pInstance = new DefaultFilterBuilderListView($pDataListView, $this->_pEnvironment);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('regionaler_zusatz', onOfficeSDK::MODULE_ESTATE, FieldTypes::FIELD_TYPE_MULTISELECT);
		$this->_pInputVariableReaderConfig->setValue('regionaler_zusatz', 'OstfriesischeInseln');

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'Id' => [
				[
					'op' => 'in',
					'val' => [0],
				],
			],
			'regionaler_zusatz' => [
				[
					'op' => 'in',
					'val' => [
						'Norderney',
						'Baltrum',
						'Borkum',
						'Juist',
						'Langeoog',
						'Spiekeroog',
						'Wangerooge',
						'OstfriesischeInseln',
					],
				],
			],
		];

		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testInputVarsArray()
	{
		$pDataListView = new DataListView(1, 'test');
		// geoPosition should get removed
		$pDataListView->setFilterableFields(['testtext', 'othertest', 'geoPosition']);

		$module = onOfficeSDK::MODULE_ESTATE;
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_MULTISELECT);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('othertest', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$this->_pInputVariableReaderConfig->setValueArray('testtext', ['asd' , 'hello']);
		$this->_pInputVariableReaderConfig->setValueArray('othertest', ['bonjour' , 'salve']);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $this->_pEnvironment);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'testtext' => [
				[
					'op' => 'in',
					'val' => ['asd', 'hello'],
				],
			],
			'othertest' => [
				[
					'op' => 'in',
					'val' => ['bonjour', 'salve'],
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}



	/**
	 *
	 */

	public function testEmptyInput()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields([
			'testtext', 'othertest', 'text', 'number_int', 'number_float', 'bool']);

		$module = onOfficeSDK::MODULE_ESTATE;
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_MULTISELECT);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('othertest', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('text', $module, FieldTypes::FIELD_TYPE_TEXT);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('number_int', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('number_float', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$this->_pInputVariableReaderConfig->setFieldTypeByModule
			('bool', $module, FieldTypes::FIELD_TYPE_BOOLEAN);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $this->_pEnvironment);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}
}
