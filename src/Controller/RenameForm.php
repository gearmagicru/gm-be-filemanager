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
use Gm\Panel\Controller\FormController;
use Gm\Backend\FileManager\Model\FileProperties;
use Gm\Backend\FileManager\Model\FolderProperties;

/**
 * Контроллер формы изменения имени файла или папки.
 * 
 * Маршруты контроллера:
 * - 'rename/folder', выводит интерфейс формы изменения имени папки;
 * - 'rename/file', выводит интерфейс формы изменения имени файла;
 * - 'rename/perfom', выполняет измение имени папки / файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class RenameForm extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'RenameForm';

    /**
     * Идентификатор выбранного файла / папки.
     * 
     * @var string|null
     */
    protected ?string $fileId = '';

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

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 450;
        $window->autoHeight = true;
        $window->resizable = false;
        $window->title = $this->t(
            '{rename.' . $this->actionName . '.title}', 
            [$model->getBaseName()]
        );
        $window->titleTpl = $window->title;
        $window->iconCls  = 'g-icon-svg gm-filemanager__icon-rename';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/rename'),
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
                'help' => ['subject' => 'rename'], 
                'save' => ['text' => '#Rename', 'iconCls' => ''], 
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
                'name'  => 'oldName',
                'value' => $this->fileId
            ],
            [
                'xtype'      => 'textfield',
                'name'       => 'newName',
                'fieldLabel' => '#New name',
                'labelWidth' => 120,
                'labelAlign' => 'right',
                'allowBlank' => false,
                'value'      => $this->getModel()->getBaseName(),
                'anchor'     => '100%'
            ]
        ];
        return $window;
    }

    /**
     * Действие "folder" выводит интерфейса формы "Переименование папки".
     * 
     * @return Response
     */
    public function folderAction(): Response
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

    /**
     * Действие "file" выводит интерфейса формы "Переименование файла".
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

    /**
     * Действие "perfom" выполняет изменение названия файла / папки.
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
