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
use Gm\Helper\Json;
use Gm\Panel\Widget\Form;
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Helper\ExtCombo;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер формы архивирования файлов / папок.
 * 
 * Маршруты контроллера:
 * - 'compress', 'compress/view', выводит интерфейс архивирования файлов / папок;
 * - 'compress/perfom', выполняет архивирование файлов / папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class CompressForm extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'CompressForm';

    /**
     * Имя создаваемого архива.
     * 
     * @var string
     */
    protected string $archiveName = '';

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
                    case 'view':
                        $this->pathId = Gm::$app->request->getPost('path', '');
                        if ($this->pathId) {
                            /** @var \Gm\Backend\FileManager\Model\FolderProperties $folder */
                            $folder = $this->getModel('FolderProperties', ['id' => $this->pathId]);
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

                        /** @var string|null $files Выбранные идентификаторы файлов / папок */
                        $files = Gm::$app->request->getPost('files', '');
                        if (empty($files)) {
                            $this->getResponse()
                                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['files']));
                            $result = false;
                            return;
                        }

                        /** @var array|false $files Выбранные идентификаторы файлов / папок */
                        $files = Json::tryDecode($files);
                        if ($error = Json::error()) {
                            $this->getResponse()
                                ->meta->error($error);
                            $result = false;
                            return;
                        }

                        /** @var \Gm\Backend\FileManager\Model\CompressForm|null $model */
                        $model = $this->getModel($this->defaultModel);
                        if ($model === null) {
                            $this->getResponse()
                                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"',[$this->defaultModel]));
                            $result = false;
                            return;
                        }

                        // подготовка файлов
                        if (!$model->prepare($files)) {
                            $this->getResponse()
                                ->meta->error($model->getError());
                            $result = false;
                            return;
                        }

                        $this->archiveName = $model->makeArchiveName($files, $this->pathId);
                        break;
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var \Gm\Backend\FileManager\Model\CompressForm|null $model */
        $model = $this->getModel($this->defaultModel);

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 400;
        $window->autoHeight = true;
        $window->resizable = false;
        $window->title = $this->t('{compress.title}');
        $window->titleTpl = $window->title;
        $window->iconCls  = 'g-icon-svg gm-filemanager__icon-compress';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->bodyPadding = 10;
        $window->form->defaults = [
            'labelWidth' => 110,
            'labelAlign' => 'right',
            'width'      => '100%',
            'allowBlank' => false
        ];
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/compress'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'perfom' => '{route}/perfom'
            ]
        ]);
        $window->form->setStateButtons(
            Form::STATE_CUSTOM,
            ExtForm::buttons([
                'help' => ['subject' => 'compress'], 
                'submit' => [
                    'text'        => '#Compress', 
                    'iconCls'     => 'g-icon-svg g-icon_size_14 gm-filemanager__icon-compress', 
                    'handler'     => 'onFormSubmit',
                    'handlerArgs' => [
                        'routeRule' => 'perfom'
                    ]
                ],
                'cancel'
            ])
        );
        $window->form->items = [
            [
                'xtype' => 'hidden',
                'name'  => 'path',
                'value' => $this->pathId
            ],
            ExtCombo::local(
                '#Archive type', 
                'format', 
                [
                    'fields' => ['id', 'name'],
                    'data'   => $model->getArchiveFormats()
                ],
                [
                    'allowBlank' => false
                ]
            ),
            [
                'xtype'      => 'textfield',
                'fieldLabel' => '#Archive name',
                'name'       => 'name',
                'value'      => $this->archiveName,
                'maxLength'  => 50
            ]
        ];
        return $window;
    }

    /**
     * Действие "perfom" выполняет архивирование файлов / папок.
     * 
     * @return Response
     */
    public function perfomAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var \Gm\Backend\FileManager\Model\CompressForm $model */
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
