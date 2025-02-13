<?php
/**
 * Модуль веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager;

use Gm;

/**
 * Модуль менеджера файлов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager
 * @since 1.0
 */
class Module extends \Gm\Panel\Module\Module
{
    /**
     * {@inheritdoc}
     */
    public string $id = 'gm.be.filemanager';

    /**
     * {@inheritdoc}
     */
    public function controllerMap(): array
    {
        return [
            'attributes'  => 'AttributesForm',
            'create'      => 'CreateForm',
            'rename'      => 'RenameForm',
            'permissions' => 'PermissionsForm',
            'preview'     => 'PreviewForm',
            'edit'        => 'EditForm',
            'upload'      => 'UploadForm',
            'compress'    => 'CompressForm',
            'extract'     => 'ExtractForm',
            'folders'     => 'FoldersTree',
        ];
    }

    /**
     * Возвращает абсолютный путь для указанной папки или файла.
     * 
     * Например: 
     *     - 'upload/images' => '/home/www/site/upload/images'; 
     *     - 'upload/images/image.jpg' => '/home/www/site/upload/images/image.jpg'.
     * 
     * @param string $path Папка или файл.
     * 
     * @return false|string Возвращает `false`, если указанная директории или файл 
     *     не существует.
     */
    public function getSafePath(string $path): false|string
    {
        static $homePath, $folderRootId;

        if ($homePath === null) {
            /** @var \Gm\Config\Config $settings */
            $settings = $this->getSettings();

            $homePath = $settings->getValue('homePath', false);
            if ($homePath) {
                $homePath = Gm::getAlias($homePath);
                $folderRootId = $settings->getValue('folderRootId', false);
            }
        }

        if ($homePath) {
            // если указанный путь является идентификатором корневой папки
            if ($folderRootId === $path || $path === '') {
                return  $homePath;
            }

            /** @var false|string $path */
            $path = $homePath . DS . ltrim($path, DS);
            if ($path === false || !file_exists($path)) {
                return false;
            }
            return $path;
        }
        return false;
    }

    /**
     * @param string $path
     * 
     * @return false|string
     */
    public function getSafeUrl(string $path): false|string
    {
        /** @var \Gm\Config\Config $settings */
        $settings = $this->getSettings();
        if ($settings->homeUrl && $settings->folderRootId) {
            if ($settings->folderRootId === $path)
                $alias = $settings->homeUrl;
            else
                $alias = $settings->homeUrl . '/' . $path;
            $url = Gm::getAlias($alias);
            if ($url === false) {
                return false;
            }
            return $url;
        }
        return false;
    }

    /**
     * @see Module::getFileIconsUrl()
     * 
     * @var string
     */
    protected $iconUrl;

    /**
     * Возвращает URL-путь к значкам файлов.
     *
     * @return string
     */
    public function getFileIconsUrl(): string
    {
        if (!isset($this->iconUrl)) {
            $this->iconUrl = $this->getAssetsUrl() . '/images/files/';
        }
        return $this->iconUrl;
    }

    /**
     * Возвращает URL-путь к перекрытиям значков папок.
     *
     * @return string
     */
    public function getFileOverlaysUrl(): string
    {
        return $this->getAssetsUrl() . '/images/overlays/';
    }

    /**
     * Возвращает путь или имя файла (включая путь) из названия папки.
     * 
     * Например: 
     *     - 'home/public/themes' => '/home/user/public/themes';
     *     - 'home/public/themes/file.php' => '/home/user/public/themes/file.php'.
     * 
     * @param string $folder Название папки, например: 'foo/bar', 'foo/bar/file.php'.
     * @param bool $basePath Если значение `true`, возвращаемый путь или имя файла 
     *     будут содержать базовый (абсолютный) путь (по умолчанию `false`).
     * @param string $folderRoot Название корневой папки в дереве папок (по умолчанию 'home').
     * 
     * @return string
     */
    public function getPathFromFolder(string $folder, bool $basePath = false, string $folderRoot = 'home'): string
    {
        if (empty($folder) || ($folder === $folderRoot))
            $path = '';
        else {
            $path = ltrim($folder, $folderRoot);
            $path = trim($path, '/');
        }

        // TODO: safe path
        if ($basePath) {
            return Gm::alias('@path', $path === '' ? '' : '/' . $path);
        }
        return $path;
    }

    /**
     * Возвращает абсолютный URL-путь (с именем файла) из названия папки.
     * 
     * Например: 
     *     - 'home/public/themes' => '/public/themes';
     *     - 'home/public/themes/image.jpg' => '/public/themes/image.jpg'.
     * 
     * @param string $folder Название папки, например: 'foo/bar', 'foo/bar/image.jpg'.
     * @param string $folderRoot Название корневой папки в дереве папок (по умолчанию 'home').
     * 
     * @return string
     */
    public function getUrlFromFolder(string $folder, string $folderRoot = 'home'): string
    {
        if (empty($folder) || ($folder === $folderRoot))
            $url = '';
        else {
            $url = ltrim($folder, $folderRoot);
            $url = trim($url, '/');
        }

        // TODO: safe path
        return Gm::alias('@home::', $url === '' ? '' : '/' . $url);
    }
}
