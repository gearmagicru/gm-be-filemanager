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
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Helper\ExtCombo;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер формы разархивирования файлов / папок.
 * 
 * Маршруты контроллера:
 * - 'extract', 'extract/view', 'extract', выводит интерфейс разархивирования файлов / папок;
 * - 'extract/perfom', выполняет разархивирование файлов / папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class ExtractForm extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'ExtractForm';

    /**
     * Идентификатор выбранной папки.
     * 
     * @var string
     */
    protected string $pathId = '';

    /**
     * Идентификатор выбранного файла архива.
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
                    case 'view':
                        // идентификатор выбранной папки
                        $this->pathId = Gm::$app->request->getPost('path', '');
                        if ($this->pathId) {
                            /** @var \Gm\Backend\FileManager\Model\FolderProperties $folder */
                            $folder = $this->getModel('FolderProperties', ['id' => $this->pathId]);
                            if (!$folder->exists()) {
                                $this->getResponse()
                                    ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
                                $result = false;
                                return;
                            }
                        } else {
                            $this->getResponse()
                                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
                            $result = false;
                            return;
                        }

                        // идентификатор выбранного файла архива
                        $this->fileId = Gm::$app->request->getPost('id', '');
                        if ($this->fileId) {
                            /** @var \Gm\Backend\FileManager\Model\FileProperties $file */
                            $file = $this->getModel('FileProperties', ['id' => $this->fileId]);
                            if (!$file->exists()) {
                                $this->getResponse()
                                    ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['file']));
                                $result = false;
                                return;
                            }
                            if (!$file->isArchive()) {
                                $this->getResponse()
                                    ->meta->error($this->module->t('The specified file is not an archive'));
                                $result = false;
                                return;
                            }
                        } else {
                            $this->getResponse()
                                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['file']));
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
        $window->width = 450;
        $window->autoHeight = true;
        $window->resizable = false;
        $window->title = $this->t('{extract.title}', [basename($this->fileId)]);
        $window->titleTpl = $window->title;
        $window->iconCls  = 'g-icon-svg gm-filemanager__icon-extract';

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->makeViewID(); // для того, чтобы сразу использовать `$window->form->id`
        $window->form->controller = 'gm-be-filemanager-extract';
        $window->form->router->setAll([
            'route' => Gm::alias('@match', '/extract'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'perfom' => '{route}/perfom'
            ]
        ]);
        $window->form->bodyPadding = 10;
        $window->form->defaults = [
            'labelWidth' => 90,
            'labelAlign' => 'right',
            'width'      => '100%'
        ];
        $window->form->setStateButtons(
            Form::STATE_CUSTOM,
            ExtForm::buttons([
                'help' => ['subject' => 'extract'], 
                'submit' => [
                    'text'        => '#Extract', 
                    'iconCls'     => 'g-icon-svg g-icon_size_14 gm-filemanager__icon-extract', 
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
            [
                'xtype' => 'hidden',
                'name'  => 'file',
                'value' => $this->fileId
            ],
            ExtCombo::local(
                '#Where', 
                'where', 
                [
                    'fields' => ['id', 'name'],
                    'data'   => [
                        ['separate', '#To a separate folder'],
                        ['current', '#To current folder']
                    ]
                ],
                [
                    'value'      => 'separate',
                    'allowBlank' => false,
                    'listeners'  => [
                        'select' => 'onSelectWhere'
                    ]
                ]
            ),
            [
                'id'         => $window->form->id . '__folder',
                'xtype'      => 'textfield',
                'fieldLabel' => '#Folder name',
                'name'       => 'folderName',
                'value'      => pathinfo($this->fileId, PATHINFO_FILENAME),
                'maxLength'  => 50,
                'allowBlank' => true
            ],
            [
                'ui'         => 'switch',
                'xtype'      => 'checkbox',
                'inputValue' => 1,
                'padding'    => '0 0 0 95px',
                'name'       => 'deleteAfter',
                'boxLabel'   => '#Delete archive after extraction',
            ]
        ];
        $window
            ->setNamespaceJS('Gm.be.filemanager')
            ->addRequire('Gm.be.filemanager.ExtractController');
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
