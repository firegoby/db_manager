<?php

require_once(CORE . '/class.symphony.php');

class QueryLog {

    public static function directory() {
        return Symphony::Configuration()->get('directory', 'db_manager');
    }

    public static function filename() {
        return Symphony::Configuration()->get('filename', 'db_manager');
    }

    public static function filePath() {
        return DOCROOT . self::directory();
    }

    public static function file() {
        return self::filePath() . self::filename();
    }
}
