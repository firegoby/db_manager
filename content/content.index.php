<?php

require_once(EXTENSIONS . '/db_manager/lib/class.querylog.php');
require_once(EXTENSIONS . '/db_manager/lib/class.dbbackup.php');
require_once(EXTENSIONS . '/db_manager/lib/class.backupmanager.php');

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

        $latest = BackupManager::getLatest();
        if ($latest) {
            $restore_button = new XMLElement('button', __('Restore from Lastest Backup'));
            $restore_button->setAttributeArray(array('name' => 'action[db-manager-restore]', 'class' => 'button delete confirm', 'title' => __('Restore the database from latest backup'), 'accesskey' => 'b', 'data-message' => __('Are you sure you want to RESTORE the database? This will OVERWRITE your current database! All data in the current database will be LOST. Think VERY CAREFULLY before proceeding!')));
            $restore_help = new XMLElement('span', 'This will attempt to restore the database from the latest available backup "<strong>' . BackupManager::getLatest()->file() . '</strong>"', ['class' => 'db_manager__button_help']);
        }
        else {
            $restore_button = new XMLElement('button', __('Restore from Lastest Backup'), ['class' => 'button disabled']);
            $restore_help = new XMLElement('span', 'There are no available database backups to restore from in "<strong>' . DbBackup::directory() . '</strong>"', ['class' => 'db_manager__button_help']);
        }
        $restore_frame = new XMLElement('span', NULL, array('class' => 'frame'));
        $restore_frame->appendChildArray([$restore_button, $restore_help]);
        $form->appendChild($restore_frame);

        $container->appendChild($form);

        $this->Contents->appendChild($container);
    }

    public function __actionIndex()
    {
        if(isset($_POST['action']['db-manager-backup'])){
            $this->backupAction();
            return;
        }

        if(isset($_POST['action']['db-manager-restore'])){
            $this->restoreAction();
            return;
        }
    }

    private function backupAction() {
        $backup = new DbBackup();
        if ($backup->dump()) {
            if ($backup->zip()) {
                $this->notifyBackupSuccess($backup);
                return;
            }
            $this->notifyBackupSuccess($backup);
            $this->notifyZipFailure($backup);
            return;
        }
        $this->notifyBackupFailure($backup);
        return;
    }

    private function restoreAction() {
        $latest = BackupManager::getLatest();
        if ($latest->unzip()) {
            if ($latest->restore()) {
                $this->notifyRestoreSuccess($latest);
                return;
            }
            $this->notifyRestoreFailure($latest);
            return;
        }
        $this->notifyUnzipFailure($latest);
        return;
    }

    private function notifyBackupSuccess($backup) {
        $this->pageAlert(__('The database was successfully backed up to the file "<strong>') . $backup->filename() . '</strong>"', Alert::SUCCESS);
    }

    private function notifyBackupFailure($backup) {
        $this->pageAlert(__('<strong>Database backup FAILED!</strong> An error was encountered while trying to backup the database: ') . $backup->errorMsg(), Alert::ERROR);
    }

    private function notifyZipFailure($backup) {
        $this->pageAlert(__('<strong>Database compression FAILED!</strong> An error was encountered while trying to gzip compress the database backup file: ') . $backup->errorMsg(), Alert::ERROR);
    }

    private function notifyRestoreSuccess($backup) {
        $this->pageAlert(__('The database was successfully restored from the file "<strong>') . $backup->filename() . '</strong>"', Alert::SUCCESS);
    }

    private function notifyRestoreFailure($backup) {
        $this->pageAlert(__('<strong>Database restoration FAILED!</strong> An error was encountered while trying to restore the database: ') . $backup->errorMsg(), Alert::ERROR);
    }

    private function notifyUnzipFailure($backup) {
        $this->pageAlert(__('<strong>Database uncompression FAILED!</strong> An error was encountered while trying to unzip the compressed database backup file: ') . $backup->errorMsg(), Alert::ERROR);
    }

}

