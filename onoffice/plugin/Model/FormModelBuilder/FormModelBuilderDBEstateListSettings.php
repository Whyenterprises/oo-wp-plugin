<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Types\ImageTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderDBEstateListSettings
	extends FormModelBuilderDBEstate
{
	/** @var string[] */
	private static $_defaultFields = array(
		'objekttitel',
		'objektart',
		'objekttyp',
		'vermarktungsart',
		'plz',
		'ort',
		'bundesland',
		'objektnr_extern',
		'wohnflaeche',
		'grundstuecksflaeche',
		'nutzflaeche',
		'anzahl_zimmer',
		'anzahl_badezimmer',
		'kaufpreis',
		'kaltmiete',
	);


	/**
	 *
	 * @param int $listViewId
	 * @return FormModel
	 *
	 */

	public function generate($listViewId = null)
	{
		if ($listViewId !== null)
		{
			$pRecordReadManager = new RecordManagerReadListViewEstate();
			$values = $pRecordReadManager->getRowById($listViewId);
			$this->setValues($values);
		}
		else
		{
			$this->setValues(array(
				DataListView::FIELDS => self::$_defaultFields,
			));
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('List View', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-listview-settings-main');
		$pFormModel->setPageSlug($this->getPageSlug());

		return $pFormModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFilter()
	{
		$labelFiltername = __('Filter', 'onoffice');
		$pInputModelFiltername = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_FILTERID, $labelFiltername);
		$pInputModelFiltername->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ESTATE);

		$pInputModelFiltername->setValuesAvailable($availableFilters);
		$filteridSelected = $this->getValue($pInputModelFiltername->getField());
		$pInputModelFiltername->setValue($filteridSelected);

		return $pInputModelFiltername;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsFilterable()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Filterable', 'onoffice');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_FILTERABLE;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsFilterable'));

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelAvailableOptions()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Available Options', 'onoffice');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_AVAILABLE_OPTIONS;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelAvailableOptions'));

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsHidden()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Hidden', 'onoffice');
		$type = InputModelDBFactoryConfigEstate::INPUT_FIELD_HIDDEN;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsHidden'));

		return $pInputModel;
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 * @return bool
	 *
	 */

	public function callbackValueInputModelIsFilterable(InputModelBase $pInputModel, $key)
	{
		$valueFromConf = $this->getValue('filterable');
		$filterableFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $filterableFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 * @return bool
	 *
	 */

	public function callbackValueInputModelIsHidden(InputModelBase $pInputModel, $key)
	{
		$valueFromConf = $this->getValue('hidden');
		$filterableFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $filterableFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelAvailableOptions(InputModelBase $pInputModel, string $key)
	{
		$valueFromConf = $this->getValue('availableOptions');

		$availableOptionsFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $availableOptionsFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param string $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$pSortableFieldsList = parent::createSortableFieldList($module, $htmlType);
		$pInputModelIsFilterable = $this->getInputModelIsFilterable();
		$pInputModelIsHidden = $this->getInputModelIsHidden();
		$pInputModelIsAvailableOptions = $this->getInputModelAvailableOptions();
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsFilterable);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsHidden);
		$pSortableFieldsList->addReferencedInputModel($pInputModelIsAvailableOptions);

		return $pSortableFieldsList;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelListType()
	{
		$labelListType = __('Type of List', 'onoffice');
		$pInputModelListType = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_LIST_TYPE, $labelListType);
		$pInputModelListType->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelListType->setValue($this->getValue($pInputModelListType->getField()));
		$pInputModelListType->setValuesAvailable(self::getListViewLabels());

		return $pInputModelListType;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelShowStatus()
	{
		$labelShowStatus = __('Show Estate Status', 'onoffice');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SHOW_STATUS, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('show_status'));
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$allPictureTypes = ImageTypes::getAllImageTypes();

		$pInputModelPictureTypes = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->getValue(DataListView::PICTURES);

		if (null == $pictureTypes)
		{
			$pictureTypes = array();
		}

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelExpose()
	{
		$labelExpose = __('PDF-Expose', 'onoffice');

		$pInputModelExpose = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_EXPOSE, $labelExpose);
		$pInputModelExpose->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$exposes = array('' => '') + $this->readExposes();
		$pInputModelExpose->setValuesAvailable($exposes);
		$pInputModelExpose->setValue($this->getValue($pInputModelExpose->getField()));

		return $pInputModelExpose;
	}


	/**
	 *
	 * @return array enum values from DB
	 *
	 */

	static public function getListViewLabels()
	{
		return array(
			DataListView::LISTVIEW_TYPE_DEFAULT => __('Default', 'onoffice'),
			DataListView::LISTVIEW_TYPE_REFERENCE => __('Reference Estates', 'onoffice'),
			DataListView::LISTVIEW_TYPE_FAVORITES => __('Favorites List', 'onoffice'),
		);
	}
}