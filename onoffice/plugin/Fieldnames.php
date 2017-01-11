<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\SDKWrapper;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class Fieldnames {
	/** @var Fieldnames */
	static private $_pInstance = null;

	/** @var array */
	private $_fieldList = array();

	/** @var array */
	private $_searchcriteriaRangeInfos = array();

	/**
	 *
	 */

	private function __construct() {}


	/**
	 *
	 */

	private function __clone() {}


	/**
	 *
	 * @return Fieldnames
	 *
	 */

	static public function getInstance() {
		if (is_null(self::$_pInstance)) {
			self::$_pInstance = new static();
		}

		return self::$_pInstance;
	}


	/**
	 *
	 * @param string $language
	 *
	 */

	public function loadLanguage( $language ) {
		if ( $this->hasLanguageCached( $language ) ) {
			return;
		}

		$parametersGetFieldList = array(
			'labels' => 1,
			'language' => $language,
		);

		$pSDKWrapper = new SDKWrapper();
		$handleGetFields = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSDKWrapper->sendRequests();

		$responseArrayFieldList = $pSDKWrapper->getRequestResponse( $handleGetFields );
		$fieldList = $responseArrayFieldList['data']['records'];

		$this->createFieldList( $fieldList, $language );
		$this->completeFieldListWithSearchcriteria( $language );
	}


	/**
	 *
	 * @param string $language
	 *
	 */

	private function completeFieldListWithSearchcriteria( $language ) {
		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();
		$requestParameter = array
			(
				'language' => $language,
				'additionalTranslations' => true,
			);

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', $requestParameter);
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		foreach ($response['data']['records'] as $tableValues)
		{
			$felder = $tableValues['elements'];

			foreach ($felder['fields'] as $field)
			{
				$fieldProperties = array();
				$fieldProperties['type'] = $field['type'];
				$fieldProperties['label'] = $field['name'];
				$fieldProperties['default'] = null;
				$fieldProperties['permittedvalues'] = array();

				if (array_key_exists('default', $field))
				{
					$fieldProperties['default'] = $field['default'];
				}

				if (array_key_exists('values', $field))
				{
					$fieldProperties['permittedvalues'] = $field['values'];
				}

				if (array_key_exists('rangefield', $field) &&
					$field['rangefield'] == true &&
					array_key_exists('additionalTranslations', $field))
				{
					$this->_searchcriteriaRangeInfos[$field['id']] = array();

					foreach ($field['additionalTranslations'] as $key => $value)
					{
						$this->_searchcriteriaRangeInfos[$field['id']][$key] = $value;
					}
				}

				$this->_fieldList[$language]['searchcriteria'][$field['id']] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function inRangeSearchcriteriaInfos($field)	{
		return array_key_exists($field, $this->_searchcriteriaRangeInfos);
	}


	/**
	 *
	 * @param string $field
	 * @return array
	 *
	 */

	public function getRangeSearchcriteriaInfosForField($field)	{
		$infos = array();

		if (array_key_exists($field, $this->_searchcriteriaRangeInfos))
		{
			$infos = $this->_searchcriteriaRangeInfos[$field];
		}

		return $infos;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchcriteriaRangeInfos() {
		return $this->_searchcriteriaRangeInfos;
	}


	/**
	 *
	 * @param string $language
	 *
	 */

	public function loadLanguageIfNotCached( $language ) {
		if ( ! $this->hasLanguageCached( $language ) ) {
			$this->loadLanguage( $language );
		}
	}


	/**
	 *
	 * @param array $fieldResult
	 * @param string $language
	 * @return null
	 *
	 */

	private function createFieldList( $fieldResult, $language ) {
		if ( count( $fieldResult ) == 0 ) {
			return;
		}

		foreach ( $fieldResult as $moduleProperties ) {
			if ( ! array_key_exists( 'elements', $moduleProperties ) ) {
				continue;
			}

			foreach ( $moduleProperties['elements'] as $fieldName => $fieldProperties ) {
				if ( 'label' == $fieldName ) {
					continue;
				}

				$this->_fieldList[$language][$moduleProperties['id']][$fieldName] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $module recordType
	 * @param string $language
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field, $module, $language ) {
		$fieldNewName = $field;

		if ( isset( $this->_fieldList[$language][$module] ) &&
			array_key_exists( $field, $this->_fieldList[$language][$module] ) ) {
			$fieldNewName = $this->_fieldList[$language][$module][$field]['label'];
		}

		return $fieldNewName;
	}


	/**
	 *
	 * @param string $fieldName
	 * @param string $module
	 * @param string $language
	 * @return string
	 *
	 */

	public function getType( $fieldName, $module, $language ) {
		return $this->_fieldList[$language][$module][$fieldName]['type'];
	}


	/**
	 *
	 * @param string $inputField
	 * @param string $module
	 * @param string $language
	 * @return string
	 *
	 */

	public function getPermittedValues( $inputField, $module, $language ) {
		return $this->_fieldList[$language][$module][$inputField]['permittedvalues'];
	}


	/**
	 *
	 * @param string $language
	 * @return bool
	 *
	 */

	public function hasLanguageCached( $language ) {
		return array_key_exists( $language, $this->_fieldList );
	}
}
