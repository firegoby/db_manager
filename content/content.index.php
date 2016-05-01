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

        if (Symphony::Engine()->isXSRFEnabled()) {
            $this->Form->prependChild(XSRF::formToken());
        }

        $container = new XMLElement('div', NULL, array('id' => 'db_manager_container'));

        $container->appendChild($this->generateBackupFrame());
        $container->appendChild($this->generateRestoreFrame());
        $container->appendChild($this->generateArchiveTable());
        $container->appendChild($this->generateTrackerOptions());

        $this->Form->appendChild($container);
    }

    public function generateBackupFrame() {
        $backup_frame = new XMLElement('span', NULL, array('class' => 'frame db_manager__frame'));
        $backup_button = new XMLElement('button', __('Backup the Database'));
        $backup_button->setAttributeArray(array('name' => 'action[db-manager-backup]', 'class' => 'button create confirm', 'title' => __('Create a backup of the current database'), 'accesskey' => 'b', 'data-message' => __('Are you sure you want to BACKUP the current database?')));
        $backup_help = new XMLElement('span', 'This will attempt to create a gzip&rsquo;d SQL backup of the entire database and save it in "<strong>' . DbBackup::directory() . '</strong>"', ['class' => 'db_manager__button_help']);
        $backup_frame->appendChildArray([$backup_button, $backup_help]);
        return $backup_frame;
    }

    public function generateRestoreFrame() {
        $latest = BackupManager::getLatest();
        if ($latest) {
            $restore_button = new XMLElement('button', __('Restore from Lastest Backup'));
            $restore_button->setAttributeArray(array('name' => 'action[db-manager-restore]', 'class' => 'button delete confirm', 'title' => __('Restore the database from latest backup'), 'accesskey' => 'b', 'data-message' => __('Are you sure you want to RESTORE the database? This will OVERWRITE your current database! All data in the current database will be LOST. Think VERY CAREFULLY before proceeding!')));
            $restore_help = new XMLElement('span', 'This will attempt to restore the database from the latest available backup "<strong>' . BackupManager::getLatest()->getCodeName() . '</strong>"', ['class' => 'db_manager__button_help']);
        }
        else {
            $restore_button = new XMLElement('button', __('Restore from Lastest Backup'), ['class' => 'button disabled']);
            $restore_help = new XMLElement('span', 'There are no available database backups to restore from in "<strong>' . DbBackup::directory() . '</strong>"', ['class' => 'db_manager__button_help']);
        }
        $restore_frame = new XMLElement('span', NULL, array('class' => 'frame db_manager__frame'));
        $restore_frame->appendChildArray([$restore_button, $restore_help]);
        return $restore_frame;
    }

    public function generateArchiveTable() {
        $backups = array_reverse(BackupManager::getBackupList());

        $thead = array(
            [__('Date'), 'col'],
            [__('Time'), 'col'],
            [__('Codename'), 'col'],
            [__('Archive'), 'col'],
        );
        $tbody = array();

        if (empty($backups)) {
            $tbody = array(Widget::TableRow([
                    Widget::TableData(__('No previous backups available. Create one now using the "Backup the Database" button above.'), 'inactive', null, count($thead))
                ])
            );
        }
        else {
            foreach ($backups as $id => $backup) {
                $current = new DbBackup($backup);

                $col_name = Widget::TableData($current->filename());
                $col_name->appendChild(Widget::Input("selected[]", "{$current->filename()}", 'checkbox'));
                $col_codename = Widget::TableData($current->getCodeName());
                $col_date = Widget::TableData(date_format($current->getDate(), 'D j M Y'));
                $col_time = Widget::TableData(date_format($current->getDate(), 'H:i:s'));

                $tbody[] = Widget::TableRow([$col_date, $col_time, $col_codename, $col_name], null);
            }
        }

        $table = Widget::Table(
            Widget::TableHead($thead), null,
            Widget::TableBody($tbody), 'selectable', null,
            array('role' => 'directory', 'aria-labelledby' => 'symphony-subheading', 'data-interactive' => 'data-interactive')
        );

        $options = array(
            array(null, false, __('With Selected...')),
            array('restore', false, __('Restore from this backup')),
            array('delete', false, __('Delete'), 'confirm', null, array(
                'data-message' => __('Are you sure you want to DELETE the selected backups?')
            )),
            array('download', false, __('Download as a file'))
        );

        $table_actions = new XMLElement('div');
        $table_actions->setAttribute('class', 'actions');
        $table_actions->appendChild(Widget::Apply($options));

        $heading = new XMLElement('h3', 'Database Backups', ['class' => 'db_manager__subheading']);

        $container = new XMLElement('div', null, ['class' => 'db_manager__archive']);
        $container->appendChildArray([$heading, $table, $table_actions]);

        return $container;
    }

    public function generateTrackerOptions() {
        $log_enabled = Symphony::Configuration()->get('enable_logging', 'db_manager');
        $log_authors = Symphony::Configuration()->get('log_authors', 'db_manager');
        $log_content = Symphony::Configuration()->get('log_content', 'db_manager');

        $heading = new XMLElement('h3', 'Database Logging Options', ['class' => 'db_manager__subheading']);

        $options_bar = new XMLElement('div', null, ['class' => 'db_manager__options-bar']);

        $options_bar->appendChild(Widget::Checkbox(
                'options[log_enabled]',
                $log_enabled,
                __('Enable Database Logging')
            )
        );

        if ($log_enabled == 'yes') {
            $options_bar->appendChild(Widget::Label(__('Log Author Changes'),
                Widget::Select(
                    'options[log_authors]',
                    array(
                        ['no', ($log_authors == 'no' ? TRUE : FALSE), 'No'],
                        ['comment', ($log_authors == 'comment' ? TRUE : FALSE), 'As Comments Only'],
                        ['yes', ($log_authors == 'yes' ? TRUE : FALSE), 'Yes'],
                    )
                )
            ));

            $options_bar->appendChild(Widget::Label(__('Log Content Changes'),
                Widget::Select(
                    'options[log_content]',
                    array(
                        ['no', ($log_content == 'no' ? TRUE : FALSE), 'No'],
                        ['comment', ($log_content == 'comment' ? TRUE : FALSE), 'As Comments Only'],
                        ['yes', ($log_content == 'yes' ? TRUE : FALSE), 'Yes'],
                    )
                )
            ));
        } else {
            $options_bar->appendChild(Widget::Input('options[log_authors]', $log_authors, 'hidden'));
            $options_bar->appendChild(Widget::Input('options[log_content]', $log_content, 'hidden'));
        }

        $save_button = new XMLElement('button', __('Save Logging Options'));
        $save_button->setAttributeArray(array('name' => 'action[db-manager-save-options]', 'class' => 'button confirm', 'title' => __('Create a backup of the current database'), 'accesskey' => 's', 'data-message' => __('Are you sure you want to save the new logging options?')));
        $options_bar->appendChild($save_button);

        $options_frame = new XMLElement('div', null, ['class' => 'frame db_manager__frame']);
        $options_frame->appendChildArray([$options_bar]);

        $container = new XMLElement('div', null, ['class' => 'db_manager__options']);
        $container->appendChildArray([$heading, $options_frame]);

        return $container;
    }

    public function __actionIndex()
    {
        if(isset($_POST['action']['db-manager-save-options'])) {
            $this->saveOptions();
            return;
        }

        if (isset($_POST['action']['db-manager-backup'])) {
            $this->backupAction();
            return;
        }

        if (isset($_POST['action']['db-manager-restore'])){
            $latest = BackupManager::getLatest();
            $this->restoreAction($latest);
            return;
        }

        if (isset($_POST['with-selected']) && $_POST['with-selected'] == 'delete') {
            $done = 0;
            $count = count($_POST['selected']);
            foreach ($_POST['selected'] as $file) {
                if (unlink($file)) {
                    $done++;
                }
            }
            if ($count == $done) {
                if ($count > 1) {
                    $message = 'The database backups were deleted.';
                }
                else {
                    $message = 'The database backup was deleted.';
                }
                $this->pageAlert(__($message), Alert::SUCCESS);
                return;
            }
            else {
                if ($count > 1) {
                    $message = 'Error: The database backups counld not be deleted.';
                }
                else {
                    $message = 'Error: The database backup could not be deleted.';
                }
                $this->pageAlert(__($message), Alert::ERROR);
                return;
            }
        }

        if (isset($_POST['with-selected']) && $_POST['with-selected'] == 'restore') {
            if (count($_POST['selected']) > 1) {
                $this->pageAlert(__('Multiple backups were selected for restore. Please select just <strong>one backup</strong>, and try again.'), Alert::ERROR);
                return;
            }
            $chosen = new DbBackup(basename($_POST['selected'][0]));
            $this->restoreAction($chosen);
            return;
        }

        if (isset($_POST['with-selected']) && $_POST['with-selected'] == 'download') {
            if (count($_POST['selected']) > 1) {
                $this->pageAlert(__('Multiple backups were selected for download. Please select just <strong>one backup</strong>, and try again.'), Alert::ERROR);
                return;
            }
            $this->downloadAction($_POST['selected'][0]);
        }
    }

    private function saveOptions() {
        Symphony::Configuration()->set('enable_logging', $_POST['options']['log_enabled'], 'db_manager');
        Symphony::Configuration()->set('log_authors', $_POST['options']['log_authors'], 'db_manager');
        Symphony::Configuration()->set('log_content', $_POST['options']['log_content'], 'db_manager');
        Symphony::Configuration()->write();
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

    private function restoreAction($backup) {
        if ($backup->unzip()) {
            if ($backup->restore()) {
                $this->notifyRestoreSuccess($backup);
                return;
            }
            $this->notifyRestoreFailure($backup);
            return;
        }
        $this->notifyUnzipFailure($backup);
        return;
    }

    private function downloadAction($backup) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false); // required for certain browsers
        header('Content-Type: application/x-gzip');
        header('Content-Disposition: attachment; filename=' . basename($backup) . ';');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($backup));
        print file_get_contents($backup);
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

