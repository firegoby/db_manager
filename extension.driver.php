<?php

require_once(EXTENSIONS . '/db_manager/lib/class.querylog.php');

class Extension_db_manager extends Extension {

    public function fetchNavigation() {
        return array(
            array(
                'location'	=> __('System'),
                'name'		=> __('Database Manager'),
                'link'		=> '/',
                'limit'		=> 'developer'
            )
        );
    }

    public function getSubscribedDelegates() {
        return array(
            array(
                'page'		=> '/backend/',
                'delegate'	=> 'PostQueryExecution',
                'callback'	=> 'log'
            ),
        );
    }

    public function install() {
        Symphony::Configuration()->set('directory', '/database/', 'db_manager');
        Symphony::Configuration()->set('query_log', 'db_query_log.sql', 'db_manager');
        Symphony::Configuration()->set('mysqldump_bin', '/usr/bin/mysqldump', 'db_manager');
        Symphony::Configuration()->set('gzip_bin', '/bin/gzip', 'db_manager');
        Symphony::Configuration()->set('mysql_bin', '/usr/bin/mysqldump', 'db_manager');
        Symphony::Configuration()->set('gunzip_bin', '/bin/gunzip', 'db_manager');
        Symphony::Configuration()->set('enable_logging', 'yes', 'db_manager');
        Symphony::Configuration()->set('log_authors', 'comment', 'db_manager');
        Symphony::Configuration()->set('log_content', 'comment', 'db_manager');
        Symphony::Configuration()->write();
        return true;
    }

    public function update($previous_version = false) {
        if(version_compare($previousVersion, '0.5.0', '<')) {
            Symphony::Configuration()->set('enable_logging', 'yes', 'db_manager');
            Symphony::Configuration()->set('log_authors', 'comment', 'db_manager');
            Symphony::Configuration()->set('log_content', 'comment', 'db_manager');
            Symphony::Configuration()->write();
        }
        return true;
    }

    public function uninstall() {
        Symphony::Configuration()->remove('db_manager');
        Symphony::Configuration()->write();
        return true;
    }

    public static function log($context) {
        if(Symphony::Configuration()->get('enable_logging', 'db_manager') == 'no') return;
        $log = new QueryLog($context);
    }
}

