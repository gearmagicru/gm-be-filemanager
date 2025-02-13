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
 * Модель данных архивирования файлов / папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class CompressForm extends FileModel
{
    /**
     * @var int Файлы находятся в папках.
     */
    public const IN_FOLDER = 0;

    /**
     * @var int Файлы не находятся в папках.
     */
    public const OUT_FOLDER = 1;

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
            'name'   => 'archiveName', // название архива (т.к. 'name' - название модели, то 'archiveName')
            'format' => 'format', // тип архива
            'path'   => 'path', // путь к архиву
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name'   => $this->module->t('Archive name'),
            'format' => $this->module->t('Archive type'),
            'path'   => $this->module->t('Path')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return [
            [['name', 'format', 'path'], 'notEmpty'],
            [
                'format', 'enum', 'enum' => (new Archive())->getCompressionFormats()
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
            'titleRun'        => $this->module->t('Compression'),
            'msgSuccessRun'   => $this->module->t('File / folder compression completed successfully'),
            'msgUnsuccessRun' => $this->module->t('Error in compressing files / folders')
        ];
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
     * Создаёт имя файла архива.
     *
     * @param array $files Идентфикаторы файлов / папок.
     * @param string $pathId Идентификатор папки файла архива.
     * 
     * @return string
     */
    public function makeArchiveName(array $files, string $pathId): string
    {
        if (empty($files)) return '';

        if (sizeof($files) > 1)
            return basename($pathId);
        else
            return basename($files[0]);
    }

    /**
     * Возвращает доступные форматы сжатия файлов.
     *
     * @return array
     */
    public function getArchiveFormats(): array
    {
        $formats = [];

        $available = (new Archive())->getAvailable();
        foreach ($available as $extension => $params) {
            if ($params['compress']) {
                $formats[] = [$extension, '#' . $params['name']];
            }
        }
        return $formats;
    }

    /**
     * Проверяет выбранные идентификаторы файлов и папок.
     * 
     * @param array $files Идентфикаторы файлов / папок. 
     *     Например: `['file', 'folder/file', ...]`.
     * 
     * @return false|array Возвращает массив имён файлов.
     *     Например: `[[{IN_FOLDER|OUT_FOLDER}, '/path/file.txt', 'file.txt'], ...]`.
     */
    public function validateFiles(array $files)
    {
        $result = [];

        /** @var \Symfony\Component\Finder\Finder $finder */
        $finder = Fs::finder();
        // не игнорировать файлы с точкой
        $finder->ignoreDotFiles(false);
        foreach ($files as $fileId) {
            /** @var string|false $absoluteName Полный путь или имя файла с путём */
            $absoluteName = $this->module->getSafePath($fileId);
            if ($absoluteName === false) {
                $this->setError(
                    $this->module->t('The folder or file with the same name "{0}" does not exist', [$absoluteName])
                );
                return false;
            }

            $baseName = basename($absoluteName);
            if (is_dir($absoluteName)) {
                $finder->files()->in($absoluteName);
                foreach ($finder as $info) {
                    $result[] = [
                        self::IN_FOLDER,
                        $info->getPathname(), 
                        $baseName . DS . $info->getRelativePathname()
                    ];
                }
            } else {
                $result[] = [self::OUT_FOLDER, $absoluteName, $baseName];
            }
        }
        return $result;
    }

    /**
     * Подготовка файлов к сжатию.
     * 
     * @param array $selected Идентфикаторы файлов / папок для сжатия. 
     *     Например: `['file', 'folder/file', ...]`.
     * 
     * @return bool
     */
    public function prepare(array $selected): bool
    {
        /** @var array|false $files Проверка файлов и папок для сжатия */
        if (!$files = $this->validateFiles($selected)) {
            return false;
        }

        /** @var \Gm\Session\Container $storage */
        $storage = $this->module->getStorage();
        $storage->compress = $files;

        Gm::debug('Model::prepare($selected)', $files);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function runFile(array $attributes): bool
    {
        /** @var Archive $archive */
        $archive = new Archive();
        $archive->setFilename(
            $attributes['path'] . '/' . $attributes['archiveName'],
            $attributes['format']
        );

        // можно ли создать файл архива
        if ($archive->isWritable()) {
            $this->setError($this->module->t('Archive file "{0}" is not writable', [$archive->filename]));
            return false;
        }

        /** @var \Gm\Session\Container $storage */
        $storage = $this->module->getStorage();
        if (empty($storage->compress)) {
            $this->setError($this->module->t('No files selected for compression'));
            return false;
        }

        /** @var bool $compressed */
        $compressed = $archive->compress($storage->compress);

        // сбрасываем выбранные имена файлов / папок для сжатия
        $storage->compress = null;
        return $compressed;
    }
}
