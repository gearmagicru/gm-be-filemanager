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
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;
use Gm\Backend\FileManager\Model\FileProperties;

/**
 * Контроллер формы редактирования файла.
 * 
 * Маршруты контроллера:
 * - 'edit', 'edit/view', выводит интерфейс формы редактирования файла;
 * - 'edit/perfom', выполняет сохранение файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class EditForm extends FormController
{
    /**
     * Идентификатор выбранного файла.
     * 
     * @var string
     */
    protected string $fileId = '';

    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'EditForm';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_BEFORE_ACTION, function ($controller, $action, &$result) {
                switch ($action) {
                    case 'view': 
                        $this->fileId = Gm::$app->request->getPost('id', '');
                        if ($this->fileId) {
                            /** @var FileProperties $model */
                            $model = $this->getModel();
                            if (!$model->exists(true)) {
                                $this->getResponse()
                                    ->meta->error(
                                        $this->module->t('The selected file "{0}" cannot be edited', [$this->fileId])
                                    );
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
     */
    public function getModel(string $name = null, array $config = []): ?BaseObject
    {
        // определение названия модели 'FileProperties'
        if ($name === null) {
            $name =  'FileProperties';
            // идентификатор файла / папки
            $config['id'] = $this->fileId;
        }

        return parent::getModel($name, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow|false
    {
        /** @var FileProperties|FolderProperties $model */
        $model = $this->getModel();
        // если скрипт / текст
        if (!($model->isScript() || $model->isText())) {
            $this->getResponse()
                ->meta->error(
                    $this->module->t('The selected file "{0}" cannot be edited', [$this->fileId])
                );
            return false;
        }

        /** @var string Название файла */
        $name = $model->getBaseName();

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->cls = 'g-window_profile';
        $window->title = $this->t('{edit.title}', [$name]);
        $window->titleTpl = $window->title;
        $window->iconCls  = 'g-icon-svg gm-filemanager__icon-edit';
        $window->layout = 'fit';
        $window->width = 700;
        $window->height = 500;
        $window->resizable = true;
        $window->maximizable = true;

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/edit'),
            'state' => Form::STATE_UPDATE,
            'rules' => [
                'view'   => '{route}/view',
                'update' => '{route}/perfom'
            ]
        ]);
        $window->form->buttons = ExtForm::buttons(
            [
                'help' => ['subject' => 'edit'], 
                'save',
                'cancel'
            ]
        );

        /** @var null|object|\Gm\Stdlib\BaseObject $viewer */
        $viewer = Gm::$app->widgets->get('gm.wd.codemirror', [
            'fileExtension' => $model->getExtension()
        ]);
        if ($viewer)
            $editor = $viewer->run();
        else
            $editor = [
                'xtype' => 'textarea',
                'anchor' => '100% 100%'
            ];
        $editor['value'] = $model->getContent();
        $editor['name']  = 'text';

        $window->form->items = [
            [
                'xtype' => 'hidden',
                'name'  => 'fileId',
                'value' => $this->fileId
            ],
            $editor
        ];
        
        // добавление в ответ скриптов 
        if ($viewer) {
            if (method_exists($viewer, 'initResponse')) {
                $viewer->initResponse($this->getResponse());
            }
        }
        return $window;
    }

    /**
     * Действие "perfom" выполняет сохранение в файл.
     * 
     * @return Response
     */
    public function perfomAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

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
