<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Widget;

use Gm\Config\Config;
use Gm\Stdlib\Collection;

/**
 * Виджет панели дерева папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Widget
 * @since 1.0
 */
class FolderTree extends \Gm\Panel\Widget\Widget
{
    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.view.tree.Tree',
        'Gm.view.plugin.PageSize'
    ];

    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype' => 'g-tree',
        /**
         * @var bool false, чтобы скрыть корневой узел.
         */
        'rootVisible' => true,
        /**
         * @var string CSS класс панели.
         */
        'cls' => 'g-tree gm-filemanager-tree ',
        /**
         * @var array Корневой узел дерева (Ext.data.Model | Ext.data.TreeModel).
         */
        'root' => [
            'id'       => 'home',
            'iconCls'  => 'g-icon-svg gm-filemanager__icon-folder_root',
            'expanded' => false,
            'leaf'     => false
        ],
        /**
         * @var array|Collection Конфигурация маршрутизатора узлов дерева.
         */
        'router' => [
            'rules' => [
                'data' => '{route}/{id}'
            ],
            'route' => ''
        ],
        /**
         * @var array|Collection Конфигурация хранения записей сетки (Ext.data.Store).
         */
        'store' => [
            'nodeParam' => 'path',
            'autoLoad'  => true,
            'proxy'     => [
                'type'   => 'ajax',
                'url'    => '',
                'method' => 'POST',
                'reader' => [
                    'rootProperty'    => 'data',
                    'successProperty' => 'success'

                ]
            ]
        ],
        'singleExpand' => false
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->store  = Collection::createInstance($this->store);
        $this->root   = Collection::createInstance($this->root);
        $this->router = Collection::createInstance($this->router);
        $this->tbar   = Collection::createInstance([
            'cls'      => 'gm-filemanager-toolbar',
            'defaults' => [
                'xtype'  => 'button',
                'cls'    => 'gm-filemanager-toolbar__btn',
                'width'  => 32,
                'height' => 32,
                'margin' => 1
            ],
            'items' => [
                [
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-expand g-icon-m_color_neutral-dark',
                    'tooltip' => '#Expand all folders',
                    'handler' => 'onExpandFolders'
                ],
                [
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-collapse g-icon-m_color_neutral-dark',
                    'tooltip' => '#Collapse all folders',
                    'handler' => 'onCollpaseFolders'
                ],
                [
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-refresh g-icon-m_color_neutral-dark',
                    'tooltip' => '#Refresh',
                    'handler' => 'onRefreshFolders'
                ],
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        $this->makeViewID();
        return true;
    }

    /**
     * @param string $position
     * 
     * @return void
     */
    public function setPosition(string $position): void
    {
        if ($position === 'left') {
            $this->region = 'west';
            $this->margin = ' 5px 0 0 0';
        } else
        if ($position === 'right') {
            $this->region = 'east';
            $this->margin = ' 0 0 5px 0';
        }
    }

    /**
     * @param Config $settings
     * 
     * @return void
     */
    public function applySettings(Config $settings): void
    {
        // идентификатор корневой папки дерева
        $this->root->id = $settings->folderRootId ?: 'home';
       //  положение панели
       $this->setPosition($settings->treePosition);
       // размер панели
       $this->width = $settings->treeWidth;
       // показывать стрелочки
       $this->useArrows = $settings->useTreeArrows;
       // сортировать папки
       $this->folderSort = $settings->sortTreeFolders;
       // изменять размер панели
       if (!$settings->resizeTree) {
            $this->split = false;
       }
       // показывать панель
       if (!$settings->showTree) {
            $this->hidden = true;
       }
       //  показывать панель инструментов
       if (!$settings->showTreeToolbar) {
            $this->tbar->hidden = true;
       }
       // показывать корень дерева
       if (!$settings->showTreeRoot) {
            $this->root->visible = false;
       }
    }
}
