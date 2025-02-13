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
 * Модель данных формы установки права доступа файлу / папке.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class PermissionsForm extends FileModel
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
            'fileId'      => 'fileId', // идентификатор файла / папки
            'type'        => 'type', // тип: 'file', 'folder'
            'permissions' => 'permissions' // права доступа
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'fileId'      => $this->module->t('File / folder name'),
            'type'        => $this->module->t('Type'),
            'permissions' => $this->module->t('Permissions')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['type', 'fileId', 'permissions'], 'notEmpty'],
            // тип
            [
                'type', 'enum', 'enum' => [self::TYPE_FILE, self::TYPE_FOLDER]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionMessages(): array
    {
        return [
            'titleRun'        => $this->module->t('Permissions'),
            'msgSuccessRun'   => $this->module->t('Permissions have been successfully set')
                               . '<br>' 
                               . (OS_WINDOWS ?
                               $this->module->t('For OS Windows, the permissions set do not matter') : 
                               ''),
            'msgUnsuccessRun' => $this->module->t('Error setting permissions')
        ];
    }

    /**
     * Устанавливает значение атрибуту "fileId".
     * 
     * @param null|string $value Идентификатор файла / папки.
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
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка файла / папки
            if (!Fs::exists($this->fileId)) {
                if ($this->isFolder())
                    $message = 'The specified folder "{0}" does not exist';
                else
                    $message = 'The specified file "{0}" does not exist';
                $this->setError($this->module->t($message, [$this->fileId]));
                return false;
            }

            // проверка корректности выбора файла / папки
            $isFile = Fs::isFile($this->fileId);
            if ($this->isFolder()) {
                if ($isFile) {
                    // папке не существует, т.к. она файл
                    $this->setError(
                        $this->module->t('The specified folder "{0}" does not exist', [$this->fileId])
                    );
                    return false;
                }
            } else {
                if (!$isFile) {
                    // файл не существует, т.к. он папка
                    $this->setError(
                        $this->module->t('The specified file "{0}" does not exist', [$this->fileId])
                    );
                    return false;
                }
            }
        }
        return $isValid;
    }

    /**
     * Проверяет, является ли текущий запрос для папки.
     * 
     * @return bool
     */
    protected function isFolder(): bool
    {
        return $this->type === self::TYPE_FOLDER;
    }

    /**
     * {@inheritdoc}
     */
    public function runFile(array $attributes): bool
    {
        Fs::$throwException = false;
        return Fs::chmod($this->fileId, $this->permissions);
    }
}
