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
use Gm\Exception;
use Gm\Helper\Json;
use Gm\Panel\Http\Response;
use Gm\Panel\Controller\BaseController;

/**
 * Контроллер скачивания файла.
 * 
 * Маршруты контроллера:
 * - 'download', скачивает файл;
 * - 'download/prepare', подготавливает файл для скачивания.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class Download extends BaseController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'Download';

    /**
     * {@inheritdoc}
     */
    public bool $enableCsrfValidation = true;

    /**
     * {@inheritdoc}
     */
    protected string $defaultAction = 'index';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verb' => [
                'class'    => '\Gm\Filter\VerbFilter',
                'autoInit' => true,
                'actions'  => [
                    '*'    => ['POST', 'ajax' => 'GJAX'],
                    'file' => ['GET']
                ]
            ],
            'audit' => [
                'class'    => '\Gm\Panel\Behavior\AuditBehavior',
                'autoInit' => true,
                'allowed'  => '*',
                'enabled'  => $this->enableAudit
            ]
        ];
    }

    /**
     * Действие "index" подготовавливает указанные файлы к скачиванию.
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var string|null $files */
        $files = $request->getPost('files');
        if (empty($files)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['files']));
            return $response;
        }

        /** @var \Gm\Backend\FileManager\Model\Download|null $model */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"',[$this->defaultModel]));
            return $response;
        }

        /** @var array|false $files  */
        $files = Json::tryDecode($files);
        if ($error = Json::error()) {
            $response
                ->meta->error($error);
            return $response;
        }

        // подготовка файлов
        if (!$model->prepare($files)) {
            $response
                ->meta->error($model->getError());
            return $response;
        }

        return $response->setContent($model->getFileId(false));
    }
 
    /**
     * Действие "file" выводит содержимое файла по указанному идентфикатору.
     * 
     * @return Response
     * 
     * @throws Exception\PageNotFoundException Если ошибки определения файла.
     */
    public function fileAction(): Response
    {
        /** @var string|null Идентификатор подготовленного файла */
        $fileId = Gm::$app->router->get('id');

        if (empty($fileId)) {
            Gm::debug('Error', ['error' => 'File ID is empty.']);
            throw new Exception\PageNotFoundException();
        }

        /** @var \Gm\Backend\FileManager\Model\Download|null $model */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            Gm::debug('Error', ['error' => 'Model "Download" not found.']);
            throw new Exception\PageNotFoundException();
        }

        /** @var string|null Название файла */
        $filename = $model->getFilename($fileId);
        if ($filename === null) {
            Gm::debug('Error', ['error' => 'File not found by ID "' . $fileId . '".']);
            throw new Exception\PageNotFoundException();
        }

        /** @var Response $response */
        $response = $this->getResponse(Response::FORMAT_RAW);
        $response->sendFile($filename);

        // сбрасываем параметры для скачивания и удаляем временный файл
        $model->reset();
        return $response;
    }
}
