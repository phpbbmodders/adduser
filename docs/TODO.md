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
