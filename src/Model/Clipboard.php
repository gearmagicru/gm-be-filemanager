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
 * Буфер обмена для копирования и вырезки файлов и папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class Clipboard extends BaseObject
{
    use Gm\Stdlib\ErrorTrait;

    /**
     * @var string Скопировать файлы в буфер обмена.
     */
    public const ACTION_COPY = 'copy';

    /**
     * @var string Вырезать файлы в буфер обмена.
     */
    public const ACTION_CUT = 'cut';

    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * Действие над файлами.
     * 
     * Вырезать или скопировать в буфер обмена.
     * 
     * @var string 
     */
    public string $action = '';

    /**
     * Путь, куда необходимо скопировать или вырезать файлы.
     * 
     * @var string 
     */
    public string $path = '';

    /**
     * Количество файлов / папок, которые были вставлены.
     * 
     * @see Clipboard::paste()
     * 
     * @var int
     */
    protected int $count = 0;

    /**
     * Проверяет, было ли совершено действие.
     * 
     * @return bool
     */
    public function hasAction(): bool
    {
        return $this->action === self::ACTION_COPY || $this->action === self::ACTION_CUT;
    }

    /**
     * Если было действие - копирование в буфер.
     * 
     * @return bool
     */
    public function isCopy(): bool
    {
        return $this->action === self::ACTION_COPY;
    }

    /**
     * Если было действие - вырезание в буфер.
     * 
     * @return bool
     */
    public function isCut(): bool
    {
        return $this->action === self::ACTION_CUT;
    }

    /**
     * Вставка файлов и папок из буфера обмена.
     * 
     * @param array $filesId Идентификаторы файлов и папок.
     * 
     * @return bool Возвращает значение `true`, если была выполнена успешно вставка.
     */
    public function paste(array $filesId): bool
    {
        Fs::$throwException = false;
        foreach ($filesId as $fileId) {
            /** @var string|null $filename Имя файла или папки (путь) */
            $filename = $this->module->getSafePath($fileId);
            if ($filename) {
                // если файл
                if (Fs::isFile($filename)) {
                    /** @var string $to Путь к целевому файлу */
                    $to = $this->path . DS . basename($filename);
                    if (!Fs::copy($filename, $to)) {
                        $this->setError(
                            $this->module->t('Error copying file from "{0}" to "{1}"', [$filename, $to])
                        );
                        return false;
                    }
                    // если вырезать
                    if ($this->isCut()) {
                        if (!Fs::deleteFile($filename)) {
                            $this->setError(
                                $this->module->t('Error deleting file "{0}"', [$filename])
                            );
                            return false;
                        }
                    }
                // если папка
                } else {
                    if (!Fs::copyDirectory($filename, $this->path . DS . basename($filename))) {
                        $this->setError(
                            $this->module->t('Error copying folder from "{0}" to "{1}"', [$filename, $this->path])
                        );
                        return false;
                    }
                    // если вырезать
                    if ($this->isCut()) {
                        if (!Fs::deleteDirectory($filename)) {
                            $this->setError(
                                $this->module->t('Error deleting folder "{0}"', [$filename])
                            );
                            return false;
                        }
                    }
                }
            } else {
                $this->setError(
                    $this->module->t('Error getting from file / folder ID "{0}"', [$fileId])
                );
                return false;
            }
        }

        $this->count = sizeof($filesId);
        return true;
    }

    /**
     * Возвращает количество встлавленных файлов и папок из буфера.
     * 
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
