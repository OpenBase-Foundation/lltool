Create an empty SQLite database file for local testing:

powershell> New-Item -ItemType File -Path database\database.sqlite

Then import the migration SQL with the `sqlite3` CLI or a DB tool.
