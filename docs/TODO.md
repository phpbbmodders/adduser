# TODO

Ideas not yet built, practical and speculative alike.

## Bulk add from a CSV file

Fold the job of phpbbmodders/bulkuseradd (david63's Bulk User Add) into this
extension as a second mode of the **Add User** page, so boards need one
extension instead of two. bulkuseradd can be archived once this ships.

Planned behavior:

- Upload a CSV file with one user per row: username and email required;
  language, birthday and password optional, the same fields as the single
  form.
- Every row is created exactly the way the single form creates a user:
  - checked with phpBB's own username, email and password rules;
  - a blank password gets a random one that is never emailed;
  - the board's activation setting decides the email the user gets: a link
    to set their own password, or with user activation the activation link
    (they then use *Forgot password*). With admin activation the admin can
    activate the accounts on the spot;
  - the chosen group, default-group and *Newly registered users* options
    apply to every row;
  - each account is recorded in the admin log.
- A preview step lists every row with its errors before anything is
  created, so one bad row doesn't leave a half-imported list. The admin then
  confirms, and rows with errors are skipped.
- A summary afterwards: created, skipped and why.

CSV only, no Excel: bulkuseradd reads Excel through PhpSpreadsheet, which
ships a large `vendor/` folder. CSV needs only PHP itself, and admins can
save a spreadsheet as CSV.

Still needs deciding:

- A maximum number of rows per upload, or processing in batches, so large
  files don't hit PHP's time limit or flood the mail queue.
- Column order fixed, or read from a header row.
- File encoding and separator: UTF-8 with commas only, or also semicolons
  (common in European Excel exports).

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
target phpBB branch's email mechanisms before implementation, decide which
administrator address receives the copy, and ensure activation or
password-setting links are handled appropriately in the administrator copy.
