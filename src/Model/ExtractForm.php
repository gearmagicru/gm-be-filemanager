<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Model;

use Gm;
use Gm\Mvc\Module\BaseModule;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Модель данных разархивирования файлов / папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class ExtractForm extends FileModel
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
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_RUN, function (array $attributes, bool $result, array $message) {
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
            'file'        => 'file', // название архива
            'where'       => 'where', // куда извлечь
            'folderName'  => 'folderName', // 
            'deleteAfter' => 'deleteAfter', // удалить архив после извлечения
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'file'        => $this->module->t('Archive name'),
            'where'       => $this->module->t('Where'),
            'folderName'  => $this->module->t('Folder name'),
            'deleteAfter' => $this->module->t('Delete archive after extraction'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatterRules(): array
    {
        return [
            [['deleteAfter'], 'logic' => [true, false]]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['file', 'where'], 'notEmpty'],
            // куда
            [
                'where', 'enum', 'enum' => ['separate', 'current']
            ],
            // название архива
            [
                'name',
                'between',
                'max' => 50, 'type' => 'string'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionMessages(): array
    {
        return [
            'titleRun'        => $this->module->t('Extraction'),
            'msgSuccessRun'   => $this->module->t('Files extracted from archive successfully'),
            'msgUnsuccessRun' => $this->module->t('Error extracting files from archive')
        ];
    }

    /**
     * Устанавливает значение атрибуту "file".
     * 
     * @param null|string $value Идентификатор файла архива.
     * 
     * @return void
     */
    public function setFile($value): void
    {
        $this->attributes['file'] = $this->module->getSafePath($value) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка файла архива
            if (!Fs::exists($this->file)) {
                $this->setError($this->module->t('The specified file "{0}" does not exist', [$this->file]));
                return false;
            }

            // проверка имени папки
            if ($this->where === 'separate' && empty($this->folderName)) {
                $this->setError(
                    $this->errorFormat(
                        [Gm::t('app', 'Value is required and can\'t be empty')], 
                        $this->module->t('Folder name')
                    )
                );
                return false;
            }
        }
        return $isValid;
    }

    /**
     * Создаёт имя папки для извлечения файлов архива.
     *
     * @param string $fileId Идентификатор файла архива.
     * 
     * @return string
     */
    public function makeFolderName(string $fileId): string
    {
        return $fileId ? basename($fileId) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function runFile(array $attributes): bool
    {
        $path = pathinfo($this->file, PATHINFO_DIRNAME);
        // если извлечь в отдельную папку
        if ($this->where === 'separate') {
            $path = $path . DS . $this->folderName;
            if (!Fs::exists($path)) {
                if (!Fs::makeDirectory($path)) {
                    $this->setError($this->module->t('Cannot create directory "{0}"', [$path]));
                    return false;
                }
            }
        }

        /** @var Archive $archive */
        $archive = new Archive(['filename' => $this->file]);
        $extracted = $archive->extract($path);
        if ($extracted) {
            // удалить архив после удаления
            if ($this->deleteAfter) {
                if (!Fs::deleteFile($this->file)) {
                    $this->setError(Gm::t('app', 'Could not perform file deletion "{0}"', [$this->file]));
                }
            }
        }
        return $extracted;
    }
}
