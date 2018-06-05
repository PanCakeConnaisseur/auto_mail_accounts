# Auto Create Mail Accounts
The Nextcloud [mail app](https://apps.nextcloud.com/apps/mail) has a built-in default configuration for mail accounts but it does not create actual mail accounts for each Nextcloud user, so they can not store alternative identitites, a sender name, attachments or collect e-mail addresses. Each user would have to enter all the mail server info (e.g. host, port, username, password) individually.

This app hooks into Nextcloud's user creation/deletion and display name/ password change and automatically creates/deletes/updates a mail account in the mail app. This creates mail accounts like if a user entered the settings manually allowing him to use all features of the mail app. This is useful if the login credentials for Nextcloud and the mail server are identical and the all Nextcloud users also have a mail account.

## Features
Hooks into:
- user creation
- user deletion
- user password change
- user display name change
	- will be set as the sender name of an e-mail

## Configuration
If your Nextcloud login is identical to the e-mail address e.g. *user@example.com* and the mail server is on the same machine and uses default ports you don't need any configuration, because the default values below will be used.

If not you can configure this app by putting the following in Nextcloud's main configuration file in *config/config.php*. These are also the default values:

	'auto_mail_accounts' => array (
                'imap_host' => 'localhost',
                'imap_port' => '143',
                'imap_ssl_mode' => 'none',
                'smtp_host' => 'localhost',
                'smtp_port' => '587',
                'smtp_ssl_mode' => 'none',
                'email_address_suffix' => ''
        ),
        
The key `email_address_suffx` goes into the main hirarchy level, where e.g. `trusted_domains` is.

If your users login as *user* (instead of *user@example.com*) you can set `email_address_suffix` to `@example.com` to automatically add this to the e-mail address of each user.
## Security
Note that the password is stored with symmetric encryption and can be retreived by the admin. This is the default behaviour of the mail app and unavoidable because it needs to login to the mail server.

        
