<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Widget;

use Gm\View\Widget;
use Gm\Panel\Widget\Window;

/**
 * Виджет предварительного просмотра файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Widget
 * @since 1.0
 */
class Preview extends Window
{
    /**
     * Идентификатор выбранного файла.
     * 
     * @see Preview::setFileId()
     * 
     * @var string
     */
    protected string $fileId = '';

    /**
     * Редактор содержимого файла.
     * 
     * @see Preview::getViewer()
     * 
     * @var Widget|null
     */
    protected ?Widget $viewer;

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->ui = 'light';
        $this->cls = 'g-window_profile';
        $this->iconCls = 'g-icon-svg g-icon-m_color_base g-icon-m_visible';
        $this->layout = 'fit';
        $this->width = 700;
        $this->height = 500;
        $this->resizable = true;
        $this->maximizable = true;
    }

    /**
     * Возвращает редактор содержимого файла.
     * 
     * @return null|Widget
     */
    public function getViewer(): ?Widget
    {
        return null;
    }

    /**
     * Устанавливает идентификатор файла.
     * 
     * @param string $value Идентификатор файла.
     * 
     * @return $this
     */
    public function setFileId(string $value): static
    {
        $this->fileId = $value;
        return $this;
    }

    /**
     * Устанавливает содержимое файла.
     * 
     * @param string $content Содержимое файла.
     * 
     * @return $this
     */
    public function setContent(string $content): static
    {
        return $this;
    }

    /**
     * Устанавливает заголовок окна.
     * 
     * @param string $title Заголовок окна.
     * 
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->titleTpl = $this->title = $title;
        return $this;
    }
}
