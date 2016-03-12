<?php

require_once(EXTENSIONS . '/db_manager/lib/class.querylog.php');
require_once(EXTENSIONS . '/db_manager/lib/class.dbbackup.php');

class contentExtensionDb_managerIndex extends contentBlueprintsPages {

    public function view() {
        $this->setPageType('index');

        $this->addScriptToHead(URL . '/extensions/db_manager/assets/db_manager.js');
        $this->addStylesheetToHead(URL . '/extensions/db_manager/assets/db_manager.css');

        $this->setTitle(
            __('%1$s &ndash; %2$s',
            array(
                __('Symphony'),
                __('Database Manager')
            ))
        );

        $this->appendSubheading(
            __('Database Manager')
        );

        $container = new XMLElement('div', NULL, array('id' => 'db_manager_container'));

        $dbb = new DbBackup();
        $container->appendChild(new XMLELement('p', QueryLog::filepath()));
        $container->appendChild(new XMLELement('p', $dbb->__toString()));

        $this->Contents->appendChild($container);
    }
}

