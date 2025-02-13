<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru/framework/
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Widget;

use Gm\Helper\Html;

/**
 * Виджет предварительного просмотра изображения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Widget
 * @since 1.0
 */
class ImagePreview extends Preview
{
    /**
     * {@inheritdoc}
     */
    public function setContent(string $content): static
    {
        $this->items = [
            'html' => Html::tag(
                'div', 
                '', 
                [
                    'class' => 'gm-filemanager-form__preview',
                    'style' => 'background-image: url(' . $content . ')'
                ]
            )
        ];
        return $this;
    }
}
