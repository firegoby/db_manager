# Database Manager

## Database Management for Symphony CMS

 - **Version**: 0.8.0
 - **Status**: Experimental
 - **Latest Release**: 1st May 2016
 - **Author**: Chris Batchelor, [Firegoby Design](http://firegoby.com/) 

## Features

 - One-click timestamped database backup (via a gzip'd MySQL dump)
 - One-click database restore (from a gzip'd MySQL dump)
 - Database Backup Archive (restore from multiple time points)
 - Download database backups (as a .sql.gz file)
 - Auto-tag backups with human friendly codename (e.g. brainy-bee, prickly-bison, etc)
 - Database Changes Logger (synchronise database instances)
 - UI for setting Database Logger Options
 - TODO (0.9.0): Browsable QueryLog (monitor Database Changes Logger)
 - TODO (0.10.0): Preferences Panel for Settings Page

![Database Manager UI](/screenshots/ui.png)

## Installation

1. Add `db_manager` folder to your Symphony `extensions` folder
2. Navigate to URL `http://YourSymphonyProject.com/symphony/system/extensions/`
3. Select `Database Manager` from the list and run `Install` from the `With Selected...` menu
4. Ensure the correct locations for the `mysqldump`, `mysql`, `gzip`, and `gunzip` binaries on your system under `db_manager` in `config.php`. (On many linux system this will be the defaults of `/usr/bin/mysqldump`, `/usr/bin/mysql`, `/bin/gzip`, and `/bin/gunzip`. On development machines they might be elsewhere like `/usr/local/bin/mysqldump` depending on how MySQL or MariaDB was installed. Find the correct path by running `which mysqldump`, `which mysql`, `which gzip`)

## Usage

1. Go to `System > Database Manager`
2. To create a new timestamped backup of the current database click the green `Backup the Database` button
3. To overwrite the current database with the latest saved backup click the 'Restore from Latest Backup' button
4. To restore from a specific backup, select/highlight it in the `Database Backups` list and then choose `Restore from this backup` from the `With Selected...` menu
5. To download a .sql.gz file of a specific backup, select/highlight it in the `Database Backups` list and then choose `Download as a file` from the `With Selected...` menu
6. To delete a specific backup, select/highlight it in the `Database Backups` list and then choose `Delete` from the `With Selected...` menu. Multiple backups can be deleted at the same time by Ctrl- or Cmd- clicking to select them.

## Database Changes Logger

> To enable this feature tick the `Enable Database Logging` checkbox in the `Database Logging Options` section of `System > Database Manager` page.

This feature will monitor all Symphony's database queries and store any that make structural changes to a SQL log file that can be 'replayed' on other copies of the same database (e.g. testing, production) in order to synchronise them.

The logging can also log changes to Authors and Content as well, alternatively such changes can be added only as comments to the log so you can easily review what non-SELECT SQL queries are being made against your database.

This feature is heavily based on the great work done by Nick Dunn in his Database Synchronizer extension. Fuller documentation of this feature coming soon.
