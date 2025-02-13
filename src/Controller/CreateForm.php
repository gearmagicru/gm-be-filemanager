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

/**
 * Контроллер формы создания файла или папки.
 * 
 * Маршруты контроллера:
 * - 'create/folder', выводит интерфейс формы создания папки;
 * - 'create/file', выводит интерфейс формы создания файла;
 * - 'create/perfom', выполняет создание папки / файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class CreateForm extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'CreateForm';

    /**
     * Идентификатор выбранной папки.
     * 
     * @var string
     */
    protected string $pathId = '';

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
                        $pathId = Gm::$app->request->getPost('path', '');
                        if ($this->pathId = $pathId) {
                            /** @var \Gm\Backend\FileManager\Model\FolderProperties $folder */
                            $folder = $this->getModel('FolderProperties');
                            if (!$folder->exists()) {
                                $this->getResponse()
                                    ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
                            }
                        } else {
                            $this->getResponse()
                                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
                            $result = false;
                            return;
                        }
                        break;
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getModel(string $name = null, array $config = []): ?BaseObject
    {
        $config['id'] = $this->pathId;

        return parent::getModel($name, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 450;
        $window->autoHeight = true;
        $window->resizable = false;
        $window->title = '#{create.' . $this->actionName . '.title}';
        $window->titleTpl = $window->title;

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/create'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'folder' => '{route}/folder',
                'file'   => '{route}/file',
                'add'    => '{route}/perfom'
            ]
        ]);
        $window->form->bodyPadding = 10;
        $window->form->buttons = ExtForm::buttons(
            [
                'help' => ['subject' => 'create'], 
                'add'  => ['text' => '#Create'], 
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
                'name'  => 'path',
                'value' => $this->pathId
            ],
            [
                'xtype'      => 'textfield',
                'name'       => 'name',
                'fieldLabel' => '#' . $this->actionName . ' name',
                'labelWidth' => 80,
                'labelAlign' => 'right',
                'allowBlank' => false,
                'anchor'     => '100%'
            ]
        ];
        return $window;
    }

    /**
     * Действие "folder" выводит интерфейса формы "Создание папки".
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
     * Действие "file" выводит интерфейса формы "Создание файла".
     * 
     * @return Response
     */
    public function fileAction(): Response
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
     * Действие "perfom" выполяет создание файла / папки.
     * 
     * @return Response
     */
    public function perfomAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var \Gm\Backend\FileManager\Model\Create $model */
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
