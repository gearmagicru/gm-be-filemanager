<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Controller;

use Gm;
use Gm\Panel\Http\Response;
use Gm\Panel\Widget\TabWidget;
use Gm\Panel\Controller\BaseController;
use  Gm\Backend\FileManager\Widget\Desk as DeskWidget;

/**
 * Контроллер панели менеджера файлов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class Desk extends BaseController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultAction = 'view';

    /**
     * {@inheritdoc}
     */
    public function createWidget(): DeskWidget
    {
        /** @var DeskWidget $desk */
        $desk = new DeskWidget();

        // панель вкладки компонента (Ext.tab.Panel Sencha ExtJS)
        $desk->id = 'tab';  // tab => gm-filemanager-tab

        /** @var Gm\Config\Config $settings */
        $settings = $this->module->getSettings();
        // панель дерева папок
        $desk->folderTree->applySettings($settings);
        // панель отображения файлов
        $desk->filesView->applySettings($settings);
        return $desk;
    }

    /**
     * Действие "view" выводит интерфейса панели.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var TabWidget $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        // сброс фильтра файлов
        $store = $this->module->getStorage();
        $store->directFilter = null;

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }
}
