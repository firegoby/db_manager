<?php

require_once(CORE . '/class.symphony.php');

class QueryLog {

    private $output = "";
    private static $header_written = FALSE;

    public function __construct($context) {
        $tbl_prefix = Symphony::Configuration()->get('tbl_prefix', 'database');

        // ensure one line per statement, no line breaks or extraneous whitespace
        $query = trim(preg_replace('/\s+/', ' ', $context['query'])); 

        // append query delimeter if it doesn't exist
        if (!preg_match('/;$/', $query)) $query .= ";";

        // one query per line
        $query .= "\n";

        /* FILTERS */

        // only structural changes, no SELECT statements
        if (!preg_match('/^(insert|update|delete|create|drop|alter|rename)/i', $query)) return;

        // un-logged tables (sessions, cache, tracker extension)
        if (preg_match("/{$tbl_prefix}(cache|forgotpass|sessions|tracker_activity)/i", $query)) return;

        // log, or not, authors
        if (preg_match("/{$tbl_prefix}(authors)/i", $query)) {
            if (Symphony::Configuration()->get('log_authors', 'db_manager') == 'no') return;
            // always ignore 'last seen' loging since it's queried on every admin page load
            if (preg_match("/^UPDATE {$tbl_prefix}authors SET \`last_seen\`/", trim($query))) return; 
            // include as comments instead of ignoring completely if log_authors=comment enabled
            if (Symphony::Configuration()->get('log_authors', 'db_manager') == 'comment') {
                $query = '-- ' . $query; // commentify
            }
        }

        // content updates in tbl_entries (includes tbl_entries_fields_*)
        if (preg_match('/^(insert|delete|update)/i', $query) && preg_match("/({$tbl_prefix}entries)/i", $query)) {
            if (Symphony::Configuration()->get('log_content', 'db_manager') == 'no') return;
            // include as comments instead of ignoring completely if log_content enabled
            if (Symphony::Configuration()->get('log_content', 'db_manager') == 'comment') {
                $query = '-- ' . $query; // commentify
            }
        }

        if(!self::$header_written) $this->writeHeader();
        $this->output .= $query;
        $this->writeLog();
    }

    public static function filename() {
        return Symphony::Configuration()->get('query_log', 'db_manager');
    }

    public static function directory() {
        return DOCROOT . Symphony::Configuration()->get('directory', 'db_manager');
    }

    public static function file() {
        return self::directory() . self::filename();
    }

    public function writeHeader() {
        $this->output .= "\n" . '-- ' . date('Y-m-d H:i:s', time());

        $author = Symphony::Engine()->Author();
        if (isset($author)) $this->output .= ', ' . $author->getFullName();

        $url = Administration::instance()->getCurrentPageURL();
        if (!is_null($url)) $this->output .= ', ' . $url;

        $this->output .= "\n";
        self::$header_written = TRUE;
    }

    public function writeLog() {
        $handle = @fopen(self::file(), 'a');
        if ($handle) {
            fwrite($handle, $this->output);
            fclose($handle);
        }
    }
}
