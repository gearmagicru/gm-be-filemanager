<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Model;

use Gm;
use Gm\Mvc\Module\BaseModule;
use Gm\Uploader\UploadedFile;
use Gm\Panel\Data\Model\FormModel;

/**
 * Модель загрузки файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class UploadForm extends FormModel
{
    /**
     * @var string Событие, возникшее после загрузки файла.
     */
    public const EVENT_AFTER_UPLOAD = 'afterUpload';

    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_UPLOAD, function ($result, $message) {
                /** @var \Gm\Panel\Http\Response\JsongMetadata $meta */
                $meta = $this->response()->meta;
                // всплывающие сообщение
                $meta->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                // если права доступа установлены для файла / папки
                if ($result) {
                    // обновляем список файлов
                    $meta->cmdComponent($this->module->viewId('view'), 'reload');
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'path' => 'path',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'path' => $this->module->t('Path'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['path'], 'notEmpty']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка пути загрузки файла
            if ($this->path === false || !is_dir($this->path)) {
                $this->setError(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
                return false;
            }

            /** @var false|UploadedFile $file */
            $file = $this->getUploadedFile('uploadFile');
            // проверка загрузки файла
            if ($file === false) {
                $this->setError('No file selected for upload');
                return false;
            }
            // если была ошибки загрузки
            if (!$file->hasUpload()) {
                $this->setError(Gm::t('app', $file->getErrorMessage()));
                return false;
            }
        }
        return $isValid;
    }

    /**
     * Устанавливает значение атрибуту "path".
     * 
     * @param null|string $value Идентификатор папки.
     * 
     * @return void
     */
    public function setPath($value)
    {
        $this->attributes['path'] = $this->module->getSafePath($value) ?: '';
    }

    /**
     * @see UploadForm::getUploadedFile()
     * 
     * @var UploadedFile|false
     */
    private UploadedFile|false $uploadedFile;

    /**
     * Возвращает загруженный файл.
     * 
     * @return UploadedFile|false Возвращает значение `false` если была ошибка загрузки.
     */
    public function getUploadedFile()
    {
        if (isset($this->uploadedFile)) return $this->uploadedFile;

        /** @var \Gm\Uploader\Uploader $uploader */
        $uploader = Gm::$app->uploader;
        $uploader->setPath($this->path);

        /** @var \Gm\Uploader\UploadedFile $uploadedFile */
        $uploadedFile = $uploader->getFile('uploadFile') ?: false;
        return $this->uploadedFile = $uploadedFile;
    }

    /**
     * Выполняет загрузку файла.
     * 
     * @param bool $useValidation Использовать проверку атрибутов (по умолчанию `false`).
     * @param array $attributes Имена атрибутов с их значениями, если не указаны - будут 
     * задействованы атрибуты записи (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если ошибка загрузки файла.
     */
    public function upload(bool $useValidation = false, array $attributes = null)
    {
        if ($useValidation && !$this->validate($attributes)) {
            return false;
        }
        return $this->uploadProcess($attributes);
    }

    /**
     * Процесс подготовки загрузки файла.
     * 
     * @param null|array $attributes Имена атрибутов с их значениями (по умолчанию `null`).
     * 
     * @return bool Возвращает значение `false`, если ошибка загрузки файла.
     */
    protected function uploadProcess(array $attributes = null): bool
    {
        /** @var UploadedFile $file */
        $file = $this->getUploadedFile();
        $this->result = $file->move();
        // если файл не загружен
        if (!$this->result) {
            $this->setError(Gm::t('app', $file->getErrorMessage()));
        }

        $this->afterUpload($this->result);
        return $this->result;
    }

    /**
     * Cобытие вызывается после загрузки файла.
     * 
     * @see UploadForm::upload()
     * 
     * @param bool $result Если значение `true`, файл успешно загружен.
     * 
     * @return void
     */
    public function afterUpload(bool $result = false)
    {
        /** @var bool|int $result */
        $this->trigger(
            self::EVENT_AFTER_UPLOAD,
            [
                'result'  => $result,
                'message' => $this->lastEventMessage = $this->uploadMessage($result)
            ]
        );
    }

    /**
     * Возвращает сообщение полученное при загрузке файла.
     *
     * @param bool $result Если значение `true`, файл успешно загружен.
     * 
     * @return array Сообщение имеет вид:
     * ```php
     *     [
     *         'success' => true,
     *         'message' => 'File uploaded successfully',
     *         'title'   => 'Uploading a file',
     *         'type'    => 'accept'
     *     ]
     * ```
     */
    public function uploadMessage(bool $result): array
    {
        $messages = $this->getActionMessages();
        return [
            'success'  => $result, // успех загрузки
            'message'  => $messages[$result ? 'msgSuccessUpload' : 'msgUnsuccessUpload'], // сообщение
            'title'    => $messages['titleUpload'], // заголовок сообщения
            'type'     => $result ? 'accept' : 'error' // тип сообщения
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionMessages(): array
    {
        return [
            'titleUpload'        => $this->module->t('Uploading a file'),
            'msgUnsuccessUpload' => $this->getError(),
            'msgSuccessUpload'   => $this->module->t('File uploaded successfully')
        ];
    }

    /**
     * Возвращает сообщение, подтверждающие загрузку файла.
     * 
     * @param array $fields Поля, переданные в HTTP-запросе.
     * @param string $confirm Тип подтверждения.
     * 
     * @return string|null Возвращает значение `null`, если нет необходимости подтверждать.
     */
    public function getConfirmMessage(array $fields, string $confirm): ?string
    {
        if ($confirm === 'upload') {
            $pathId = $fields['path'] ?? '';
            // если не указан идентификатор папки
            if (empty($pathId)) {
                $this->setError(Gm::t('app', 'Parameter "{0}" not specified', ['path']));
                return null;
            }

            $filename = $fields['uploadFile'] ?? '';
            // если не указано название файла
            if (empty($filename)) {
                $this->setError(Gm::t('app', 'Parameter "{0}" not specified', ['uploadFile']));
                return null;
            }

            $basename = basename($filename);
            $filename = $this->module->getSafePath($pathId) . DS . $basename;
            if (file_exists($filename)) {
                return $this->module->t('The downloaded file "{0}" is already on the server, should I replace it?', [$basename]);
            }
            return null;
        }
        return null;
    }
}
