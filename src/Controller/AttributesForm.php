<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Controller;

use Gm;
use Gm\Panel\Widget\Form;
use Gm\Stdlib\BaseObject;
use Gm\Panel\Http\Response;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;
use Gm\Panel\Helper\HtmlNavigator as Nav;
use Gm\Backend\FileManager\Model\Archive;
use Gm\Backend\FileManager\Model\FileProperties;
use Gm\Backend\FileManager\Model\FolderProperties;

/**
 * Контроллер информации о файле / папке.
 * 
 * Маршруты контроллера:
 * - 'attributes/file', выводит интерфейс формы c информацией о файле;
 * - 'attributes/folder', выводит интерфейс формы c информацией о папке.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class AttributesForm extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * Идентификатор выбранного файла / папки.
     * 
     * @var string|null
     */
    protected ?string $fileId = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_BEFORE_ACTION, function ($controller, $action, &$result) {
                switch ($action) {
                    case 'file': 
                    case 'folder': 
                        $this->fileId = Gm::$app->request->getPost('id');
                        if ($this->fileId) {
                            /** @var FileProperties|FolderProperties $model */
                            $model = $this->getModel();
                            if (!$model->exists(true)) {
                                $this->getResponse()
                                    ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['file']));
                                $result = false;
                                return;
                            }
                        } else {
                            $this->getResponse()
                                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['file']));
                            $result = false;
                            return;
                        }
                        break;
                }
            });
    }

    /**
     * {@inheritdoc}
     * 
     * @return FileProperties|FolderProperties
     */
    public function getModel(string $name = null, array $config = []): ?BaseObject
    {
        // определение названия модели: 'FileProperties', 'FolderProperties'
        if ($name === null) {
            $name = ucfirst($this->actionName) . 'Properties';

            /** @var \Gm\Config\Config $settings */
            $settings = $this->module->getSettings();
            // значки файлов
            if ($settings && $settings->icons) {
                $config['icons'] = $settings->icons;
            }
            // идентификатор файла / папки
            $config['id'] = $this->fileId;
            // URL-адрес к значкам
            $config['fileIconsUrl'] = $this->module->getFileIconsUrl();
        }

        return parent::getModel($name, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var FileProperties|FolderProperties $model */
        $model = $this->getModel();

        /** @var string Название файла */
        $name = $model->getBaseName();

        /** @var \Gm\Panel\Widget\EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->ui = 'light';
        $window->cls = 'g-window_profile';
        $window->width = 550;
        $window->autoHeight = true;
        $window->resizable = false;
        $window->title = $this->t(
            '{attributes.' . $this->actionName . '.title}', [$name]
        );
        $window->titleTpl = $window->title;
        $window->iconCls  = 'g-icon-svg g-icon-m_color_base gm-filemanager__icon-attributes';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/attributes'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'folder' => '{route}/folder',
                'file'   => '{route}/file'
            ]
        ]);
        $window->form->bodyPadding = '5 10 5 10';
        $window->form->defaults = [
            'xtype'      => 'displayfield',
            'ui'         => 'parameter',
            'labelWidth' => 150,
            'labelAlign' => 'right'
        ];
        $window->form->items = [
            [
                'xtype'      => 'container',
                'height'     => 150,
                'style'      => [
                    'background'    => 'center / contain no-repeat url(' .  $model->getPreview() . ')',
                    'margin-bottom' => '7px'
                ]
            ],
            [
                'ui'         => 'parameter-head',
                'fieldLabel' => '#Name',
                'labelWidth' => 60,
                'name'       => 'name',
                'value'      => $name
            ],
            [
                'ui'           => 'parameter-head',
                'labelWidth'   => 60,
                'fieldLabel'   => '#Type',
                'fieldBodyCls' => ($value = $model->getType()) ? '' : 'gm-filemanager-error',
                'name'         => 'type',
                'value'        => '#' . ($value ?: 'impossible to determine')
            ],
            [
                'fieldLabel' => '#Path',
                'name'       => 'path',
                'labelWidth' => 60,
                'value'      => $model->getDirName()
            ],
            [
                'xtype' => 'label',
                'ui'    => 'header-line',
                'style' => 'border-bottom:1px solid #e0e0e0'
            ],
            [
                'fieldLabel' => '#Size',
                'name'       => 'size',
                'value'      => $model->isFile() ? $model->getSize() : '#unknown'
            ],
            [
                'fieldLabel'   => '#Permissions',
                'fieldBodyCls' => ($value = $model->getPermissions()) ? '' : 'gm-filemanager-error',
                'name'         => 'permissions',
                'value'        => $value ?: '#impossible to determine'
            ],
            [
                'fieldLabel'   => '#MIME-type',
                'fieldBodyCls' => ($value = $model->getMimeType()) ? '' : 'gm-filemanager-error',
                'name'         => 'mime',
                'value'        => $value ?: '#impossible to determine'
            ],
            [
                'fieldLabel'   => '#Owner ID',
                'fieldBodyCls' => ($value = $model->getOwnerId()) ? '' : 'gm-filemanager-error',
                'name'         => 'ownerId',
                'value'        => $value ?: '#impossible to determine'
            ],
            [
                'fieldLabel'   => '#Group ID',
                'fieldBodyCls' => ($value = $model->getGroupId()) ? '' : 'gm-filemanager-error',
                'name'         => 'groupId',
                'value'        => $value ?: '#impossible to determine'
            ],
            [
                'fieldLabel' => '#Readable',
                'name'       => 'readable',
                'value'      => Nav::checkIcon($model->isReadable(), 17)
            ],
            [
                'fieldLabel' => '#Writable',
                'name'       => 'writable',
                'value'      => Nav::checkIcon($model->isWritable(), 17),
            ],
            [
                'fieldLabel' => $model->isFile() ? '#The configuration file' : '#System folder',
                'hidden'     => !$model->isSystem(),
                'name'       => 'system',
                'value'      => Nav::checkIcon($model->isSystem(), 17),
            ],
            [
                'xtype' => 'label',
                'ui'    => 'header-line',
                'style' => 'border-bottom:1px solid #e0e0e0'
            ],
            [
                'fieldLabel'   => $model->isFile() ? '#Changing a file' : '#Changing a folder',
                'fieldBodyCls' => ($value = $model->getChangeTime()) ? '' : 'gm-filemanager-error',
                'name'         => 'ctime',
                'value'        => $value ?: '#impossible to determine'
            ],
            [
                'fieldLabel'   => '#Access time',
                'fieldBodyCls' => ($value = $model->getAccessTime()) ? '' : 'gm-filemanager-error',
                'name'         => 'actime',
                'value'        => $value ?: '#impossible to determine'
            ],
            [
                'fieldLabel'   => '#Change time',
                'fieldBodyCls' => ($value = $model->getModifiedTime()) ? '' : 'gm-filemanager-error',
                'name'         => 'mtime',
                'value'        => $value ?: '#impossible to determine'
            ],
        ];

        if ($model->isFile()) {
            // если архив
            if ($model->isArchive()) {
                $info = (new Archive(['filename' => $model->getFilename()]))->getInfo();
                if ($info) {
                    $window->form->items[] = [
                        'xtype' => 'label',
                        'ui'    => 'header-line',
                        'style' => 'border-bottom:1px solid #e0e0e0'
                    ];
                    $window->form->items[] = [
                        'fieldLabel'   => '#Archive type',
                        'fieldBodyCls' => $info['name'] ? '' : 'gm-filemanager-error',
                        'name'         => 'archiveType',
                        'value'        => $info['name'] ? $this->module->t($info['name']) : '#impossible to determine'
                    ];
                    $window->form->items[] = [
                        'fieldLabel'   => '#Files in the archive',
                        'fieldBodyCls' => ($value = $info['count']) ? '' : 'gm-filemanager-error',
                        'name'         => 'archiveFiles',
                        'value'        => $value ?: '#impossible to determine'
                    ];
                }
            }
            // если изображение
            else if ($model->isImage()) {
                $info = $model->getImageInfo();
                if ($info) {
                    $window->form->items[] = [
                        'xtype' => 'label',
                        'ui'    => 'header-line',
                        'style' => 'border-bottom:1px solid #e0e0e0'
                    ];
                    $window->form->items[] = [
                        'fieldLabel'   => '#Width',
                        'fieldBodyCls' => ($value = $info['width']) ? '' : 'gm-filemanager-error',
                        'name'         => 'width',
                        'value'        => $value ?: '#impossible to determine'
                    ];
                    $window->form->items[] = [
                        'fieldLabel'   => '#Height',
                        'fieldBodyCls' => ($value = $info['height']) ? '' : 'gm-filemanager-error',
                        'name'         => 'height',
                        'value'        => $value ?: '#impossible to determine'
                    ];
                    $window->form->items[] = [
                        'fieldLabel' => '#Color',
                        'name'       => 'color',
                        'value'      => $info['color'] ? '#yes' : '#no'
                    ];
                    if ($info['comment']) {
                        $window->form->items[] = [
                            'fieldLabel' => '#Comment',
                            'name'       => 'comment',
                            'value'      => $info['comment']
                        ];
                    }
                    if ($info['copyright']) {
                        $window->form->items[] = [
                            'fieldLabel' => 'Copyright',
                            'name'       => 'copyright',
                            'value'      => $info['copyright']
                        ];
                    }
                    if ($info['software']) {
                        $window->form->items[] = [
                            'fieldLabel' => 'Software',
                            'name'       => 'software',
                            'value'      => $info['software']
                        ];
                    }
                }
            }
        }    
        return $window;
    }

    /**
     * Действие "folder" выводит интерфейса формы "Информация о папке".
     * 
     * @return Response
     */
    public function folderAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var EditWindow $widget */
        $widget = $this->getWidget();
        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие "file" выводит интерфейса формы "Информация о файле".
     * 
     * @return Response
     */
    public function fileAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Widget\EditWindow $widget */
        $widget = $this->getWidget();
        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }
}
