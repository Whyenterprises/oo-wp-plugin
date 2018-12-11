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

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

interface FormPostConfiguration
{
	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper;


	/**
	 *
	 * @return array all the post variables
	 *
	 */

	public function getPostVars(): array;


	/**
	 *
	 * @param string $input
	 * @param string $module
	 * @return string
	 *
	 */

	public function getTypeForInput(string $input, string $module): string;


	/**
	 *
	 * @return string
	 *
	 */

	public function getPostvarCaptchaToken(): string;


	/**
	 *
	 * @return string
	 *
	 */

	public function getCaptchaSecret(): string;


	/**
	 *
	 * @return bool
	 *
	 */

	public function isCaptchaSetup(): bool;


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchCriteriaFields(): array;


	/**
	 *
	 * @param string $logString
	 *
	 */

	public function log(string $logString);
}