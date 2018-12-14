# Auto Mail Accounts
The Nextcloud [mail app](https://apps.nextcloud.com/apps/mail) has a built-in default configuration for mail accounts but it does not create actual mail accounts for each Nextcloud user, so they can not store alternative identitites, a sender name, attachments or collect e-mail addresses. Each user would have to enter all the mail server info (e.g. host, port, username, password) individually.

This app hooks into Nextcloud's user creation/deletion and display name/ password change and automatically creates/deletes/updates a mail account in the mail app. This creates mail accounts like if a user entered the settings manually allowing him to use all features of the mail app. This is useful if the login credentials for Nextcloud and the mail server are identical and all Nextcloud users also have a mail account.

## Features
Hooks into:
- user creation
- user deletion
- user password change
- user display name change
	- will be set as the sender name of an e-mail

## Configuration
If your Nextcloud login is identical to the e-mail address e.g. *user@example.com* and the mail server is on the same machine and uses default ports you don't need any configuration, because the default values below will be used.

If not, you can configure this app by putting the following in Nextcloud's main configuration file in *config/config.php*. These are also the default values:

	'auto_mail_accounts' => array (
                'imap_host' => 'localhost',
                'imap_port' => '143',
                'imap_ssl_mode' => 'none',
                'smtp_host' => 'localhost',
                'smtp_port' => '587',
                'smtp_ssl_mode' => 'none',
                'email_address_suffix' => ''
        ),
        
The key `auto_mail_accounts` goes into the main hierarchy level, where e.g. `trusted_domains` is.

If your users login as *user* (instead of *user@example.com*) you can set `email_address_suffix` to `@example.com` to automatically add this to the e-mail address of each user.

Valid ssl modes are `none`, `ssl` and `tls` as described in the [mail app documentation](https://github.com/nextcloud/mail/blob/master/doc/admin.md#minimal-configuration).

Note that the email field during user creation in the Nextcloud user interface is ignored and set to the username (uid).
## Security
Note that the password is stored with symmetric encryption and can be retrieved by the admin. This is the default behaviour of the mail app whose methods this app uses.

## Troubleshooting
Set `'loglevel' => '0',` in Nextcloud's *config/config.php* to enable the app's debug output and check the log file.

## Cleanup
Afaics the mail app has no mechanism to delete

- all collected e-mail addresses (i.e. addresses that are stored when you send someone an e-mail) of a user
- all references to attachments of a user
	
This means that if you delete a user, while his mail account and aliases will be deleted, his collected addresses and attachment references will remain in the db. This is not the *Auto Mail Account* app's fault.

This data can be found in the `oc_mail_attachments` and `oc_mail_collected_addresses` tables respectively.

        
## Changelog
### 0.1.2
- Add compatibility with Nextcloud 15
### 0.1.1
- Add compatibility with Nextcloud 14
### 0.1.0
- Initial release