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

namespace onOffice\WPlugin\Record;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerReadForm
	extends RecordManagerRead
{
	/**
	 *
	 * @return object[]
	 *
	 */

	public function getRecords()
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$columns = implode(', ', $this->getColumns());
		$join = implode("\n", $this->getJoins());
		$where = "(".implode(") AND (", $this->getWhere()).")";
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$columns}
				FROM {$prefix}oo_plugin_forms
				{$join}
				WHERE {$where}
				ORDER BY `form_id` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";
		$this->setFoundRows($pWpDb->get_results($sql, OBJECT));
		$this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

		return $this->getFoundRows();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getCountByType()
	{
		$pWpDb = $this->getWpdb();
		$prefix = $this->getTablePrefix();

		$sql = "SELECT `form_type`, COUNT(`form_id`) as count
				FROM {$prefix}oo_plugin_forms
				GROUP BY `form_type`";
		$result = $pWpDb->get_results($sql, ARRAY_A);
		$returnValues = array();

		foreach ($result as $row)
		{
			$returnValues[$row['form_type']] = $row['count'];
		}

		$returnValues['all'] = array_sum($returnValues);

		return $returnValues;
	}
}
