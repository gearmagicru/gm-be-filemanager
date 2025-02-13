<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Model;

use Phar;
use PharData;
use RarArchive;
use ZipArchive;
use Exception;
use UnexpectedValueException;
use Gm\Stdlib\BaseObject;

/**
 * Класс форматов архивов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class Archive extends BaseObject
{
    /**
     * Название файла архива.
     * 
     * @var null|string
     */
    public ?string $filename = null;

    /**
     * Форматы файлов архивов.
     * 
     * @var array
     */
    protected array $formats = [
        'rar' => [
            'name'      => 'RAR format (.rar)',
            'class'     => 'RarArchive',
            'modules'   => 'rar',
            'compress'  => false,
            'extension' => '.rar'
        ],
        'zip' => [
            'name'      => 'ZIP format (.zip)',
            'class'     => 'ZipArchive',
            'modules'   => ['zip', 'zlib'],
            'compress'  => true,
            'extension' => '.zip'
        ],
        'gz' => [
            'name'      => 'TAR/ZIP format (.tar.gz)',
            'class'     => 'PharData',
            'modules'   => ['zip', 'zlib'],
            'compress'  => true,
            'extension' => '.tar.gz'
        ],
        'bz2' => [
            'name'      => 'TAR/ZIP format (.tar.bz2)',
            'class'     => 'PharData',
            'modules'   => 'bz2',
            'compress'  => true,
            'extension' => '.tar.bz2'
        ],
        'tar' => [
            'name'      => 'TAR/ZIP format (.tar)',
            'class'     => 'PharData',
            'modules'   => 'Phar',
            'compress'  => true,
            'extension' => '.tar'
        ]
    ];

    /**
     * Устанавливает имя файла архива.
     *
     * @param string $filename Имя файла архива.
     * @param string $format Имя формата архива, например: 'rar', 'zip', 'tar', 'gz', 'bz2'.
     *     Если значение указано, имя файла $filename будет преобразовано согласно
     *     правилу {@see Archive::makeName()}.
     * 
     * @return void
     */
    public function setFilename(string $filename, string $format = null): static
    {
        if ($format !== null) {
            $filename = $this->makeName($filename, $format);
        }

        $this->filename = $filename;
        return $this;
    }

    /**
     * Возвращает информацию о архиве.
     *
     * @return array
     */
    public function getInfo(): array
    {
        if ($this->isAvailable()) {
            $method = 'get' . ($this->getExtension()) . 'Info';
            return array_merge(
                $this->getFormat(), 
                call_user_func_array([$this, $method], [$this->filename])
            );
        }
        return [];
    }

    /**
     * Возвращает информацию о ZIP-архиве.
     * 
     * @param string $filename Имя файла архива.
     * 
     * @return array
     */
    public function getZipInfo(string $filename): array
    {
        $info = ['count' => 0];

        $zip  = new ZipArchive;
        /** @var bool|int $result */
        $result = $zip->open($filename, ZipArchive::RDONLY);
        if ($result === true) {
            $info['count'] = $zip->count();
            $zip->close();
        }
        return $info;
    }

    /**
     * Возвращает информацию о RAR-архиве.
     * 
     * @param string $filename Имя файла архива.
     * 
     * @return array
     */
    public function getRarInfo(string $filename): array
    {
        $info = ['count' => 0];

        /** @var false|RarArchive $rar */
        $rar = RarArchive::open($filename);
        if ($rar !== false) {
            /** @var false|array $entries */
            $entries = $rar->getEntries();
            if (is_array($entries))
                $info['count'] = sizeof($entries);
            else
                $info['count'] = 0;
            $rar->close();
        }
        return $info;
    }

    /**
     * Возвращает информацию о TAR-архиве (Gzip).
     * 
     * @param string $filename Имя файла архива.
     * 
     * @return array
     */
    public function getGzInfo(string $filename): array
    {
        return ['count' => 1];
    }

    /**
     * Возвращает информацию о TAR-архиве (Bz2).
     * 
     * @param string $filename Имя файла архива.
     * 
     * @return array
     */
    public function getBz2Info(string $filename): array
    {
        return ['count' => 1];
    }

    /**
     * Возвращает информацию о TAR-архиве.
     * 
     * @param string $filename Имя файла архива.
     * 
     * @return array
     */
    public function getTarInfo(string $filename): array
    {
        $info = ['count' => 0];

        try {
            $archive = new \PharData($filename);
            $info['count'] = $archive->count();
        } catch (\Exception $e) {
            return $info;
        }
        return $info;
    }

    /**
     * Извлекает файлы из архива.
     * 
     * @param string $path Путь извлечения файлов.
     * 
     * @return bool
     */
    public function extract(string $path): bool
    {
        if ($this->isAvailable()) {
            $method = 'extractFrom' . $this->getExtension();
            return call_user_func_array([$this, $method], [$this->filename, $path]);
        }
        return false;
    }

    /**
     * Извлекает файлы из RAR-архива.
     * 
     * @param string $filename Имя файла архива.
     * @param string $path Путь извлечения файлов.
     * 
     * @return bool
     */
    public function extractFromRar(string $filename, string $path): bool
    {
        /** @var false|RarArchive $rar */
        $rar = RarArchive::open($filename);
        if ($rar !== false) {
            /** @var false|array $entries */
            $entries = $rar->getEntries();
            foreach ($entries as $entry) {
                $entry->extract($path);
            }
            $rar->close();
            return true;
        }
        return false;
    }

    /**
     * Извлекает файлы из ZIP-архива.
     * 
     * @param string $filename Имя файла архива.
     * @param string $path Путь извлечения файлов.
     * 
     * @return bool
     */
    public function extractFromZip(string $filename, string $path): bool
    {
        $zip  = new ZipArchive;
        /** @var bool|int $result */
        $result = $zip->open($filename, ZipArchive::RDONLY);
        if ($result === true) {
            $zip->extractTo($path);
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * Извлекает файлы из TAR-архива.
     * 
     * @param string $filename Имя файла архива.
     * @param string $path Путь извлечения файлов.
     * 
     * @return bool
     * 
     * @throws Exception
     */
    public function extractFromTar(string $filename, string $path): bool
    {
        try {
            $tar = new PharData($filename);
            $extracted = $tar->extractTo($path, null, true);
        } catch (Exception $e) {
            $extracted = false;
        }
        return $extracted;
    }

    /**
     * Извлекает файлы из TAR-архива (Gzip).
     * 
     * @param string $filename Имя файла архива.
     * @param string $path Путь извлечения файлов.
     * 
     * @return bool
     * 
     * @throws Exception
     */
    public function extractFromGz(string $filename, string $path): bool
    {
        return $this->extractFromTar($filename, $path);
    }

    /**
     * Извлекает файлы из TAR-архива (Bz2).
     * 
     * @param string $filename Имя файла архива.
     * @param string $path Путь извлечения файлов.
     * 
     * @return bool
     * 
     * @throws Exception
     */
    public function extractFromBz2(string $filename, string $path): bool
    {
        return $this->extractFromTar($filename, $path);
    }

    /**
     * Сжимает файлы в архив.
     * 
     * @param string $filename Имя файла архива.
     * 
     * @return bool
     */
    public function compress(array $files): bool
    {
        if ($this->isAvailable()) {
            $method = 'compressTo' . $this->getExtension();
            return call_user_func_array([$this, $method], [$this->filename, $files]);
        }
        return false;
    }

    /**
     * Сжимает файлы в ZIP-архив.
     * 
     * @param string $filename Имя файла архива.
     * @param array $files Имена файлов.
     * 
     * @return bool
     */
    public function compressToZip(string $filename, array $files): bool
    {
        $zip  = new ZipArchive;
        /** @var bool|int $result */
        $result = $zip->open($filename, ZipArchive::CREATE);
        if ($result === true) {
            foreach ($files as $file) {
                $zip->addFile($file[1], $file[2]);
            }
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * Сжимает файлы в TAR-архив.
     * 
     * @param string $filename Имя файла архива.
     * @param array $files Имена файлов.
     * 
     * @return bool
     * 
     * @throws UnexpectedValueException
     */
    public function compressToTar(string $filename, array $files, int $compression = Phar::NONE): bool
    {
        $filename = str_replace(['.tar.gz', '.tar.bz2'], '.tar', $filename);
        try {
            $tar = new PharData($filename);
        } catch (UnexpectedValueException $e) {
            return false;
        }

        foreach ($files as $file) {
            $tar->addFile($file[1], $file[2]);
        }
        if ($compression === Phar::NONE)
            return true;
        else
            return $tar->compress($compression) !== null;
    }

    /**
     * Сжимает файлы в TAR-архив (Gzip).
     * 
     * @param string $filename Имя файла архива.
     * @param array $files Имена файлов.
     * 
     * @return bool
     * 
     * @throws UnexpectedValueException
     */
    public function compressToGz(string $filename, array $files): bool
    {
        return $this->compressToTar($filename, $files, Phar::GZ);
    }

    /**
     * Сжимает файлы в TAR-архив (Bzip2).
     * 
     * @param string $filename Имя файла архива.
     * @param array $files Имена файлов.
     * 
     * @return bool
     * 
     * @throws UnexpectedValueException
     */
    public function compressToBz2(string $filename, array $files): bool
    {
        return $this->compressToTar($filename, $files, Phar::BZ2);
    }

    /**
     * Возвращает текущий формат архива.
     * 
     * @param string|null $name Имя формат архива, например: 'rar', 'zip', 'tar', 'gz', 'bz2'.
     *     Если значение `null`, определяет формат из расширения файла архива.
     * 
     * @return array|null
     */
    public function getFormat(?string $name = null): ?array
    {
        if ($name === null) {
            $name = $this->getExtension();
        }
        return $this->formats[$name] ?? null;
    }

    /**
     * Возвращает имена доступных форматов, имеющие возможность сжимать файлы.
     * 
     * Доступные - это те форматы сжатия (архивирования) файлов, которые поддерживает PHP.
     * 
     * @param bool $available Возвращать только доступные форматы (по умолчанию `true`).
     * 
     * @return array Например: `['zip', 'tar', 'gz', 'bz2']`.
     */
    public function getCompressionFormats(bool $available = true): array
    {
        $formats = [];
        foreach ($this->formats as $extension => $params) {
            if ($params['compress']) {
                if ($available && $this->isAvailable($extension)) {
                    $formats[] = $extension;
                }
            }
        }
        return $formats;
    }

    /**
     * Возвращает форматы сжатия файлов с их параметрами.
     *
     * @return array
     */
    public function getFormats(): array
    {
        return $this->formats;
    }

    /**
     * Возвращает доступные форматы с их параметрами.
     * 
     * Доступные - это те форматы сжатия (архивирования) файлов, которые поддерживает PHP.
     * 
     * @return array
     */
    public function getAvailable(): array
    {
        $available = [];
        foreach ($this->formats as $extension => $params) {
            if ($this->isAvailable($extension)) {
                $available[$extension] = $params;
            }
        }
        return $available;
    }

    /**
     * Проверяет, является ли формат доступным.
     *
     * @param string|null $format Имя формата архива, например: 'rar', 'zip', 'tar', 'gz', 'bz2'.
     *     Если значение `null`, определяет формат из расширения файла архива.
     * 
     * @return boolean
     */
    public function isAvailable(string $format = null): bool
    {
        /** @var array|null $format */
        $format = $this->getFormat($format);
        if ($format) {
            if ($format['modules']) {
                $modules = (array) $format['modules'];
                foreach ($modules as $module) {
                    if (!extension_loaded($module)) return false;
                }
            }
            return class_exists($format['class']);
        }
        return false;
    }

    /**
     * Возвращает расширение файла из указанного ранее идентификатора файла.
     * 
     * @return string Возвращает '', если расширение отсутствует.
     */
    public function getExtension(): string
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);
        return $extension ? strtolower($extension) : '';
    }

    /**
     * Проверяет, существует ли файла архива.
     * 
     * @see Archive::$filename
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    /**
     * Определяет, доступно ли имя файла архива для записи.
     * 
     * @see Archive::$filename
     * 
     * @return bool
     */
    public function isWritable(): bool
    {
        return is_writable($this->filename);
    }

    /**
     * Добавляет имени файла расширение соответствующего формата.
     * 
     * Например: 'arhive_name' + 'rar' => 'arhive_name.rar'.
     * 
     * @param string $name Имя файла архива.
     * @param string $format Имя формата архива, например: 'rar', 'zip', 'tar', 'gz', 'bz2'.
     * 
     * @return string
     */
    public function makeName(string $name, string $format): string
    {
        /** @var array|null $format */
        $format = $this->getFormat($format);
        return $format ? $name . $format['extension'] : $name;
    }
}
