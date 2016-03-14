<?php

require_once(CORE . '/class.symphony.php');

class BackupManager {

    // Return a DbBackup object of the lastest backup on disk
    public static function getLatest() {
        $latest = array_pop(self::getBackupList());
        if ($latest) {
            return new DbBackup(basename($latest));
        }
        return null;
    }

    // Returns an array of backup filenames, sorted in alphabetical (and so temporal) order
    public static function getBackupList() {
        $db_name = Symphony::Configuration()->get('db', 'database');
        $backup_dir = Symphony::Configuration()->get('directory', 'db_manager');

        $backups = [];
        foreach (glob(DOCROOT . $backup_dir . $db_name . "-*") as $backup) {
            $backups[] = $backup;
        }
        return $backups; // already sorted alphabetically by glob()
    }

}

