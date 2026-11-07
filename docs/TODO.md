# TODO

Ideas not yet built, practical and speculative alike.

## Reply to the bulk-user support topic

Once bulk-user creation is implemented, verified, and available in a
release, prepare a reply to this [ACP Add User support topic](https://www.phpbb.com/customise/db/extension/acp_add_user_2/support/topic/168031).
Review the discussion before drafting the reply, explain the available CSV
workflow and its limits, and include the release version and download link.
Obtain approval of the complete draft before posting, then check the live
reply. This remains pending until the functionality is available to users.

## Optional administrator copy of welcome emails

Add an optional ACP setting, disabled by default, to send an administrator
an email copy when ACP Add User sends a welcome email to a newly created
user. This provides confirmation of the welcome email sent by the extension.
The request comes from this [ACP Add User support topic](https://www.phpbb.com/customise/db/extension/acp_add_user_2/support/topic/239096).

ACP Add User no longer emails passwords; preserve that behavior. Check the
target phpBB branch's email mechanisms before implementation, and ensure
activation or password-setting links are handled appropriately in the
administrator copy.

Decided 11/07/2026: the copy goes to the admin who created the account, at
their own profile email address, and it is a separate short email (which
user, which address, which kind of welcome email) without the activation or
set-password link, since those links take over the account. It also covers
accounts created from a CSV file.

## Newly Registered Users as the default group

Accepted by William on 10/29/2026, following this
[default-group request](https://www.phpbb.com/customise/db/extension/acp_add_user_2/support/topic/236106).

Allow ACP-created accounts to use Newly Registered Users as their default
group. Existing new-user membership controls do not provide this exact
behavior: the group selector currently excludes Newly Registered Users.

Decided 11/07/2026: replace the "make the selected group default" checkbox
with one default-group choice: Registered users, Newly registered users, or
the group chosen above. The Newly registered users option is only shown
while the new-member post limit is on, and choosing it marks the account as
a new user. It starts out matching phpBB's own *Set Newly registered users
group to default* setting (which `user_add()` already applies to new users).
Preserve Registered Users membership and phpBB's new-user/post-limit rules,
including boards where that restriction is disabled, and use the same choice
on the **Add users from CSV** page.
