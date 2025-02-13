<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Model;

/**
 * Класс свойств папки.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class FolderProperties extends Properties
{
    /**
     * {@inheritdoc}
     */
    public function isFolder(): bool
    {
        return true;
    }

    /**
     * Возвращает имя папки из указанного ранее идентификатора.
     * 
     * @return string|null
     */
    public function getFolder(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function isSystem(): bool
    {
        if ($this->name) {
            $basename = $this->getBaseName();
            return $basename[0] === '.';
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        $basename = $this->getBaseName();
        if ($this->fileIconsUrl && $basename) {
            return $this->fileIconsUrl . 'list/' . ($this->icons[$basename] ?? 'folder') . '.svg';
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function exists(bool $check = false): bool
    {
        if ($check) {
            if (file_exists($this->name)) {
                return !is_file($this->name);
            } else
                return false;
        }
        return file_exists($this->name);
    }
}
