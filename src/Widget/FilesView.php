<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Widget;

use Gm;
use Gm\Helper\Url;
use Gm\Config\Config;
use Gm\Stdlib\Collection;
use Gm\Panel\Widget\Widget;
use Gm\Panel\Helper\HtmlGrid;
use Gm\Panel\Widget\Navigator;
use Gm\Panel\Helper\HtmlNavigator as HtmlNav;

/**
 * Виджет отображения папок и файлов в виде сетки и списка.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Widget
 * @since 1.0
 */
class FilesView extends Widget
{
    /**
     * {@inheritdoc}
     */
    public Collection|array $params = [
        /**
         * @var string Короткое название класса виджета.
         */
        'xtype'  => 'gm-be-filemanager-filesview',
        'region' => 'center',
        'layout' => 'fit',
        'msgMustSelect'        => '#You must select a file or folder',
        'msgMustSelectFile'    => '#You must select a file',
        'msgMustSelectOne'     => '#Only one file or folder needs to be selected',
        'msgMustSelectArchive' => '#You only need to select the archive file',
        'msgDelConfirm'        => '#Are you sure you want to delete the selected files / folders ({0} pcs)? {1}',
        'msgDelConfirmFolders' => '#Are you sure you want to delete the selected folders ({0} pcs)? {1}',
        'msgDelConfirmFolder'  => '#Are you sure you want to delete the folder "{0}"?',
        'msgDelConfirmFiles'   => '#Are you sure you want to delete the selected files ({0} pcs)? {1}',
        'msgDelConfirmFile'    => '#Are you sure you want to delete the file "{0}"?',
        'msgCannotPasteFiles'  => '#Cannot paste files where they were copied or cut from',
        'msgCopyClipboard'     => '#Files / folders copied to clipboard',
        'msgCutClipboard'      => '#Files / folders cut to clipboard',
        'titleClipboard'       => '#Clipboard',
    ];

    /**
     * Виджет отображения папок / файлов в виде сетки.
     * 
     * @var GridView
     */
    public GridView $grid;

    /**
     * Виджет отображения папок / файлов в виде списка.
     * 
     * @var ListView
     */
    public ListView $list;

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->initGrid();
        $this->initList();

        $this->makeViewID(); // для того, чтобы сразу использовать `$this->id`
        $this->items = [$this->grid, $this->list];

        // панель навигации (Gm.view.navigator.Info GmJS)
        $this->navigator = new Navigator();
        $this->navigator->show = ['g-navigator-modules', 'g-navigator-info'];
        $this->navigator->info['tpl'] = HtmlNav::tags([
            HtmlNav::header('{name}'),
            HtmlGrid::tplIf(
                'isImage', 
                HtmlGrid::tag('div', '', [
                    'class' => 'gm-filemanager__celltip-preview', 
                    'style' => 'background-image: url({preview})'
                ]), 
                ''
            ),
            HtmlNav::fieldLabel($this->creator->t('Size'), '{size}'),
            HtmlNav::fieldLabel(
                 $this->creator->t('Type'), 
                 HtmlGrid::tplIf("type=='folder'", $this->creator->t('Folder'), $this->creator->t('File'))
            ),
            HtmlNav::fieldLabel($this->creator->t('MIME type'), '{mimeType}'),
            HtmlNav::fieldLabel($this->creator->t('Permissions'), '{permissions}'),
            HtmlNav::fieldLabel(
                $this->creator->t('Access time'), 
                '{accessTime:date("' . Gm::$app->formatter->formatWithoutPrefix('dateTimeFormat') . '")}'
            ),
            HtmlNav::fieldLabel(
                $this->creator->t('Change time'), 
                '{changeTime:date("' . Gm::$app->formatter->formatWithoutPrefix('dateTimeFormat') . '")}'
            )
        ]);

        // панель инструментов (Ext.toolbar.Toolbar Sencha ExtJS)
        $this->tbar = [
            'id'       => $this->id . '__toolbar',
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
                    'id'      => $this->id . '__btnHome',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-home g-icon-m_color_neutral-dark',
                    'tooltip' => '#Home',
                    'handler' => 'onHomeClick'
                ],
                [
                    'id'      => $this->id . '__btnGoUp',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-up g-icon-m_color_neutral-dark',
                    'tooltip' => '#Go up one level',
                    'handler' => 'onGoUpClick'
                ],
                '-',
                [
                    'id'      => $this->id . '__btnCreateFolder',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-create_folder',
                    'tooltip' => '#Create folder',
                    'handler' => 'onCreateFolderClick'
                ],
                [
                    'id'      => $this->id . '__btnCreateFile',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-create_file',
                    'tooltip' => '#Create file',
                    'handler' => 'onCreateFileClick'
                ],
                [
                    'id'      => $this->id . '__btnDelete',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-delete g-icon-m_color_neutral-dark',
                    'tooltip' => '#Delete selected folders / files',
                    'handler' => 'onDeleteClick'
                ],
                '-',
                [
                    'id'      => $this->id . '__btnRefresh',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-refresh g-icon-m_color_neutral-dark',
                    'tooltip' => '#Refresh',
                    'handler' => 'onRefreshClick'
                ],
                [
                    'id'      => $this->id . '__btnSearch',
                    'xtype'   => 'splitbutton',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-find g-icon-m_color_neutral-dark',
                    'tooltip' => '#Search for folder / file',
                    'width'   => 50,
                    'menu'    => [
                        'items' => [
                            'xtype'       => 'form',
                            'action'      =>  Url::toMatch('files/filter'),
                            'cls'         => 'g-form-filter',
                            'flex'        => 1,
                            'width'       => 400,
                            'height'      => 180,
                            'bodyPadding' => 8,
                            'defaults'    => [
                                'labelAlign' => 'right',
                                'labelWidth' => 100,
                                'width'      => '100%'
                            ],
                            'items' => [
                                [
                                    'xtype'      => 'textfield',
                                    'name'       => 'name',
                                    'fieldLabel' => '#Search name',
                                ],
                                [
                                    'id'      => $this->id . '__searchPath',
                                    'xtype'      => 'textfield',
                                    'name'       => 'path',
                                    'fieldLabel' => '#Search location',
                                ],
                                [
                                    'xtype'      => 'radio',
                                    'name'       => 'type',
                                    'inputValue' => 'file',
                                    'padding'    => '0 0 0 100px',
                                    'boxLabel'   => '#find File',
                                    'checked'    => true
                                ],
                                [
                                    'xtype'      => 'radio',
                                    'name'       => 'type',
                                    'inputValue' => 'folder',
                                    'padding'    => '0 0 0 100px',
                                    'boxLabel'   => '#find Path'
                                ]
                            ],
                            'buttons' => [
                                [
                                    'text'    => '#Find',
                                    'handler' => 'onSearch'
                                ], 
                                [
                                    'text'    => '#Reset',
                                    'handler' => 'onSearchReset'
                                ]
                            ]
                        ]
                    ],
                    'listeners' => [
                        'menushow' => 'onSearchDropdown'
                    ]
                ],
                [
                    'iconCls'         => 'g-icon-svg gm-filemanager__icon-profile_on g-icon-m_color_neutral-dark',
                    'activeIconCls'   => 'g-icon-svg gm-filemanager__icon-profile_on g-icon-m_color_neutral-dark',
                    'inactiveIconCls' => 'g-icon-svg gm-filemanager__icon-profile_off g-icon-m_color_neutral-dark',
                    'tooltip'         => '#Profiling a folder / file',
                    'enableToggle'    => true,
                    'toggleHandler'   => 'onProfilingClick'
                ],
                '-',
                [
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-select g-icon-m_color_neutral-dark',
                    'tooltip' => '#Select all',
                    'handler' => 'onSelectAllClick'
                ],
                [
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-inselect g-icon-m_color_neutral-dark',
                    'tooltip' => '#Invert selection',
                    'handler' => 'onInvertSelectionClick'
                ],
                [
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-unselect g-icon-m_color_neutral-dark',
                    'tooltip' => '#Remove selection',
                    'handler' => 'onRemoveSelectionClick'
                ],
                '-',
                [
                    'id'      => $this->id . '__btnUpload',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-upload g-icon-m_color_neutral-dark',
                    'tooltip' => '#Upload file',
                    'handler' => 'onUploadClick'
                ],
                [
                    'id'      => $this->id . '__btnDonwload',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-download g-icon-m_color_neutral-dark',
                    'tooltip' => '#Download selected folders / files',
                    'handlerArgs' => [
                        'route' => Gm::getAlias('@match/download/prepare')
                    ],
                    'handler' => 'onDownloadClick'
                ],
                '-',
                [
                    'id'      => $this->id . '__btnCompress',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-compress g-icon-m_color_neutral-dark',
                    'tooltip' => '#Archive',
                    'handler' => 'onCompressClick'
                ],
                [
                    'id'      => $this->id . '__btnExtract',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-extract g-icon-m_color_neutral-dark',
                    'tooltip' => '#Extract from archive',
                    'handler' => 'onExtractClick'
                ],
                '-',
                [
                    'id'      => $this->id . '__btnRename',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-rename g-icon-m_color_neutral-dark',
                    'tooltip' => '#Rename',
                    'handler' => 'onRenameClick'
                ],
                [
                    'id'      => $this->id . '__btnEdit',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-edit g-icon-m_color_neutral-dark',
                    'tooltip' => '#Edit file',
                    'handler' => 'onEditClick'
                ],
                [
                    'id'      => $this->id . '__btnView',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-view g-icon-m_color_neutral-dark',
                    'tooltip' => '#View file',
                    'handler' => 'onViewClick'
                ],
                [
                    'id'      => $this->id . '__btnPerms',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-permissions g-icon-m_color_neutral-dark',
                    'tooltip' => '#Permissions',
                    'handler' => 'onPermissionsClick'
                ],
                [
                    'id'      => $this->id . '__btnAttr',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-attributes g-icon-m_color_neutral-dark',
                    'tooltip' => '#Information about the selected folder/file',
                    'handler' => 'onAttributesClick'
                ],
                '-',
                [
                    'id'      => $this->id . '__btnCut',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-cut g-icon-m_color_neutral-dark',
                    'tooltip' => '#Move selected folders / files to clipboard',
                    'handler' => 'onCutClick'
                ],
                [
                    'id'      => $this->id . '__btnCopy',
                    'iconCls' => 'g-icon-svg gm-filemanager__icon-copy g-icon-m_color_neutral-dark',
                    'tooltip' => '#Copy selected folders / files',
                    'handler' => 'onCopyClick'
                ],
                [
                    'id'       => $this->id . '__btnPaste',
                    'iconCls'  => 'g-icon-svg gm-filemanager__icon-paste',
                    'tooltip'  => '#Paste the contents of the buffer into the current folder',
                    'handler'  => 'onPasteClick',
                    'disabled' => true
                ],
                '-',
                [
                    'iconCls'      => 'g-icon-svg gm-filemanager__icon-grid g-icon-m_color_neutral-dark',
                    'tooltip'      => '#Grid',
                    'pressed'      => true,
                    'enableToggle' => true,
                    'toggleGroup'  => 'view',
                    'handler'      => 'onToggleGrid'
                ],
                [
                    'iconCls'      => 'g-icon-svg gm-filemanager__icon-list g-icon-m_color_neutral-dark',
                    'tooltip'      => '#List',
                    'enableToggle' => true,
                    'toggleGroup'  => 'view',
                    'handler'      => 'onToggleList'
                ],
                '-',
                [
                    'iconCls'     => 'g-icon-svg gm-filemanager__icon-info g-icon-m_color_neutral-dark',
                    'tooltip'     => '#Help',
                    'handlerArgs' => [
                        'route' => Gm::alias('@backend', '/guide/modal/view?component=module:' . $this->creator->getId() . '&subject=index')
                    ],
                    'handler'     => 'onHelpClick'
                ],
                [
                    'iconCls'     => 'g-icon-svg gm-filemanager__icon-settings g-icon-m_color_neutral-dark',
                    'tooltip'     => '#Settings',
                    'handlerArgs' => ['route' => Gm::alias('@match', '/settings/view')],
                    'handler'     => 'onSettingsClick'
                ]
            ]
        ];
    }

    /**
     * Инициализация виджета отображения папок / файлов в виде списка.
     * 
     * @return void
     */
    protected function initList(): void
    {
        $this->list = new ListView([
            'id' => 'list-view', // list-view => gm-filemanager-list-view
        ]);
    }

    /**
     * Инициализация виджета отображения папок / файлов в виде сетки.
     * 
     * @return void
     */
    protected function initGrid(): void
    {
        $this->grid = new GridView([
            'id' => 'grid-view' // grid-view => gm-filemanager-grid-view
        ]);
    }

    /**
     * Применение настроек модуля к интерфейсу виджета.
     * 
     * @param Config $settings Конфигуратор настроек модуля.
     * 
     * @return void
     */
    public function applySettings(Config $settings): void
    {
        $this->grid->applySettings($settings);
        $this->list->applySettings($settings);

        // идентификатор корневой папки дерева
        $this->folderRootId = $settings->folderRootId ?: 'home';

        // панель навигации (Gm.view.navigator.Info GmJS)
        $infoTpl = [
            HtmlNav::header('{name}'),
            HtmlNav::tplIf(
                'isImage', 
                HtmlNav::tag('div', '', [
                    'class' => 'gm-filemanager__celltip-preview', 
                    'style' => 'background-image: url({preview})'
                ]), 
                HtmlNav::tag('div', '', [
                    'class' => 'gm-filemanager__celltip-icon', 
                    'style' => 'background-image: url({icon})'
                ]), 
            )
        ];
        // столбец "Размер"
        if ($settings->showSizeColumn) {
            $infoTpl[] = HtmlNav::fieldLabel($this->creator->t('Size'), '{size}');
        }
        // столбец "Тип"
        if ($settings->showTypeColumn) {
            $infoTpl[] = HtmlNav::fieldLabel(
                $this->creator->t('Type'), 
                HtmlNav::tplIf("type=='folder'", $this->creator->t('Folder'), $this->creator->t('File'))
            );
        }
        // столбец "MIME-тип"
        if ($settings->showMimeTypeColumn) {
            $infoTpl[] = HtmlNav::fieldLabel($this->creator->t('MIME type'), '{mimeType}');
        }
        // столбец "Права доступа"
        if ($settings->showPermissionsColumn) {
            $infoTpl[] = HtmlNav::fieldLabel($this->creator->t('Permissions'), '{permissions}');
        }
        // столбец "Последний доступ"
        if ($settings->showAccessTimeColumn) {
            $infoTpl[] = HtmlNav::fieldLabel(
                $this->creator->t('Access time'), 
                '{accessTime:date("' . Gm::$app->formatter->formatWithoutPrefix('dateTimeFormat') . '")}'
            );
        }
        // столбец "Последнее обновление"
        if ($settings->showChangeTimeColumn) {
            $infoTpl[] = HtmlNav::fieldLabel(
                $this->creator->t('Change time'), 
                '{changeTime:date("' . Gm::$app->formatter->formatWithoutPrefix('dateTimeFormat') . '")}'
            );
        }
        $this->navigator->info['tpl'] = HtmlNav::tags($infoTpl);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender(): bool
    {
        $this->makeViewID();
        return true;
    }
}