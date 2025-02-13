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
use Gm\Backend\FileManager\Widget;
use Gm\Panel\Controller\FormController;
use Gm\Panel\Widget\Widget as PanelWidget;
use Gm\Backend\FileManager\Model\FileProperties;

/**
 * Контроллер формы предварительного просмотра файла.
 * 
 * Маршруты контроллера:
 * - 'preview', 'preview/view', выводит интерфейс окна предварительного просмотра файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class PreviewForm extends FormController
{
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
                    case 'view': 
                        $this->fileId = Gm::$app->request->getPost('id', '');
                        if ($this->fileId) {
                            /** @var FileProperties $model */
                            $model = $this->getModel();
                            if (!$model->exists(true)) {
                                $this->getResponse()
                                    ->meta->error(
                                        $this->module->t('The selected file "{0}" cannot be viewed', [$this->fileId])
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
     * 
     * @return mixed
     */
    public function createWidget(): PanelWidget|false
    {
        /** @var FileProperties $model */
        $model = $this->getModel();

        // если изображение
        if ($model->isImage()) {
            $widget = new Widget\ImagePreview();
            $content = $model->getUrl();
        } else
        // если скрипт / текст
        if ($model->isScript() || $model->isText()) {
            $widget = new Widget\ScriptPreview();
            $widget->setExtension($model->getExtension());
            $content = $model->getContent();
        } else {
            $content = false;
        }

        if ($content === false) {
            $this->getResponse()
                ->meta->error($this->module->t('The selected file "{0}" cannot be viewed', [$this->fileId]));
            return false;
        }

        /** @var null|object|\Gm\Stdlib\BaseObject $viewer */
        $viewer = $widget->getViewer();
        // добавление в ответ скриптов 
        if ($viewer) {
            if (method_exists($viewer, 'initResponse')) {
                $viewer->initResponse($this->getResponse());
            }
        }

        $widget
            ->setFileId($this->fileId)
            ->setTitle($model->getFilename())
            ->setContent($content);
        return $widget;
    }
}
