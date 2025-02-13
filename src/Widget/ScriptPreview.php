<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Widget;

use Gm;
use Gm\View\Widget;

/**
 * Виджет предварительного просмотра текста файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Widget
 * @since 1.0
 */
class ScriptPreview extends Preview
{
    /**
     * Расширение файла.
     * 
     * @see ScriptPreview::setExtension()
     * 
     * @var string
     */
    protected string $extension = '';

    /**
     * {@inheritdoc}
     */
    protected function init(): void
    {
        parent::init();

        $this->tools = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getViewer(): ?Widget
    {
        if (!isset($this->viewer)) {
            $this->viewer = Gm::$services->getAs('widgets')->get('gm.wd.codemirror', [
                'fileExtension' => $this->extension
            ]);
        }
        return $this->viewer;
    }

    /**
     * Устанавливает расширение файла.
     * 
     * @param string $value Расширение файла, например: 'php', 'html'.
     * 
     * @return $this
     */
    public function setExtension(string $value): static
    {
        $this->extension = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(string $content): static
    {
        /** @var null|object|\Gm\Stdlib\BaseObject $viewer */
        $viewer = $this->getViewer();
        if ($viewer)
            $editor = $viewer->run();
        else
            $editor = ['xtype' => 'textarea'];
        $editor['value'] = $content;

        $this->items = $editor;
        return $this;
    }
}
