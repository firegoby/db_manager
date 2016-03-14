# Database Manager

## Database Management for Symphony CMS

 - **Version**: 0.3.0
 - **Status**: Experimental
 - **Latest Release**: 14th March 2016
 - **Author**: Chris Batchelor, [Firegoby Design](http://firegoby.com/) 

## Features

 - One-click timestamped database backup (via a gzip'd MySQL dump)
 - One-click database restore (from a gzip'd MySQL dump)
 - TODO (0.4.0): Database backup archive (restore from multiple time points)
 - TODO (0.5.0): Database Changes Tracker (synchronise database instances)
 - TODO (0.6.0): Preferences Panel for Settings
 - TODO (0.7.0): Browsable QueryLog (monitor Database Changes Tracker)

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

