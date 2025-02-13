<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Controller;

use Gm;
use Gm\Helper\Json;
use Gm\Helper\Html;
use Gm\Panel\Widget\Form;
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер формы загрузки файла.
 * 
 * Маршруты контроллера:
 * - 'upload', 'upload/view', выводит интерфейс окна загрузки файла;
 * - 'upload/perfom', выполняет загрузку файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class UploadForm extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'UploadForm';

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
                        $pathId = Gm::$app->request->getPost('path');
                        if ($pathId) {
                            /** @var \Gm\Backend\FileManager\Model\FolderProperties $folder */
                            $folder = $this->getModel('FolderProperties', ['id' => $pathId]);
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
    public function createWidget(): EditWindow
    {
        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->title = '#{upload.title}';
        $window->titleTpl = $window->title;
        $window->width = 470;
        $window->autoHeight = true;
        $window->layout = 'fit';
        $window->resizable = false;
        $window->iconCls = 'g-icon-m_upload';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->autoScroll = true;
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/upload'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'submit' => '{route}/perfom'
            ] 
        ]);
        $window->form->setStateButtons(
            Form::STATE_CUSTOM,
            ExtForm::buttons([
                'help' => ['subject' => 'upload'], 
                'submit' => [
                    'text'        => '#Upload', 
                    'iconCls'     => 'g-icon-svg g-icon_size_14 g-icon-m_upload', 
                    'handler'     => 'onFormAction',
                    'handlerArgs' => [
                        'routeRule' => 'submit',
                        'confirm'   => 'upload'
                    ]
                ],
                'cancel'
            ])
        );
        $window->form->items = [
            [
                'xtype'    => 'container',
                'padding'  => 7,
                'defaults' => [
                    'labelAlign' => 'right',
                    'labelWidth' => 110,
                    'width'      => '100%',
                    'allowBlank' => false
                ],
                'items' => [
                    // т.к. параметры ("_csrf", "X-Gjax") не передаются через заголовок, 
                    // то передаём их через метод POST
                    [
                        'xtype' => 'hidden',
                        'name'  => 'X-Gjax',
                        'value' => true
                    ],
                    [
                        'xtype' => 'hidden',
                        'name'  => Gm::$app->request->csrfParamName,
                        'value' => Gm::$app->request->getCsrfTokenFromHeader()
                    ],
                    [
                        'xtype' => 'hidden',
                        'name'  => 'path',
                        'value' => Gm::$app->request->getPost('path')
                    ],
                    [
                        'xtype'      => 'filefield',
                        'name'       => 'uploadFile',
                        'fieldLabel' => '#File name'
                    ]
                ]
            ],
            [
                'xtype' => 'label',
                'ui'    => 'note',
                'html'  => 
                    $this->module->t(
                        'The file(s) will be downloaded according to the parameters for downloading resources to the server {0}', 
                        [
                            Html::a(
                                $this->module->t('(more details)'), 
                                '#', 
                                [
                                    'onclick' => ExtForm::jsAppWidgetLoad('@backend/config/upload')
                                ]
                            )
                        ]
                    )
            ]
        ];
        return $window;
    }

    /**
     * Действие "perfom" выполняет загрузку файла или подтверждает запрос.
     * 
     * @return Response
     */
    public function perfomAction(): Response
    {
        /** @var \Gm\Panel\Http\Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request  = Gm::$app->request;

        /** @var \Gm\Backend\FileManager\\Model\UploadForm $form */
        $form = $this->getModel($this->defaultModel);
        if ($form === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        /** @var null|string $confirm Если есть запрос на подтверждение */
        $confirm = $request->getPost('confirm');
        if ($confirm) {
            $fields = $request->getPost('fields', []);
            if ($fields) {
                $fields = Json::tryDecode($fields);
                // если нет ошибки в полях переданных на подтверждение
                if ($fields === false) {
                    $response
                        ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['files']));
                    return $response;
                }
            }
            /** @var null|string $message Сообщение диалога подтверждения */
            $message = $form->getConfirmMessage($fields, $confirm);
            if ($message !== null) {
                $response
                    ->meta->confirm = ['message' => $message];
                return $response;
            }
            if ($form->hasErrors()) {
                $response
                    ->meta->error($form->getError());
                return $response;
            }
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->module->id . ':onFormAction', [$this->module, $form, 'upload']);
        }

        // загрузка атрибутов в модель из запроса
        if (!$form->load($request->getPost())) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        // валидация атрибутов модели
        if (!$form->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$form->getError()]));
            return $response;
        }

        // загрузка файла
        if (!$form->upload()) {
            $response
                ->meta->error(
                    $form->hasErrors() ? $form->getError() : $this->module->t('File uploading error')
                );
            return $response;
        }

        if ($this->useAppEvents) {
            Gm::$app->doEvent($this->module->id . ':onAfterFormAction', [$this->module, $form, 'upload']);
        }
        return $response;
    }
}
