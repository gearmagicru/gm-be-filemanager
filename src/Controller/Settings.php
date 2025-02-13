<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Controller;

use Gm\Panel\Widget\SettingsWindow;
use Gm\Panel\Controller\ModuleSettingsController;

/**
 * Контроллер настроек модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Controller
 * @since 1.0
 */
class Settings extends ModuleSettingsController
{
    /**
     * {@inheritdoc}
     */
    public function createWidget(): SettingsWindow
    {
        /** @var SettingsWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 660;
        $window->autoHeight = true;
        $window->resizable = false;

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->autoScroll = true;
        $window->form->bodyPadding = 10;
        $window->form->defaults = [
            'labelAlign' => 'right',
            'labelWidth' => 270
        ];
        $window->responsiveConfig = [
            'height < 870' => ['height' => '100%'],
            'width < 660' => ['width' => '100%'],
        ];
        $window->form->loadJSONFile('/settings', 'items');
        return $window;
    }
}
