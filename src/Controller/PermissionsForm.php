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
use Gm\Stdlib\BaseObject;
use Gm\Panel\Widget\Form;
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Widget\EditWindow;
use Gm\Filesystem\Filesystem as Fs;
use Gm\Panel\Controller\FormController;
use Gm\Backend\FileManager\Model\FileProperties;
use Gm\Backend\FileManager\Model\FolderProperties;

/**
 * Контроллер формы установки прав доступа файлу / папки.
 * 
 * Маршруты контроллера:
 * - 'permissions/folder', выводит интерфейс формы установки прав доступа папке;
 * - 'permissions/file', выводит интерфейс формы установки прав доступа файлу;
 * - 'permissions/perfom', установливает права доступа файлу / папке.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class PermissionsForm extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'PermissionsForm';

    /**
     * Идентификатор выбранного файла / папки.
     * 
     * @var string
     */
    protected string $fileId = '';

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
                        $this->fileId = Gm::$app->request->getPost('id', '');
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
     * @return FileProperties|FolderProperties|\Gm\Backend\FileManager\Model\Permissions
     */
    public function getModel(string $name = null, array $config = []): ?BaseObject
    {
        // определение названия модели: 'FileProperties', 'FolderProperties'
        if ($name === null) {
            $name = ucfirst($this->actionName) . 'Properties';
            // идентификатор файла / папки
            $config['id'] = $this->fileId;
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

        /** @var false|string $permissions Права доступа */
        $permissions = $model->getPermissions(true, false);
        // если невозможно определить права доступа
        if ($permissions === false) {
            $this->getResponse()
                ->meta->error(
                    $this->t('Unable to determine permissions for "{0}"', [$model->getBaseName()])
                );
            return null;
        }
        /** @var array $groups Группы прав доступа */
        $groups = Fs::permissionsToArray(intval($permissions, 8));

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 500;
        $window->autoHeight = true;
        $window->resizable = false;
        $window->title = $this->t(
            '{permissions.' . $this->actionName . '.title}', 
            [$model->getBaseName()]
        );
        $window->titleTpl = $window->title;
        $window->iconCls  = 'g-icon-svg gm-filemanager__icon-permissions';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->makeViewID(); // для того, чтобы сразу использовать `$window->form->id`
        $window->form->controller = 'gm-be-filemanager-pms';
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/permissions'),
            'state' => Form::STATE_UPDATE,
            'rules' => [
                'folder' => '{route}/folder',
                'file'   => '{route}/file',
                'update' => '{route}/perfom'
            ]
        ]);
        $window->form->bodyPadding = 10;
        $window->form->buttons = ExtForm::buttons(
            [
                'help' => ['subject' => 'permissions'], 
                'save'  => ['text' => '#Apply', 'iconCls' => ''], 
                'cancel'
            ]
        );
        $window->form->items = [
            [
                'xtype' => 'hidden',
                'name'  => 'type',
                'value' => $this->actionName
            ],
            [
                'xtype' => 'hidden',
                'name'  => 'fileId',
                'value' => $this->fileId
            ],
            [
                'xtype'  => 'container',
                'height' => 'auto',
                'layout' => 'column',
                'margin' => '0 0 5px 0',
                'items'  => [
                    [
                        'xtype'       => 'fieldset',
                        'columnWidth' => '0.333',
                        'margin'      => '2',
                        'title'       => '#Owner permission',
                        'defaults'    => [
                            'ui'         => 'switch',
                            'xtype'      => 'checkbox',
                            'inputValue' => 1,
                            'listeners'  => ['change'=> 'onCheckPermission']
                        ],
                        'items' => [
                            [
                                'boxLabel' => '#Read',
                                'id'       => $window->form->id . '__or',
                                'name'     => 'groups[owner][r]',
                                'value'    => !empty($groups['owner']['r'])
                            ],
                            [
                                'boxLabel' => '#Write',
                                'id'       => $window->form->id . '__ow',
                                'name'     => 'groups[owner][w]',
                                'value'    => !empty($groups['owner']['w'])
                            ],
                            [
                                'boxLabel' => '#Execution',
                                'id'       => $window->form->id . '__ox',
                                'name'     => 'groups[owner][x]',
                                'value'    => !empty($groups['owner']['x'])
                            ]
                        ]
                    ],
                    [
                        'xtype'       => 'fieldset',
                        'columnWidth' => '0.333',
                        'margin'      => '2',
                        'title'       => '#Group permission',
                        'defaults'    => [
                            'ui'         => 'switch',
                            'xtype'      => 'checkbox',
                            'inputValue' => 1,
                            'listeners'  => ['change'=> 'onCheckPermission']
                        ],
                        'items'=> [
                            [
                                'boxLabel' => '#Read',
                                'id'       => $window->form->id . '__gr',
                                'name'     => 'groups[group][r]',
                                'value'    => !empty($groups['group']['r'])
                            ],
                            [
                                'boxLabel' => '#Write',
                                'id'       => $window->form->id . '__gw',
                                'name'     => 'groups[group][w]',
                                'value'    => !empty($groups['group']['w'])
                            ],
                            [
                                'boxLabel' => '#Execution',
                                'id'       => $window->form->id . '__gx',
                                'name'     => 'groups[group][x]',
                                'value'    => !empty($groups['group']['x'])
                            ]
                        ]
                    ],
                    [
                        'xtype'       => 'fieldset',
                        'columnWidth' => '0.333',
                        'margin'      => '2',
                        'title'       => '#World permission',
                        'defaults'    => [
                            'ui'         => 'switch',
                            'xtype'      => 'checkbox',
                            'inputValue' => 1,
                            'listeners'  => ['change'=> 'onCheckPermission']
                        ],
                        'items'=> [
                            [
                                'boxLabel' => '#Read',
                                'id'       => $window->form->id . '__wr',
                                'name'     => 'groups[world][r]',
                                'value'    => !empty($groups['world']['r'])
                            ],
                            [
                                'boxLabel' => '#Write',
                                'id'       => $window->form->id . '__ww',
                                'name'     => 'groups[world][w]',
                                'value'    => !empty($groups['world']['w'])
                            ],
                            [
                                'boxLabel' => '#Execution',
                                'id'       => $window->form->id . '__wx',
                                'name'     => 'groups[world][x]',
                                'value'    => !empty($groups['world']['x'])
                            ]
                        ]
                    ]
                ]
           ],
            [
                'id'         => $window->form->id . '__permissions',
                'xtype'      => 'textfield',
                'fieldLabel' => '#Numerical value',
                'labelAlign' => 'right',
                'labelWidth' => 156,
                'name'       => 'permissions',
                'value'      => $permissions,
                'maxLength'  => 10,
                'width'      => 315
            ]
        ];
        $window
            ->setNamespaceJS('Gm.be.filemanager')
            ->addRequire('Gm.be.filemanager.PermissionsController');
        return $window;
    }

    /**
     * Действие "folder" выводит интерфейса формы "Права доступа папке".
     * 
     * @return Response
     */
    public function folderAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Widget\EditWindow $widget */
        $widget = $this->getWidget();
        if ($widget === null) {
            return $response;
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие "file" выводит интерфейса формы "Права доступа файлу".
     * 
     * @return Response
     */
    public function fileAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Panel\Widget\EditWindow $widget */
        $widget = $this->getWidget();
        if ($widget === null) {
            return $response;
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие контроллера "perfom" выполняет установку прав доступа файлу / папке.
     * 
     * @return Response
     */
    public function perfomAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var \Gm\Backend\FileManager\Model\Rename $model */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        $form = $model;
        // загрузка атрибутов в модель из запроса
        if (!$form->load($request->getPost())) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        // проверка атрибутов
        if (!$form->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$form->getError()]));
            return $response;
        }

        // попытка выполнить действие над файлом / папкой
        if (!$form->run()) {
            $response
                ->meta->error(
                    $form->hasErrors() ? $form->getError() : $this->module->t('Error performing an action on a file / folder')
                );
            return $response;
        }
        return $response;
    }
}
