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
use Gm\Config\Mimes;
use Gm\Filesystem\Filesystem as Fs;

/**
 * Класс свойства файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class FileProperties extends Properties
{
    /**
     * MIMES.
     * 
     * @var Mimes
     */
    protected Mimes $mimes;

    /**
     * URL-адрес, полученный из идентификатора файла.
     * 
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * {@inheritdoc}
     */
    public function setId(?string $value)
    {
        parent::setId($value);

        if ($value) {
            $url = $this->module->getSafeUrl($value);
            $this->url = $url ?: '';
        }
    }

    /**
     * Возвращает MIMES.
     * 
     * @return Mimes
     */
    public function getMimes(): Mimes
    {
        if (!isset($this->mimes)) {
            $this->mimes = new Mimes();
        }
        return $this->mimes;
    }

    /**
     * Возвращает имя файла из указанного ранее идентификатора файла.
     * 
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->name;
    }

    /**
     * Возвращает URL-адрес из указанного ранее идентификатора файла.
     * 
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Возвращает расширение файла из указанного ранее идентификатора файла.
     * 
     * @return string Возвращает '', если расширение отсутствует.
     */
    public function getExtension(): string
    {
        $info = $this->getPathInfo();
        return $this->name ? strtolower($info['extension'] ?? '') : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        $extension = $this->getExtension();
        if ($this->fileIconsUrl && $extension) {
            return $this->fileIconsUrl . 'list/' . ($this->icons[$extension] ?? 'file') . '.svg';
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPreview(): string
    {
        if ($this->isImage())
            return $this->getUrl();
        else
            return $this->getIcon();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): string
    {
        if ($this->name) {
            $size = Fs::size($this->name);
            return $size ? Gm::$app->formatter->toShortSizeDataUnit($size, 1) : SYMBOL_NONAME;
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function isFile(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSystem(): bool
    {
        if ($this->name) {
            $basename = $this->getBaseName();
            if ($basename[0] === '.') {
                return $this->getExtension() === 'php';
            }
        }
        return false;
    }

    /**
     * Проверяет, является ли файл изображением.
     * 
     * @return bool
     */
    public function isImage(): bool
    {
        $extension = $this->getExtension();
        return $extension ? $this->getMimes()->exists($extension, null, 'image') : false;
    }

    /**
     * Проверяет, является ли файл архивом.
     * 
     * @return bool
     */
    public function isArchive(): bool
    {
        $extension = $this->getExtension();
        return $extension ? $this->getMimes()->exists($extension, null, 'archive') : false;
    }

    /**
     * Возвращает информацию о изображение.
     * 
     * @return array
     */
    public function getImageInfo(): array
    {
        $info = [];

        if ($this->name) {
            try {
                $extension = $this->getExtension();
                if (in_array($extension, ['jpg', 'jpeg', 'tiff', 'png', 'gif'])) {
                    /** @var false|array $data */
                    $data = @exif_read_data($this->name, 0, false);
                    if ($data !== false) {
                        $info = [
                            'width'     => $data['COMPUTED']['Width'] ?? null,
                            'height'    => $data['COMPUTED']['Height'] ?? null,
                            'color'     => $data['COMPUTED']['IsColor'] ?? null,
                            'copyright' => $data['COMPUTED']['Copyright'] ?? null,
                            'software'  => $data['COMPUTED']['Copyright'] ?? null,
                            'comment'   => $data['COMMENT'][0] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) { }
        }
        return $info;
    }

    /**
     * Проверяет, является ли файл скриптом.
     * 
     * @return bool
     */
    public function isScript(): bool
    {
        $extension = $this->getExtension();
        return $extension ? $this->getMimes()->exists($extension, null, 'script') : false;
    }

    /**
     * Проверяет, является ли файл текстовым.
     * 
     * @return bool
     */
    public function isText(): bool
    {
        $extension = $this->getExtension();
        return $extension ? $this->getMimes()->exists($extension, null, 'text') : false;
    }

    /**
     * Возвращает содержимое файла.
     * 
     * @return string|false Если значение `false`, невозможно прочитать файл.
     */
    public function getContent()
    {
        return file_get_contents($this->name, true);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(bool $check = false): bool
    {
        if ($check) {
            if (file_exists($this->name)) {
                return is_file($this->name);
            } else
                return false;
        }
        return file_exists($this->name);
    }
}
