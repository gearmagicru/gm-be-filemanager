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
 * Модель данных формы редактирования файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class EditForm extends FileModel
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
            });
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'fileId' => 'fileId', // идентификатор файла
            'text'   => 'text' // содержимое
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'fileId' => $this->module->t('File / folder name'),
            'text'   => $this->module->t('File content')
        ];
    }

    /**
     * Устанавливает значение атрибуту "fileId".
     * 
     * @param null|string $value Идентификатор файла.
     * 
     * @return void
     */
    public function setFileId($value): void
    {
        $this->attributes['fileId'] = $this->module->getSafePath($value) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['fileId'], 'notEmpty']
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionMessages(): array
    {
        return [
            'titleRun'        => $this->module->t('Saving a file'),
            'msgSuccessRun'   => $this->module->t('The file has been successfully modified'),
            'msgUnsuccessRun' => $this->module->t('Error writing to file')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка файла
            if (!Fs::exists($this->fileId)) {
                $this->setError(
                    $this->module->t('The specified file "{0}" does not exist', [$this->fileId])
                );
                return false;
            }

            // проверка корректности выбора файла
            $isFile = Fs::isFile($this->fileId);
            if (!$isFile) {
                $this->setError(
                    $this->module->t('The specified file "{0}" does not exist', [$this->fileId])
                );
                return false;
            }
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function runFile(array $attributes): bool
    {
        return Fs::put($this->fileId, $this->text) !== false;
    }
}
