# Add User update screenshots — 11/07/2026

Screenshots of the changes in Add User 1.0.7-dev since the first development post: adding users from a CSV file, the default-group choice, and the administrator copy of welcome emails. Taken on phpBB 3.3 with the extension's ACP pages; nothing was created or sent.

## Add users from CSV

The check page after a CSV file was uploaded. Every row is checked with the same rules as the Add User page and shown with its errors before anything is created; rows with errors are skipped.

[<img src="adduser-csv-check.png" width="640" height="450" alt="The Add users from CSV check page: a table of five rows, three ready and two skipped with their errors, and a Create 3 accounts button." />](adduser-csv-check.png)

## Default group

The Add User page with a group chosen. The new Default group choice offers Registered users, Newly registered users, or the group chosen above, and starts out matching the board's own Newly registered users setting.

[<img src="adduser-add-user.png" width="640" height="518" alt="The Add User page in the ACP, with Global moderators chosen as the group and the Default group radio buttons below it." />](adduser-add-user.png)

## Administrator copy of welcome emails

The new setting under ACP » Board settings » User registration settings. When it is on, the administrator who created accounts gets one email listing them, without the activation or set-password links.

[<img src="adduser-acp-admin-copy.png" width="640" height="351" alt="User registration settings with the new Send a copy of Add User welcome emails setting below Set Newly Registered Users group to default." />](adduser-acp-admin-copy.png)
