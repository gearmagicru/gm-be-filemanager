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
use Gm\Stdlib\BaseObject;
use Gm\Mvc\Module\BaseModule;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Модель скачивания файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class Download extends BaseObject
{
    use Gm\Stdlib\ErrorTrait;

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
     * @see Download::getFileId()
     * 
     * @var string |null
     */
    protected ?string $fileId = null;

    /**
     * @param bool $make Есои значение `true`, генерирует  идентификатор.
     * 
     * @return string|null
     */
    public function getFileId(bool $make = true): ?string
    {
        if ($make) {
            $this->fileId =  uniqid();
        }
        return $this->fileId;
    }

    /**
     * Сжатие файлов и создание архива.
     * 
     * @param string $filename Имя файла архива.
     * @param array $files Имена файлов.
     * 
     * @return bool
     */
    public function compress(string $filename, array $files): bool
    {
        /** @var Archive $archive */
        $archive = new Archive(['filename' => $filename]);
        return $archive->compress($files);
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
    public function validateFiles(array $files): false|array
    {
        $result = [];

        /** @var array $unique Уникальность имени файла / папки в архиве */
        $unique = [];
        /** @var array $names Имена файлов / папок */
        $names = [];
        foreach ($files as $fileId) {
            /** @var string|false $absname Полный путь или имя файла с путём */
            $absname = $this->module->getSafePath($fileId);
            if ($absname === false) {
                $this->setError(
                    $this->module->t('The folder or file with the same name "{0}" does not exist', [$absname])
                );
                return false;
            }

            $basename = basename($absname);
            if (isset($unique[$basename]))
                $unique[$basename] = true;
            else
                $unique[$basename] = false;
            $names[] = [
                'id'       => $fileId,
                'base'     => $basename,
                'absolute' => $absname
            ];
        }

        /** @var \Symfony\Component\Finder\Finder $finder */
        $finder = Fs::finder();
        // не игнорировать файлы с точкой
        $finder->ignoreDotFiles(false);
        foreach ($names as $name) {
            $absolute = $name['absolute'];
            $base = $name['base'];
            // чтобы не было повторений имён
            $base = $unique[$base] ? $name['id'] : $base;
            // если папка
            if (is_dir($absolute)) {
                $finder->files()->in($absolute);
                foreach ($finder as $info) {
                    $result[] = [
                        self::IN_FOLDER,
                        $info->getPathname(), 
                        $base . DS . $info->getRelativePathname()
                    ];
                }
            // если файл
            } else
                $result[] = [self::OUT_FOLDER, $absolute, $base];
        }
        return $result;
    }

    /**
     * @return string
     */
    public function makeName(): string
    {
        return uniqid() . '_' . date('His');
    }

    /**
     * Подготовка файлов к скачиванию.
     * 
     * @param array $selected Идентфикаторы файлов / папок для скачивания. 
     *     Например: `['file', 'folder/file', ...]`.
     * 
     * @return bool
     */
    public function prepare(array $selected): bool
    {
        /** @var string|null $id Генерация идент. файла для скачивания */
        $id = $this->getFileId();
        if ($id === null) {
            $this->setError(
                $this->module->t('Unable to get file ID')
            );
            return false;
        }

        /** @var array|false $files Проверка файлов и папок для скачивания */
        if (!$files = $this->validateFiles($selected)) {
            return false;
        }

        // если выбран один элемент и это файл
        $isFile = sizeof($files) === 1 && $files[0][0] === self::OUT_FOLDER;
        // если выбрана папка или несколько файлов или папок
        if (!$isFile) {
            if (!(new Archive())->isAvailable('zip')) {
                $this->setError(
                    $this->module->t('PHP module "{0}" is not installed', ['ZipArchive'])
                );    
                return false;
            }

            // определяем имя архива, если выбрана одна папка, то имя этой папки
            // если выбрано несколько папок или файлов, то имя родительской папки
            if (sizeof($selected) === 1)
                $archive = basename($selected[0]);
            else
                $archive = $this->makeName();

            // имя файла архива (включает путь)
            $filename = Gm::getAlias('@runtime/' . $archive . '.zip');
            if (!$this->compress($filename, $files)) {
                $this->setError(
                    $this->module->t('Unable to compress selected files for download')
                ); 
                return false;
            }
            $runtime = true;
        } else {
            $filename = $files[0][1];
            $runtime = false;
        }

        /** @var \Gm\Session\Container $storage */
        $storage = $this->module->getStorage();
        $storage->download = [
            'id'       => $id,
            'filename' => $filename,
            'runtime'  => $runtime // если файл находится во временной папке
        ];

        Gm::debug('Model::prepare($files)', $files);
        Gm::debug('Module::$storage->download', $storage->download);
        return true;
    }

    /**
     * Возвращает имя файла для скачивания.
     * 
     * @param string $fileId Идентификатор подготовленного файла для скачивания.
     * 
     * @return string|null Возвращает значение `null`, если невозможно получить имя 
     *     файла.
     */
    public function getFilename(string $fileId): ?string
    {
        /** @var \Gm\Session\Container $storage */
        $storage = $this->module->getStorage();
        if ($storage->download) {
            $id = $storage->download['id'] ?? null;
            if ($id === $fileId) {
                $filename = $storage->download['filename'] ?? null;
                // если файл еще существует на сервере
                if ($filename) {
                    return file_exists($filename) ? $filename : null;
                }
            }
        }
        return null;
    }

    /**
     * Сбрасывает параметры для скачивания файла и удаляет временный файл.
     * 
     * @return void
     */
    public function reset(): void
    {
        /** @var \Gm\Session\Container $storage */
        $storage = $this->module->getStorage();
        if ($storage->download) {
            $runtime = $storage->download['runtime'] ?? false;
            if ($runtime) {
                /** @var string|null $filename */
                $filename = $storage->download['filename'] ?? null;
                // если файл еще существует на сервере
                if ($filename) {
                    if (!Fs::deleteFile($filename)) {
                        Gm::debug('Error', ['error' => 'Cannot delete file "' . $filename . '".']);
                    }
                }
            }
            $storage->download = null;
        }
    }
}
