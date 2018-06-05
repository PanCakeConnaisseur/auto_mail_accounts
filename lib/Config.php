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

namespace OCA\AutoMailAccounts;

use OCP\ILogger;
use \OCP\IConfig;

class Config {

	const DEFAULT_IMAP_HOST = 'localhost';
	const DEFAULT_IMAP_PORT = '143';
	const DEFAULT_IMAP_SSL_MODE = 'none';
	const DEFAULT_SMTP_HOST = 'localhost';
	const DEFAULT_SMTP_PORT = '587';
	const DEFAULT_SMTP_SSL_MODE = 'none';
	const DEFAULT_EMAIL_ADDRESS_SUFFIX = '';

	const CONFIG_KEY = 'auto_mail_accounts';
	const CONFIG_KEY_IMAP_HOST = 'imap_host';
	const CONFIG_KEY_IMAP_PORT = 'imap_port';
	const CONFIG_KEY_IMAP_SSL_MODE = 'imap_ssl_mode';
	const CONFIG_KEY_SMTP_HOST = 'smtp_host';
	const CONFIG_KEY_SMTP_PORT = 'smtp_port';
	const CONFIG_KEY_SMTP_SSL_MODE = 'smtp_ssl_mode';
	const CONFIG_KEY_EMAIL_ADDRESSS_SUFFIX = 'email_address_suffix';
	
	private $logger;
	private $appConfiguration;
	private $logContext = ['app' => 'auto_mail_accounts'];

	public function __construct(ILogger $logger, IConfig $nextCloudConfiguration) {
		$this->logger = $logger;
		$this->appConfiguration = $nextCloudConfiguration->getSystemValue(self::CONFIG_KEY);
	}

	public function getImapHost() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_IMAP_HOST
			,self::DEFAULT_IMAP_HOST);
	}

	public function getImapPort() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_IMAP_PORT
			,self::DEFAULT_IMAP_PORT);
	}


	public function getImapSslMode() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_IMAP_SSL_MODE
			,self::DEFAULT_IMAP_SSL_MODE);
	}

	public function getSmtpHost() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_SMTP_HOST
			,self::DEFAULT_SMTP_HOST);
	}

	public function getSmtpPort() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_SMTP_PORT
			,self::DEFAULT_SMTP_PORT);
	}


	public function getSmtpSslMode() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_SMTP_SSL_MODE
			,self::DEFAULT_SMTP_SSL_MODE);
	}


	public function getEmailAddressSuffix() {
		return $this->getConfigValueOrDefaultValue(self::CONFIG_KEY_EMAIL_ADDRESSS_SUFFIX
			,self::DEFAULT_EMAIL_ADDRESS_SUFFIX);
	}

	/**
	 * Tries to read a config value and if it is set returns its value,
	 * otherwise returns provided value. Also logs a debug message that default
	 * value was used.
	 * @param $configKey string key name of configuration parameter
	 * @param $defaultValue string default parameter that will be returned if
	 * config key is not set
	 * @return string value of config key or provided default value
	 */
	private function getConfigValueOrDefaultValue($configKey, $defaultValue) {
		if (empty($this->appConfiguration) || empty($this->appConfiguration[$configKey])) {
			$this->logger->debug('The config key ' . $configKey
				. ' is not set, defaulting to ' . $defaultValue . '.'
				, $this->logContext);
			return $defaultValue;
		} else {
			return $this->appConfiguration[$configKey];
		}
	}
}