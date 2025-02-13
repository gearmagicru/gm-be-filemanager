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
 * Модель данных формы создания файла / папки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class CreateForm extends FileModel
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * Идентификатор папки в которую добавляют.
     * 
     * @see Create::setPath()
     * 
     * @var string
     */
    protected string $pathId = '';

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
                if ($this->type === self::TYPE_FOLDER) {
                    /** @var Gm\Config\Config $settings */
                    $settings = $this->module->getSettings();
                    // если панель папок отображается
                    if ($settings->showTree) {
                        // обновляем только папку в которую добавили
                        $meta->cmdCallControllerMethod(
                            $this->module->viewId('tree'), 
                            'add',
                            [$attributes['newName'], $this->pathId]
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
            'name' => 'newName', // название (т.к. 'name' - название модели, то 'newName')
            'type' => 'type', // тип: 'file', 'folder'
            'path' => 'path', // базовый путь
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => $this->module->t('folder name'),
            'type' => $this->module->t('Type'),
            'path' => $this->module->t('Path'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['name', 'type', 'path'], 'notEmpty'],
            // название
            [
                'name',
                'between',
                'min' => 2, 'max' => 255, 'type' => 'string'
            ],
            [
                'name', 'filename'
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
                'titleRun'        => $this->module->t('Creation'),
                'msgSuccessRun'   => $this->module->t('Folder created successfully'),
                'msgUnsuccessRun' => $this->module->t('Error creating folder')
            ];
        else
        if ($this->type === self::TYPE_FILE)
            return [
                'titleRun'        => $this->module->t('Creation'),
                'msgSuccessRun'   => $this->module->t('File created successfully'),
                'msgUnsuccessRun' => $this->module->t('Error creating file')
            ];
        else
        return parent::getActionMessages();
    }

    /**
     * Устанавливает значение атрибуту "path".
     * 
     * @param null|string $value Идентификатор папки.
     * 
     * @return void
     */
    public function setPath($value): void
    {
        $this->attributes['path'] = $this->module->getSafePath($value) ?: '';
        $this->pathId = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка базового пути
            if (!Fs::exists($this->path)) {
                $this->setError($this->module->t('The specified folder "{0}" does not exist', [$this->path]));
                return false;
            }
        }
        return $isValid;
    }

    /**
     *  Создаёт папку с указанным именем.
     * 
     * @param string $folder Имя папки.
     * 
     * @return bool Возвращает значение `false`, если невозможно создать папку.
     */
    protected function createFolder(string $folder): bool
    {
        $newPath = $this->path . '/' . $folder;
        // если папка уже создана
        if (Fs::exists($newPath)) {
            $this->setError($this->module->t('The specified folder "{0}" already exists', [$newPath]));
            return false;
        }
        if (!Fs::makeDirectory($newPath, '0755', false, true)) {
            $this->setError($this->module->t('Error creating folder "{0}"', [$newPath]));
            return false;
        }
        return true;
    }

    /**
     * Создаёт файл с указанным именем.
     * 
     * @param string $filename Имя файла.
     * 
     * @return bool Возвращает значение `false`, если невозможно создать файл.
     */
    protected function createFile(string $filename): bool
    {
        $newFile = $this->path . '/' . $filename;
        // если файл уже создан
        if (Fs::exists($newFile)) {
            $this->setError($this->module->t('The specified file "{0}" already exists', [$newFile]));
            return false;
        }
        $content = '/** File created by FileManager */';
        // если невозможно создать файл
        if (!Fs::put($newFile, $content)) {
            $this->setError($this->module->t('Error creating file "{0}"', [$newFile]));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function runFile(array $attributes): bool
    {
        // создание папки
        if ($this->type === self::TYPE_FOLDER)
            return $this->createFolder($attributes['newName']);
        else
        // создание файла
        if ($this->type === self::TYPE_FILE)
            return $this->createFile($attributes['newName']);
        else {
            $this->setError(Gm::t('app', 'Parameter "{0}" not specified', ['type']));
            return false;
        }
    }
}
