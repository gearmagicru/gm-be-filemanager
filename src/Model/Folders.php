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
use Gm\Config\Config;
use Gm\Mvc\Module\BaseModule;
use Gm\Filesystem\Filesystem;
use Gm\Panel\Data\Model\NodesModel;

/**
 * Модель данных дерева папок.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Workspace\Model
 * @since 1.0
 */
class Folders extends NodesModel
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\FileManager\Module
     */
    public BaseModule $module;

    /**
     * Стили папок.
     * 
     * Имеет вид: `['folder' => 'style']`.
     * 
     * @var array
     */
    public array $foldersCls = [
        'public'  => 'public',
        'vendor'  => 'vendor',
        'config'  => 'config',
        'runtime' => 'runtime',
    ];

    /**
     * {@inheritdoc}
     */
    public string $nodeParam = 'path';

    /**
     * Настройки модуля.
     * 
     * @var Config|null
     */
    protected ?Config $settings;

    /**
     * Идентификатор корневой папки.
     *
     * @var string
     */
    protected string $rootNodeId;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->settings = $this->module->getSettings();
        $this->rootNodeId = $this->settings->folderRootId ?: 'home';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): mixed
    {
        if (!isset($this->nodeId)) {
            $this->nodeId = Gm::$app->request->getQuery($this->nodeParam);
        }
        return $this->nodeId;
    }

    /**
     * Возвращает корневые элементы дерева (элементы панели разделов не имеющих 
     * родителей).
     * 
     * @return array
     */
    public function getRootNodes(): array
    {
        /** @var array $nodes Корневые элементы дерева */
        $nodes  = [];

        /** @var \Symfony\Component\Finder\Finder $finder */
        $finder = Filesystem::finder();
        $finder
            ->depth('== 0')
            ->ignoreVCS(!$this->settings->showVCSFiles)
            ->ignoreVCSIgnored(!$this->settings->showVCSFiles)
            ->ignoreDotFiles(!$this->settings->showDotFiles);
        // показывать папки без доступа
        if (!$this->settings->showUnreadableDirs) {
            $finder->ignoreUnreadableDirs();
        }
    
        /** @var false|string $findInPath */
        $findInPath = $this->module->getSafePath('');
        if ($findInPath === false) {
            Gm::debug('Error', ['error' => sprintf('Path "%s" not exists.', '')]);
            return [];
        }

        foreach ($finder->directories()->in($findInPath) as $dir => $info) {
            $name = $info->getFileName();
            $node = [
                'id'   => $name,
                'text' => $name,
                'leaf' => false
            ];
            // показывать значки папок
            if ($this->settings->showTreeFolderIcons) {
                // показывать значки системных папок
                if ($this->settings->showTreeSomeIcons && isset($this->foldersCls[$name])) {
                    $node['iconCls'] = 'gm-filemanager__icon-folder_' . $this->foldersCls[$name];
                }
            } else
                $node['iconCls'] = 'empty';
            $nodes[] = $node;
        }
        return $nodes;
    }


    /**
     * Возвращает дочернии элементы дерева по указанному идентификатору родителя.
     * 
     * @param int|null $parentId Идентификатор родителя.
     * 
     * @return array Дочернии элементы дерева. Если идентификатор родителя не указан, то 
     *     реузльтат - пустой массив.
     */
    public function getChildNodes(string $parentId): array
    {
        /** @var array $nodes Корневые элементы дерева */
        $nodes  = [];

        /** @var \Symfony\Component\Finder\Finder $finder */
        $finder = Filesystem::finder();
        $finder
            ->ignoreVCS(!$this->settings->showVCSFiles)
            ->ignoreVCSIgnored(!$this->settings->showVCSFiles)
            ->ignoreDotFiles(!$this->settings->showDotFiles);
        // показывать папки без доступа
        if (!$this->settings->showUnreadableDirs) {
            $finder->ignoreUnreadableDirs();
        }

        /** @var false|string $findInPath */
        $findInPath = $this->module->getSafePath($parentId);
        if ($findInPath === false) {
            return [];
        }

        $finder->depth('== 0');
        foreach ($finder->directories()->in($findInPath) as $dir => $info) {
            $name = $info->getFileName();
            $node = [
                'id'   => $parentId . '/' . $name,
                'text' => $name,
                'leaf' => false
            ];
            // показывать значки папок
            if (!$this->settings->showTreeFolderIcons) {
                $node['iconCls'] = 'empty';
            }
            $nodes[] = $node;
        }
        return $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodes(): array
    {
        $nodeId = $this->getIdentifier();

        if ($nodeId) {
            if ($nodeId === $this->rootNodeId)
                return $this->getRootNodes();
            else
                return $this->getChildNodes($nodeId);
        }
        return [];
    }
}
