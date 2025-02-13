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
use Gm\Panel\Http\Response;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Controller\GridController;

/**
 * Контроллер сетки / списка файлов.
 * 
 * Маршруты контроллера:
 * - 'files/paste', вставляет из буфера обмена файлы / папки.;
 * - 'files/delete', удаляет выбранные файлы / папки;
 * - 'files/data', создаёт список файлов / папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class Files extends GridController
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
    protected string $defaultModel = 'Files';

    /**
     * Действие "paste" вставляет из буфера обмена файлы / папки.
     *
     * @return Response
     */
    public function pasteAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var string|null $pathId Идентификатор папки в которую выполнятся вставка */
        $pathId = $request->getPost('path');
        if (empty($pathId)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
            return $response;
        }

        /** @var string $path Папка (путь) в которую выполнятся вставка */
        $path = $this->module->getSafePath($pathId);
        if (!file_exists($path)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
            return $response;
        }

        /** @var string|null $action Действие на файлами: 'cut', 'copy' */
        $action = $request->getPost('action');
        if (empty($action)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['action']));
            return $response;
        }

        /** @var string|null $files Идентификаторы файлов / папок в JSON-формате */
        $files = $request->getPost('files');
        if (empty($files)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['files']));
            return $response;
        }

        /** @var array|false $files  */
        $files = Json::tryDecode($files);
        if ($error = Json::error()) {
            $response
                ->meta->error($error);
            return $response;
        }

        /** @var \Gm\Backend\FileManager\Model\Clipboard $clipboard */
        $clipboard = $this->getModel('Clipboard', ['action' => $action, 'path' => $path]);
        if ($clipboard === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', ['Clipboard']));
            return $response;
        }

        if (!$clipboard->hasAction()) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['action']));
            return $response;
        }

        // копирование / вырезка файлов
        if (!$clipboard->paste($files)) {
            $response
                ->meta->error($clipboard->getError());
        } else {
            // copy
            if ($clipboard->isCopy())
                $message = $this->module->t('Successfully copied "{0}" files / folders', [$clipboard->getCount()]);
            // cut
            else
                $message = $this->module->t('Successfully cut "{0}" files / folders', [$clipboard->getCount()]);
            $response
                ->meta->success($message);
        }
        return $response;
    }

    /**
     * Действие "delete" удаляет выбранные файлы и папки.
     *
     * @return Response
     */
    public function deleteAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var \Gm\Backend\FileManager\Model\Files $model */
        $model = $this->getModel($this->defaultModel);
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // проверка идентификаторов в запросе
        if (empty($model->rowsId)) {
            $response
                ->meta->error($this->module->t('You must select a file or folder'));
            return $response;
        }

        // удаление файлов / папок
        if ($model->delete() === false) {
            // если не было сообщения об ошибке ранее
            if (!$response->meta->isError()) {
                $response
                    ->meta->error($this->module->t('Cannot delete selected files or folders'));
                return $response;
            }
        }
        return $response;
    }
}
