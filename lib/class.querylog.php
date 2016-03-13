<?php

require_once(CORE . '/class.symphony.php');

class QueryLog {

    public static function filename() {
        return Symphony::Configuration()->get('query_log', 'db_manager');
    }

    public static function directory() {
        return DOCROOT . Symphony::Configuration()->get('directory', 'db_manager');
    }

    public static function file() {
        return self::directory() . self::filename();
    }
}
