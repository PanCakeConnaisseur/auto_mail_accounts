<?php
/**
 * @copyright Copyright (c) 2018 Alexey Abel <dev@abelonline.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\AutoMailAccounts\AppInfo;

use \OCP\AppFramework\App;
use \OCA\AutoMailAccounts\Hooks\UserHooks;

class Application extends App {

	public function __construct(array $urlParams = array()) {
		parent::__construct('auto_mail_accounts', $urlParams);

		$container = $this->getContainer();

		$container->registerService('UserHooks', function($c) {
			return new UserHooks(
				$c->query('ServerContainer')->getLogger(),
				$c->query('OCA\AutoMailAccounts\Config'),
				$c->query('ServerContainer')->getUserManager(),
				$c->query('ServerContainer')->getUserSession(),
				$c->query('OCA\Mail\Service\AccountService'),
				$c->query('OCA\Mail\Service\AliasesService')
			);
		});
	}
}