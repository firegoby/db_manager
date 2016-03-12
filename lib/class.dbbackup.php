<?php

require_once(CORE . '/class.symphony.php');

class DbBackup {

    private $filename;

    public function __construct($filename = null) {
        $db_name = Symphony::Configuration()->get('db', 'database');
        if ($filename) {
            $this->filename = $filename;
        }
        else {
            $this->filename = $db_name . '-' . date('Y-m-d-H-i-s') . '.sql';
        }
    }

    public function __toString() {
        return self::filePath() . $this->filename;
    }

    public static function filePath() {
        return DOCROOT . Symphony::Configuration()->get('directory', 'db_manager');
    }
}

