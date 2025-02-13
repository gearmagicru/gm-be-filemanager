<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Model;

use Gm;
use Gm\Panel\Data\Model\ModuleSettingsModel;

/**
 * Модель настроек модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class Settings extends ModuleSettingsModel
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns, $result, $message) {
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg(Gm::t(BACKEND, 'Settings successfully changed'), $this->t('{settings.title}'), 'accept');
            });
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'folderRootId'          => 'folderRootId', // идентификатор корневой папки
            'homePath'              => 'homePath', // базовый путь
            'homeUrl'               => 'homeUrl', // базовый URL-адрес
            'showUnreadableDirs'    => 'showUnreadableDirs', // показывать папки без доступа
            'showVCSFiles'          => 'showVCSFiles', // показывать файлы VCS
            'showDotFiles'          => 'showDotFiles', // показывать файлы и папки с точкой
            'showTreeFolderIcons'   => 'showTreeFolderIcons', // показывать значки папок
            'showTreeSomeIcons'     => 'showTreeSomeIcons', // показывать значки системных папок
            'showTreeToolbar'       => 'showTreeToolbar', // показывать панель инструментов
            'showTreeRoot'          => 'showTreeRoot', // показывать корневую папку
            'showTree'              => 'showTree', // показывать панель
            'resizeTree'            => 'resizeTree', // изменять размер панели
            'useTreeArrows'         => 'useTreeArrows', // показывать стрелочки
            'sortTreeFolders'       => 'sortTreeFolders', // сортировать папки
            'totalExpandedFolders'  => 'totalExpandedFolders', // количество раскрываемых папок
            'treePosition'          => 'treePosition', // положение панели
            'treeWidth'             => 'treeWidth', // ширина панели дерева папок
            'showOnlyFiles'         => 'showOnlyFiles', // показывать только файлы
            'dblClick'              => 'dblClick', // двойной клик на папке / файле
            'showGridColumnLines'   => 'showGridColumnLines', // показывать линии между столбцами
            'showGridRowLines'      => 'showGridRowLines', // показывать линии между строками
            'stripeGridRows'        => 'stripeGridRows', // чередование строк
            'showGridIcons'         => 'showGridIcons', // показывать значки
            'showPopupMenu'         => 'showPopupMenu', // показывать всплывающие меню
            'gridPageSize'          => 'gridPageSize', // количество файлов и папок на странице
            'showSizeColumn'        => 'showSizeColumn', // показывать столбец "Размер"
            'showTypeColumn'        => 'showTypeColumn', // показывать столбец "Тип"
            'showMimeTypeColumn'    => 'showMimeTypeColumn', // показывать столбец "MIME-тип"
            'showPermissionsColumn' => 'showPermissionsColumn', // показывать столбец "Права доступа"
            'showAccessTimeColumn'  => 'showAccessTimeColumn', // показывать столбец "Последний доступ"
            'showChangeTimeColumn'  => 'showChangeTimeColumn', // показывать столбец "Последнее обновление"
            'icons'                 => 'icons' // значки расширений файлов
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatterRules(): array
    {
        return [
            [['showUnreadableDirs', 'showVCSFiles', 'showDotFiles', 'showTreeFolderIcons', 'showTreeSomeIcons', 
              'showTreeToolbar', 'showTreeRoot', 'showTree', 'resizeTree', 'useTreeArrows', 'sortTreeFolders', 
              'showOnlyFiles', 'dblClick', 'showGridColumnLines', 'showGridRowLines', 'stripeGridRows', 'showGridIcons', 
              'showPopupMenu', 'showSizeColumn', 'showTypeColumn', 'showMimeTypeColumn', 'showPermissionsColumn', 
              'showAccessTimeColumn','showChangeTimeColumn'], 'logic' => [true, false]],
            [['totalExpandedFolders', 'treeWidth', 'gridPageSize'], 'type' => ['int']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['folderRootId', 'homePath', 'homeUrl', 'treePosition'], 'notEmpty'],
            // количество раскрываемых папок
            [
                'totalExpandedFolders',
                'between',
                'min' => 50, 'max' => 1000,
            ],
            // ширина панели дерева папок
            [
                'treeWidth',
                'between',
                'min' => 150, 'max' => 1000,
            ],
            // количество файлов и папок на странице
            [
                'gridPageSize',
                'between',
                'min' => 50, 'max' => 1000,
            ],
        ];
    }
}
