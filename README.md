# Add User

[![Tests](https://github.com/phpbbmodders/adduser/actions/workflows/tests.yml/badge.svg)](https://github.com/phpbbmodders/adduser/actions/workflows/tests.yml) [![Lint](https://github.com/phpbbmodders/adduser/actions/workflows/lint.yml/badge.svg)](https://github.com/phpbbmodders/adduser/actions/workflows/lint.yml)

Lets administrators create user accounts from the ACP.

## Features

- New **Add User** page under **ACP → Users and Groups**.
- Set username, email, password (left blank, a random one is generated and never emailed), language and birthday.
- Choose the user's group and their default group: *Registered users*, *Newly registered users* or the chosen group. It starts out matching the board's *Set Newly registered users group to default* setting. Optionally add them to *Newly registered users*.
- Follows the board's activation setting: the new user gets an email with a link to set their own password, or with admin activation you can activate the account on the spot. With user activation, the email has the activation link instead, and the user sets their password with *Forgot password* after activating. With board email disabled, the account is active at once, a password is required, and you give it to the user yourself.
- **Add users from CSV** page: upload a CSV file of up to 50 users (username and email, optionally password, language and birthday). Every row is checked with the same rules as the single form and shown with its errors before anything is created; rows with errors are skipped, and a summary lists what was created and skipped. The group and activation options apply to every row. A spreadsheet can be saved as "CSV UTF-8" from Excel or LibreOffice first.
- Every account created is recorded in the admin log.
- Optional copy for the admin (**ACP → Board settings → User registration settings**, off by default): the admin who created accounts gets one email listing them and which welcome email each was sent, without the activation or set-password links.

## Requirements

- phpBB 3.3.19 or later; phpBB 4.0 works too (tested with its development version)
- PHP 8.1 or later

## Installation

1. Copy the extension to `/ext/phpbbmodders/adduser`
2. In the Administration Control Panel, go to **Customise → Manage extensions**
3. Enable the **Add User** extension
4. Add users under **ACP → Users and Groups → Add User**, or many at once under **ACP → Users and Groups → Add users from CSV**

## TODO

Ideas not yet built, practical and speculative alike: [`docs/TODO.md`](docs/TODO.md).

## Contributing

Contributions are welcome!

- **Bug reports**: [Open an issue](https://github.com/phpbbmodders/adduser/issues).
- **Everything else** (questions, feature requests, ideas, general discussion): [Use Discussions](https://github.com/orgs/phpbbmodders/discussions), or the [community forum](https://www.phpbbmodders.com/community/).
- Pull requests are welcome for bug fixes or discussed features.

## Acknowledgments

- Based on the phpBB 3.0 **ACP Add User** MOD by phpbbmodders.net.
- Ported to phpBB 3.1 by Rich McGirr ([RMcGirr83](https://github.com/rmcgirr83)) and tumba25.
- Code review, bug fixes, and documentation assisted by [Claude](https://www.anthropic.com/claude).

## License

This extension is licensed under the **GNU General Public License v2.0**.

See [license.txt](license.txt) for more information.
