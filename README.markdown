# Database Manager

## Database Management for Symphony CMS

 - **Version**: 0.6.0
 - **Status**: Experimental
 - **Latest Release**: 26th April 2016
 - **Author**: Chris Batchelor, [Firegoby Design](http://firegoby.com/) 

## Features

 - One-click timestamped database backup (via a gzip'd MySQL dump)
 - One-click database restore (from a gzip'd MySQL dump)
 - Database backup archive (restore from multiple time points)
 - Database Changes Tracker (synchronise database instances)
 - Auto-tag backups with human friendly codename (e.g. brainy-bee, prickly-bison, etc)
 - TODO (0.7.0): Browsable QueryLog (monitor Database Changes Tracker)
 - TODO (0.8.0): Preferences Panel for Settings

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
5. To delete a specific backup, select/highlight it in the `Database Backups` list and then choose `Delete` from the `With Selected...` menu. Multiple backups can be deleted at the same time by Ctrl- or Cmd- clicking to select them.

## Database Changes Tracker

> To enable this feature set `enable_logging` to `yes` in `config.php`

This feature will monitor all Symphony's database queries and store any that make structural changes to a SQL log file that can be 'replayed' on other copies of the same database (e.g. testing, production) in order to synchronise them.

The logging can also track & log changes to Authors and Content as well, alternatively such changes can be added only as comments to the log so you can easily review what non-SELECT SQL queries are being made against your database.

This feature is heavily based on the great work done by Nick Dunn in his Database Synchronizer extension. Fuller documentation of this feature coming soon.
