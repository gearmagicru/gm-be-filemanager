<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Widget;

use Gm;
use Gm\Panel\Widget\TabWidget;

/**
 * Виджет основной панели файлового менеджера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Widget
 * @since 1.0
 */
class Desk extends TabWidget
{
    /**
     * {@inheritdoc}
     */
    public array $css = ['/desk.css'];

    /**
     * Виджет панели дерева папок.
     * 
     * @var FolderTree
     */
    public FolderTree $folderTree;

    /**
     * Виджет панели отображения файлов.
     * 
     * @var FilesView
     */
    public FilesView $filesView;

    /**
     * {@inheritdoc}
     */
    public array $requires = [
        'Gm.be.filemanager.FileViews',
        'Gm.be.filemanager.FolderTreeController',
        'Gm.be.filemanager.FileViewsController'
    ];

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->initFolderTree();
        $this->initFilesView();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $this->layout = 'border';
        $this->title = '#{name}';
        $this->tooltip = [
            'icon'  => $this->imageSrc('/icon.svg'),
            'title' => '#{name}',
            'text'  => '#{description}'
        ];
        $this->icon = $this->imageSrc('/icon_small.svg');
        $this->items = [$this->folderTree, $this->filesView];

        $this->setNamespaceJS('Gm.be.filemanager');
    }

    /**
     * Инициализация панели отображения файлов.
     *
     * @return void
     */
    protected function initFilesView(): void
    {
        // панель отображения файлов (Ext.tree.Panel Sencha ExtJS)
        $this->filesView = new FilesView([
            'id'         => 'view', // view => gm-filemanager-view
            'controller' => 'gm-be-filemanager-files',
            'baseRoute'  => Gm::alias('@match')
        ]);
        $this->filesViewId = $this->filesView->makeViewID();
    }

    /**
     * Инициализация панели дерева папок.
     *
     * @return void
     */
    protected function initFolderTree(): void
    {
        // панель дерева папок (Gm.view.tree.Tree GmJS)
        $this->folderTree = new FolderTree([
            'id'        => 'tree', // tree => gm-filemanager-tree
            'split'     => ['size' => 2],
            'useArrows' => true,
            'router' => [
                'rules' => [
                    'data' => '{route}/data'
                ],
                'route' => Gm::alias('@route', '/folders')
            ],
            'controller' => 'gm-be-filemanager-folders'
        ]);
        $this->folderTree->root->text = Gm::$app->request->getServerName();
        $this->folderTreeId = $this->folderTree->makeViewID();
    }
}
