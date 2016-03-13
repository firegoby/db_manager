<?php

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
        return array();
    }

    public function install() {
        Symphony::Configuration()->set('directory', '/database/', 'db_manager');
        Symphony::Configuration()->set('query_log', 'db_query_log.sql', 'db_manager');
        Symphony::Configuration()->set('mysqldump_bin', '/usr/bin/mysqldump', 'db_manager');
        Symphony::Configuration()->set('gzip_bin', '/bin/gzip', 'db_manager');
        Symphony::Configuration()->write();
        return true;
    }

    public function update($previous_version = false) {
        if(version_compare($previousVersion, '0.2.0', '<')) {
        }
        return true;
    }

    public function uninstall() {
        Symphony::Configuration()->remove('db_manager');
        Symphony::Configuration()->write();
        return true;
    }

}

