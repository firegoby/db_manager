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

        $form = Widget::Form(Symphony::Engine()->getCurrentPageURL(), 'post');
        if (Symphony::Engine()->isXSRFEnabled()) {
            $form->prependChild(XSRF::formToken());
        }

        $backup_frame = new XMLElement('span', NULL, array('class' => 'frame'));
        $backup_button = new XMLElement('button', __('Backup the Database'));
        $backup_button->setAttributeArray(array('name' => 'action[db-manager-backup]', 'class' => 'button create confirm', 'title' => __('Create a backup of the current database'), 'accesskey' => 'b', 'data-message' => __('Are you sure you want to BACKUP the current database?')));
        $backup_help = new XMLElement('span', 'This will attempt to create a time-stamped gzip&rsquo;d SQL backup of the entire database and save it in the directory "<strong>' . DbBackup::directory() . '</strong>"', ['class' => 'db_manager__button_help']);
        $backup_frame->appendChildArray([$backup_button, $backup_help]);
        $form->appendChild($backup_frame);

        $container->appendChild($form);

        $this->Contents->appendChild($container);
    }

    public function __actionIndex()
    {
        if(isset($_POST['action']['db-manager-backup'])){
            $backup = new DbBackup();
            if ($backup->dump()) {
                if ($backup->compress()) {
                    $this->notifyBackupSuccess($backup);
                    return;
                }
                $this->notifyBackupSuccess($backup);
                $this->notifyCompressionFailure($backup);
                return;
            }
            $this->notifyBackupFailure($backup);
            return;
        }
    }

    public function notifyBackupSuccess($backup) {
        $this->pageAlert(__('The database was successfully backed up to the file "<strong>') . $backup->filename() . '</strong>"', Alert::SUCCESS);
    }

    public function notifyBackupFailure($backup) {
        $this->pageAlert(__('<strong>Database backup FAILED!</strong> An error was encountered while trying to backup the database: ') . $backup->errorMsg(), Alert::ERROR);
    }

    public function notifyCompressionFailure($backup) {
        $this->pageAlert(__('<strong>Database compression FAILED!</strong> An error was encountered while trying to gzip compress the database backup file: ') . $backup->errorMsg(), Alert::ERROR);
    }
}

