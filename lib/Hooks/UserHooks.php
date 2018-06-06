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


namespace OCA\AutoMailAccounts\Hooks;

use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\AliasesService;
use OCP\ILogger;
use OCP\IUserManager;
use OCA\Mail\Db\MailAccount;
use OCP\IUserSession;
use OCA\AutoMailAccounts\Config;

class UserHooks {

	/** @var ILogger */
	private $logger;

	/** @var Config * */
	private $config;

	/** @var IUserManager */
	private $userManager;

	/** @var IUserSession */
	private $userSession;

	/** @var AccountService */
	private $accountService;

	/** @var AliasesService */
	private $aliasesService;

	private $logContext = ['app' => 'auto_mail_accounts'];

	public function __construct(ILogger $logger, Config $config, IUserManager $userManager, IUserSession $userSession, AccountService $accountService, AliasesService $aliasesService) {
		$this->logger = $logger;
		$this->config = $config;
		$this->userManager = $userManager;
		$this->userSession = $userSession;
		$this->accountService = $accountService;
		$this->aliasesService = $aliasesService;
	}

	public function register() {
		$this->userManager->listen('\OC\User', 'postCreateUser', $this->createUserCallbackFunction());
		$this->userManager->listen('\OC\User', 'preDelete', $this->deleteUserCallbackFunction());
		$this->userManager->listen('\OC\User', 'changeUser', $this->changeUserCallbackFunction());
		$this->userManager->listen('\OC\User', 'postSetPassword', $this->changePasswordCallbackFunction());
	}

	private function createUserCallbackFunction() {
		return function (\OC\User\User $user, string $password) {

			$uid = $user->getUID();

			$account = new MailAccount();
			$account->setUserId($uid);
			$account->setName($user->getDisplayName());

			$email = $uid;
			if (!empty($this->config->getEmailAddressSuffix())) {
				$email = $email . $this->config->getEmailAddressSuffix();
			}
			$account->setEmail($email);

			$account->setInboundHost($this->config->getImapHost());
			$account->setInboundPort($this->config->getImapPort());
			$account->setInboundSslMode($this->config->getImapSslMode());
			$account->setInboundUser($uid);
			$account->setInboundPassword($this->encrypt($password));

			$account->setOutboundHost($this->config->getSmtpHost());
			$account->setOutboundPort($this->config->getSmtpPort());
			$account->setOutboundSslMode($this->config->getSmtpSslMode());
			$account->setOutboundUser($uid);
			$account->setOutboundPassword($this->encrypt($password));

			$this->accountService->save($account);
			$this->logger->debug("Automatically created mail account for uid " . $uid
				. " with e-mail address \"" . $email
				. "\", imap host \"" . $this->config->getImapHost()
				. "\", imap port \"" . $this->config->getImapPort()
				. "\", imap ssl mode \"" . $this->config->getImapSslMode()
				. "\", smtp host mode \"" . $this->config->getSmtpHost()
				. "\", smtp port \"" . $this->config->getSmtpPort()
				. "\", smtp ssl mode \"" . $this->config->getSmtpSslMode()
				. "\". smtp/imap user is identical to uid."
				. " e-mail address suffix was \"". $this->config->getEmailAddressSuffix(). "\"."
				, $this->logContext);
		};
	}

	private function deleteUserCallbackFunction() {
		return function (\OC\User\User $user) {
			$userUid = $user->getUID();
			$userAccounts = $this->accountService->findByUserId($user->getUID());
			// A user can have multiple configured mail accounts, so we have to
			// delete all of them. This is why findByUserId() returns an array
			// and not a single element.
			foreach ($userAccounts as $userAccount) {
				// each account can have multiple aliases
				$thisAccountsAliases = $this->aliasesService->findAll($userAccount->getId()
					, $userUid);
				foreach ($thisAccountsAliases as $alias) {
					$this->aliasesService->delete($alias->getId(), $userUid);
					$this->logger->debug("Automatically deleted alias \""
						. $alias->getName() . "\" of mail account \"" . $userAccount->getName()
						. "\".", $this->logContext);
				}
				// After deleting all aliases delete the actual mail account.
				$this->accountService->delete($userUid, $userAccount->getId());
				$this->logger->debug("Automatically deleted mail account "
					. $userAccount->getName(). "<".$userAccount->getEMailAddress()
					."> before deleting user " . $userUid.".", $this->logContext);
			}
			//TODO: delete attachments, mail app does not support deletion yet
			//TODO: delete collected e-mails, mail app does not support deletion yet
		};
	}

	private function changeUserCallbackFunction() {
		return function (\OC\User\User $user, string $feature, string $value) {
			// The assumption is made that the automatically created mail
			// account is the first one, because it was automatically created
			// immediately after the creation of the user.
			$firstAccountsMailAccount = $this->getUsersFirstMailAccount($user);

			if ($feature === 'displayName') {
				$firstAccountsMailAccount->setName($value);
				$this->accountService->save($firstAccountsMailAccount);
				$this->logger->debug("Automatically changed sender name for mail account"
					. " of uid " . $user->getUID() . " to \"" . $value . "\".", $this->logContext);
			}
		};
	}

	private function changePasswordCallbackFunction() {
		return function (\OC\User\User $user, string $password) {
			// The assumption is made that the automatically created mail
			// account is the first one, because it was automatically created
			// immediately after the creation of the user.
			$firstAccountsMailAccount = $this->getUsersFirstMailAccount($user);
			$firstAccountsMailAccount->setInboundPassword($this->encrypt($password));
			$firstAccountsMailAccount->setOutboundPassword($this->encrypt($password));
			$this->accountService->save($firstAccountsMailAccount);
			$this->logger->debug("Automatically changed password for mail account of uid "
				. $user->getUID() . ".", $this->logContext);
		};
	}

	private function encrypt($plaintext) {
		$crypto = \OC::$server->getCrypto();
		return $crypto->encrypt($plaintext);
	}

	private function getUsersFirstMailAccount(\OC\User\User $user) {
		$accounts = $this->accountService->findByUserId($user->getUID());
		uksort($accounts, function ($a, $b) {
			return ($a['account']['id'] < $b['account']['id']) ? -1 : 1;
		});

		return $accounts[0]->getMailAccount();
	}
}