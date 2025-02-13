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
 * Модель данных формы изменения названия файла / папки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class RenameForm extends FileModel
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * Идентификатор файла/папки которую переименовывают.
     * 
     * @see Create::setOldName()
     * 
     * @var string
     */
    protected string $folderId = '';

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
                // если добавлена папка / файл
                if ($result) {
                    // обновляем список файлов
                    $meta->cmdComponent($this->module->viewId('view'), 'reload');
                }
                // если добавлена папка
                if ($this->isFolder()) {
                    /** @var Gm\Config\Config $settings */
                    $settings = $this->module->getSettings();
                    // если панель папок отображается
                    if ($settings->showTree) {
                        // изменяем название папки
                        $meta->cmdCallControllerMethod(
                            $this->module->viewId('tree'), 
                            'rename',
                            [pathinfo($this->newName, PATHINFO_BASENAME), $this->folderId]
                        );
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'oldName' => 'oldName', // старое название
            'newName' => 'newName', // новое название
            'type'    => 'type', // тип: 'file', 'folder'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'newName' => $this->module->t('New name'),
            'type'    => $this->module->t('Type')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['oldName', 'newName', 'type'], 'notEmpty'],
            // новое название
            [
                'newName',
                'between',
                'min' => 1, 'max' => 255, 'type' => 'string'
            ],
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
        if ($this->type === self::TYPE_FOLDER)
            return [
                'titleRun'        => $this->module->t('Renaming'),
                'msgSuccessRun'   => $this->module->t('The folder was successfully renamed'),
                'msgUnsuccessRun' => $this->module->t('Error renaming folder')
            ];
        else
        if ($this->type === self::TYPE_FILE)
            return [
                'titleRun'        => $this->module->t('Renaming'),
                'msgSuccessRun'   => $this->module->t('The file was successfully renamed'),
                'msgUnsuccessRun' => $this->module->t('Error creating file')
            ];
        else
        return parent::getActionMessages();
    }

    /**
     * Устанавливает значение атрибуту "oldName".
     * 
     * @param null|string $value Идентификатор папки.
     * 
     * @return void
     */
    public function setOldName($value)
    {
        $this->attributes['oldName'] = $this->module->getSafePath($value) ?: '';
        // для файла не играет никакой роли
        $this->folderId = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка предыдущего файла / папки
            if (!Fs::exists($this->oldName)) {
                if ($this->isFolder())
                    $message = 'The specified folder "{0}" does not exist';
                else
                    $message = 'The specified file "{0}" does not exist';
                $this->setError($this->module->t($message, [$this->oldName]));
                return false;
            }

            // проверка корректности выбора файла / папки
            $isFile = Fs::isFile($this->oldName);
            if ($this->isFolder()) {
                if ($isFile) {
                    // папке не существует, т.к. она файл
                    $this->setError(
                        $this->module->t('The specified folder "{0}" does not exist', [$this->oldName])
                    );
                    return false;
                }
            } else {
                if (!$isFile) {
                    // файл не существует, т.к. он папка
                    $this->setError(
                        $this->module->t('The specified file "{0}" does not exist', [$this->oldName])
                    );
                    return false;
                }
            }

            // проверка расширения файла
            if ($isFile) {
                $extension = pathinfo($this->newName, PATHINFO_EXTENSION);
                if (empty($extension)) {
                    $this->setError(
                        $this->module->t('The extension of the new file is incorrect')
                    );
                    return false;
                }
            }

            // проверка нового имени файла / папки
            if (!preg_match('/^([-\.\w]+)$/', $this->newName)) {
                $this->setError(
                    $this->module->t(
                        $isFile ? 'The new file name is incorrect' : 'The new folder name is incorrect'
                    )
                );
                return false;
            }

            // проверка существования нового имени файла / папки
            $newName = pathinfo($this->oldName, PATHINFO_DIRNAME) . DS . $this->newName;
            if (Fs::exists($newName)) {
                $this->setError(
                    $this->module->t(
                        $isFile ? 'A file with the new name "{0}" already exists' 
                                : 'A folder with the new name "{0}" already exists', 
                        [$this->newName]
                    )
                );
                return false;
            }
            $this->newName = $newName;
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
        return @rename($this->oldName, $this->newName);
    }
}
