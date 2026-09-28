# Add User

[![Tests](https://github.com/phpbbmodders/adduser/actions/workflows/tests.yml/badge.svg)](https://github.com/phpbbmodders/adduser/actions/workflows/tests.yml) [![Lint](https://github.com/phpbbmodders/adduser/actions/workflows/lint.yml/badge.svg)](https://github.com/phpbbmodders/adduser/actions/workflows/lint.yml)

Lets administrators create user accounts from the ACP.

## Features

- New **Add User** page under **ACP → Users and Groups**.
- Set username, email, password (left blank, one is generated), language and birthday.
- Choose the user's group and optionally make it their default group; optionally add them to *Newly registered users*.
- Follows the board's activation setting: the new user gets their login details by email, or with admin activation you can activate the account on the spot.
- Every account created is recorded in the admin log.

## Requirements

- phpBB 3.3.19 or later
- PHP 8.0 or later

## Installation

1. Copy the extension to `/ext/phpbbmodders/adduser`
2. In the Administration Control Panel, go to **Customise → Manage extensions**
3. Enable the **Add User** extension
4. Add users under **ACP → Users and Groups → Add User**

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
