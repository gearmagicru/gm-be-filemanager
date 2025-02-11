/*!
 * Контроллер представления виджета панели отображения файлов.
 * Модуль "Файловый менеджер".
 * Copyright 2015 Вeб-студия GearMagic. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://gearmagic.ru/license/
 */

Ext.define('Gm.be.filemanager.FileViewsController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gm-be-filemanager-files',

    /**
     * @cfg {String} folderTreeId
     * Идентификатор дерева папок.
     */
    folderTreeId: '',

    /**
     * @cfg {String} filesViewId
     * Идентификатор списка файлов.
     */
    filesViewId: '',

    /**
     * Инициализация контроллера.
     * @param {Gm.be.filemanager.FileViews} view
     */
    init: function (view) {
        this.filesViewId = view.up().filesViewId;
        this.folderTreeId = view.up().folderTreeId;
    },

    /**
     * Событие после рендера сетки отображения файлов.
     * @param {Gm.view.grid.Grid} me
     * @param {Object} eOpts
     */
    afterRenderGrid: function (me, eOpts) {
        let controller = this;
        // связываем обработчик каждого пункта контекстного меню с обработчиком контроллера, 
        // т.к. на контекстное меню не распостраняется контроллер
        me.popupMenu.items.each((item, index) => {
            if (Ext.isDefined(item.viewEvent)) {
                item.on('click', controller[item.viewEvent], controller);
            }
        });
    },

    /**
     * Возвращает панель отображения файлов.
     * @return {Gm.be.filemanager.FileViews}
     */
    getFiles: function () { return Ext.getCmp(this.filesViewId); },

    /**
     * Возвращает дерево папок.
     * @return {Ext.tree.Panel}
     */
    getFolderTree: function () { return Ext.getCmp(this.folderTreeId); },

    /**
     * Выделить папку в панеле дерева папок.
     * @param {String} folder Идентификатор папки, которую необходимо выделить.
     */
    selectFolder: function (folder) {
        let tree = this.getFolderTree();

        if (!tree.isHidden()) {
            let node = tree.getStore().findNode('id', folder);
            if (node) {
                tree.getSelectionModel().select(node);
                tree.getView().focusRow(node);
            }
        }
    },

    /**
     * Развернуть папку в панеле дерева папок.
     * @param {String} folder Идентификатор папки, которую необходимо развернуть.
     */
    expandFolder: function (folder) {
        let tree = this.getFolderTree();

        if (!tree.isHidden()) {
            let node = tree.getStore().findNode('id', folder);
            if (node) {
                tree.getSelectionModel().select(node);
                tree.getView().focusRow(node);
                node.expand();
            }
        }
    },

    /**
     * Событие перед загрузкой файлов.
     * @param {Ext.data.Store} store
     * @param {Ext.data.operation.Operation} operation
     * @param {Object} eOpts
     */
    onBeforeLoadFiles: function (store, operation, eOpts) {
        this.toolbarDisabled(true);
    },

    /**
     * Событие после загрузки файлов.
     * @param {Ext.data.Store} store
     * @param {Ext.data.Model} records
     * @param {Boolean} successful
     * @param {Ext.data.operation.Operation} operation
     * @param {Object} eOpts
     */
    onLoadFiles: function (store, records, successful, operation, eOpts) {
        this.toolbarDisabled(false);
    },

    /**
     * Двойное нажатие на папке или файле сетки или списка.
     * @param {Ext.grid.Panel|Ext.panel.Panel} me
     * @param {Ext.data.Model} record
     * @param {HTMLElement} element
     * @param {Number} rowIndex
     * @param {Ext.event.Event} e
     * @param {Object} eOpts
     */
    onFileDblClick: function (me, record, element, rowIndex, e, eOpts) {
        let files = this.getFiles();
        if (record.get('isFolder')) {
            let folder = files.getSubFolder(record.get('name'));
            this.expandFolder(folder);
            files.load(folder);
        } else {
            let route = record.get('isImage') ? 'preview' : 'edit';
            files.loadWidget(route, { id: record.id });
        }
    },

    /**
     * Нажатие кнопки "Корневая папки".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onHomeClick: function (me, e, eOpts) {
        let files = this.getFiles(),
            folder = files.getHomeFolder();

        this.selectFolder(folder);
        files.load(folder);
    },

    /**
     * Нажатие кнопки "Перейти на один уровень выше".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onGoUpClick: function (me, e, eOpts) { 
        let files = this.getFiles(),
            folder = files.getTopFolder();

        this.selectFolder(folder);
        files.load(folder);
    },

    /**
     * Нажатие кнопки "Создать папку".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onCreateFolderClick: function (me, e, eOpts) { 
        let files = this.getFiles();
        files.loadWidget('create/folder', { path: files.getPath() }); 
    },

    /**
     * Нажатие кнопки "Создать файл".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onCreateFileClick: function (me, e, eOpts) { 
        let files = this.getFiles();
        files.loadWidget('create/file', { path: files.getPath() }); 
    },

    /**
     * Нажатие кнопки "Удалить выбранные папки / файлы".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onDeleteClick: function (me, e, eOpts) {
        let files = this.getFiles(),
            folders = this.getFolderTree(),
            selected = { files: [], folders: [] },
            confirm = '';

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            let item = me.parentMenu.activeRecord;
            if (item.isFolder) {
                selected.folders.push(item.name);
                confirm = files.msgDelConfirmFolder.put([item.name]);
            } else {
                selected.files.push(item.name);
                confirm = files.msgDelConfirmFile.put([item.name]);
            }
        // для Ext.button.Button
        } else {
            if (!files.isSelected()) {
                Ext.Msg.warning(files.msgMustSelect);
                return;
            }
            selected = files.getSelected(false, false);
            // если выделены только файлы
            if (selected.files.length > 0 && selected.folders.length === 0) {
                let sfiles = selected.files,
                    length = selected.files.length;
                if (length === 1)
                    confirm = files.msgDelConfirmFile.put([sfiles[0]]);
                else {
                    if (length > 10) {
                        sfiles = sfiles.slice(0, 10);
                        sfiles.push('...');
                    }
                    confirm = files.msgDelConfirmFiles.put([length, '<br> ' + sfiles.join(',<br>')]);
                }
            } else
            // если выделены только папки
            if (selected.folders.length > 0 && selected.files.length === 0) {
                let sfolders = selected.folders,
                    length = selected.folders.length;
                if (length === 1)
                    confirm = files.msgDelConfirmFolder.put([sfolders[0]]);
                else {
                    if (length > 10) {
                        sfolders = sfolders.slice(0, 10);
                        sfolders.push('...');
                    }
                    confirm = files.msgDelConfirmFolders.put([length, '<br> ' + sfolders.join(',<br>')]);
                }
            } else {
                let items = selected.folders.concat(selected.files),
                    length = items.length;
                if (items.length > 10) {
                    items = items.slice(0, 10);
                    items.push('...');
                }
                confirm = files.msgDelConfirm.put([length, '<br> ' + items.join(',<br>')]);
            }
        }

        Ext.Msg.confirm(
            Ext.Txt.confirmation,
            confirm,
            function (btn, text) {
                if (btn == 'yes') {
                    files.delete(
                        selected.folders.concat(selected.files),
                        function (success) {
                            if (success && !folders.isHidden() && selected.folders.length > 0) {
                                folders.controller.remove(selected.folders, files.getPath());
                            }
                    });
                }
            },
            this
        );
    },

    /**
     * Нажатие кнопки "Обновить".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onRefreshClick: function (me, e, eOpts) { this.getFiles().reload(); },

    /**
     * Нажатие кнопки "Профилирование папки / файла".
     * @param {Ext.button.Button} me
     * @param {Boolean} state Следующее состояние кнопки «истинно» означает нажатие.
     */
    onProfilingClick: function (me, state) {
        let filesView = me.up('panel');

        var info = filesView.navigator.info;
        if (!state)
            Ext.getCmp(info.id).update('');
        info.active = state;
        if (state) {
            Gm.app.navigator.expand();
        } else {
            Gm.app.navigator.collapse();
        }
    },

    /**
     * Нажатие кнопки "Выделить всё".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onSelectAllClick: function (me, e, eOpts) { this.getFiles().selectAll(); },

    /**
     * Нажатие кнопки "Инвертировать выделение".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onInvertSelectionClick: function (me, e, eOpts) { this.getFiles().invertSelection(); },

    /**
     * Нажатие кнопки "Убрать выделение".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onRemoveSelectionClick: function (me, e, eOpts) { this.getFiles().deselectAll(); },

    /**
     * Нажатие кнопки "Загрузить файл".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onUploadClick: function (me, e, eOpts) { 
        let files = this.getFiles();
        files.loadWidget('upload', { path: files.getPath() });
    },

    /**
     * Нажатие кнопки "Скачать выбранные файлы / папки".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onDownloadClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            files.download('download', [me.parentMenu.activeRecord.file]);
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                let selected = files.getSelected(false, false),
                    items = selected.folders.concat(selected.files);
                files.download('download', items);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Архивировать".
     * @param {Ext.button.Button|Ext.menu.Item}} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onCompressClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            files.loadWidget(
                'compress', 
                { files: Ext.encode([me.parentMenu.activeRecord.id]), path: files.getPath() }
            );
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                let selected = files.getSelectedFiles();
                files.loadWidget(
                    'compress', 
                    { files: Ext.encode(selected), path: files.getPath() }
                );
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Разархивировать".
     * @param {Ext.button.Button|Ext.menu.Item}} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onExtractClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            let file = me.parentMenu.activeRecord;
            if (file.isArchive) {
                files.loadWidget(
                    'extract', 
                    { id: file.id, path: files.getPath() }
                );
            } else
                Ext.Msg.warning(files.msgMustSelectArchive);
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                if (files.isOneSelected()) {
                    let file = files.getSelected(true, true);
                    if (file.get('isArchive')) {
                        files.loadWidget(
                            'extract', 
                            { id: file.id, path: files.getPath() }
                        );
                    } else
                        Ext.Msg.warning(files.msgMustSelectArchive);
                } else
                    Ext.Msg.warning(files.msgMustSelectArchive);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Переименовать".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onRenameClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            let file = me.parentMenu.activeRecord;
            files.loadWidget('rename/' + file.type, { id: file.id });
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                if (files.isOneSelected()) {
                    let file = files.getSelected(true, true);
                    files.loadWidget('rename/' + file.get('type'), { id: file.id });
                } else
                    Ext.Msg.warning(files.msgMustSelectOne);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Редактировать файл".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onEditClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            let file = me.parentMenu.activeRecord;
            files.loadWidget('edit', { id: file.id });
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                if (files.isOneSelected()) {
                    if (files.isFolderSelected())
                        Ext.Msg.warning(files.msgMustSelectFile);
                    else {
                        let file = files.getSelected(true, true);
                        files.loadWidget('edit', { id: file.id });
                    }
                } else
                    Ext.Msg.warning(files.msgMustSelectOne);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Просмотреть файл".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onViewClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            let file = me.parentMenu.activeRecord;
            files.loadWidget('preview', { id: file.id });
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                if (files.isOneSelected()) {
                    if (files.isFolderSelected())
                        Ext.Msg.warning(files.msgMustSelectFile);
                    else {
                        let file = files.getSelected(true, true);
                        files.loadWidget('preview', { id: file.id });
                    }
                } else
                    Ext.Msg.warning(files.msgMustSelectOne);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Права доступа".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onPermissionsClick: function (me, e, eOpts) {
        let files = this.getFiles();

         // для Ext.menu.Item
         if (me instanceof Ext.menu.Item) {
            let file = me.parentMenu.activeRecord;
            files.loadWidget('permissions/' + file.type, { id: file.id });
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                if (files.isOneSelected()) {
                    let file = files.getSelected(true, true);
                    files.loadWidget('permissions/' + file.get('type'), { id: file.id });
                } else
                    Ext.Msg.warning(files.msgMustSelectOne);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Копировать".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onCopyClick: function (me, e, eOpts) {
        let items,
            files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            items = [me.parentMenu.activeRecord.file];
        // для Ext.button.Button
        } else {
            if (files.isSelected())
                items = files.getSelectedFiles();
            else
                Ext.Msg.warning(files.msgMustSelect);
        }

        if (items) {
            files.clipboard.copy(items);
            Gm.getApp().popup.msg(files.msgCopyClipboard, files.titleClipboard, 'accept');
            Ext.getCmp(this.view.id + '__btnPaste').setDisabled(false);
        }
    },

    /**
     * Нажатие кнопки "Вырезать".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onCutClick: function (me, e, eOpts) {
        let items,
            files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            items = [me.parentMenu.activeRecord.file];
        // для Ext.button.Button
        } else {
            if (files.isSelected())
                items = files.getSelectedFiles();
            else
                Ext.Msg.warning(files.msgMustSelect);
        }

        if (items) {
            files.clipboard.cut(items);
            Gm.getApp().popup.msg(files.msgCutClipboard, files.titleClipboard, 'accept');
            Ext.getCmp(this.view.id + '__btnPaste').setDisabled(false);
        }
    },

    /**
     * Нажатие кнопки "Вставить".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onPasteClick: function (me, e, eOpts) {
        let files = this.getFiles();

        if (files.clipboard.canPaste()) {
            files.clipboard.paste();
            me.setDisabled(true);
        } else
            Ext.Msg.warning(files.msgCannotPasteFiles);
    },

    /**
     * Нажатие кнопки "Информация о выбранной папке / файле".
     * @param {Ext.button.Button|Ext.menu.Item} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onAttributesClick: function (me, e, eOpts) {
        let files = this.getFiles();

        // для Ext.menu.Item
        if (me instanceof Ext.menu.Item) {
            let file = me.parentMenu.activeRecord;
            files.loadWidget('attributes/' + file.type, { id: file.id });
        // для Ext.button.Button
        } else {
            if (files.isSelected()) {
                if (files.isOneSelected()) {
                    let file = files.getSelected(true, true);
                    files.loadWidget('attributes/' + file.get('type'), { id: file.id });
                } else
                    Ext.Msg.warning(files.msgMustSelectOne);
            } else
                Ext.Msg.warning(files.msgMustSelect);
        }
    },

    /**
     * Нажатие кнопки "Сетка файлов".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onToggleGrid: function (me, e, eOpts) {
        let files = this.getFiles();

        files.showGridView();
        files.load(files.path);
    },

    /**
     * Нажатие кнопки "Список файлов".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onToggleList: function (me, e, eOpts) {
        let files = this.getFiles();

        files.showListView();
        files.load(files.path);
    },

    /**
     * Нажатие кнопки "Справка".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onHelpClick: (me, e, eOpts) => { Gm.getApp().widget.load(me.handlerArgs.route); },

    /**
     * Нажатие кнопки "Настройки".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onSettingsClick: (me, e, eOpts) => { Gm.getApp().widget.load(me.handlerArgs.route); },

    /**
     * Нажатие кнопки "Найти".
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onSearch: function (me, e, eOpts) { 
        let files = this.getFiles(),
            form = me.up('form');

        files.fireEvent('beforeSearch');

        form.mask(Ext.Txt.loading);
        form.submit({
            clientValidation: true,
            url: form.action,
            success: function (me, action) {
                form.unmask();
                var response = Gm.response.normalize(action.response);
                if (!response.success) {
                    Ext.Msg.exception(response, false, true);
                }
                files.fireEvent('afterSearch', { success: response.success });
            },
            failure: function (me, action) {
                form.unmask();
                Ext.Msg.exception(action, true, true);
                files.fireEvent('afterSearch', { success: false });
            }
        });
    },

    /**
     * Нажатие кнопки "Сбросить" поиск.
     * @param {Ext.button.Button} me
     * @param {Event} e
     * @param {Object} eOpts
     */
    onSearchReset: function (me, e, eOpts) {
        let files = this.getFiles(),
            form = me.up('form');

        files.fireEvent('beforeSearchReset');

        form.mask(Ext.Txt.loading);
        form.reset();
        Ext.Ajax.request({
            url: form.action,
            method: 'post',
            /**
             * Успешное выполнение запроса.
             * @param {XMLHttpRequest} response Ответ.
             * @param {Object} opts Параметр запроса вызова.
             */
            success: function (response, opts) {
                form.unmask();
                var response = Gm.response.normalize(response);
                if (!response.success)
                    Ext.Msg.exception(response, false, true);
                files.fireEvent('afterSearchReset', { success: response.success });
            },
            /**
             * Ошибка запроса.
             * @param {XMLHttpRequest} response Ответ.
             * @param {Object} opts Параметр запроса вызова.
             */
            failure: function (response, opts) {
                form.unmask();
                Ext.Msg.exception(response, true, true);
                files.fireEvent('afterSearchReset', { success: false });
            }
        });
    },

    /**
     * Раскрытия мен после нажатие кнопки "Поиск".
     * @param {Ext.button.Button} me
     * @param {Ext.menu.Menu} menu
     * @param {Object} eOpts
     */
    onSearchDropdown: function (me, menu, eOpts) {
        let fldSchPath = Ext.getCmp(this.view.id + '__searchPath'),
            schPath = fldSchPath.getValue(),
            path = this.getFiles().getPath();

        if (schPath.length === 0)
            fldSchPath.setValue(path);
        else {
            if (schPath.indexOf(path) !== 0) {
                fldSchPath.setValue(path);
            }
        }
    }
});