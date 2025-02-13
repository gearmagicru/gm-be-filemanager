<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

 namespace Gm\Backend\FileManager\Controller;

use Gm;
use Gm\Panel\Controller\TreeController;

/**
 * Контроллер узлов дерева папок.
 * 
 * Маршруты контроллера:
 * - 'folders/data', выводит список папок пренадлежащик указанной папке.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Workspace\Controller
 * @since 1.0
 */
class FoldersTree extends TreeController
{
    /**
     * {@inheritdoc}
     * 
     * Заменит 'index' на 'data', но в маршрутизаторе будет действие 'index'.
     */
    protected string $defaultAction = 'data';

    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'Folders';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verb' => [
                'class'    => '\Gm\Filter\VerbFilter',
                'autoInit' => true,
                'actions'  => [
                    '' => ['GET', 'ajax' => 'GJAX'] // только для index
                ]
            ],
            'audit' => [
                'class'    => '\Gm\Panel\Behavior\AuditBehavior',
                'autoInit' => true,
                'allowed'  => '*'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function translateAction($params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод записи по указанному идентификатору
            case 'data':
                if ($params->queryId) {
                    if ($params->queryId === '["root"]')
                        return $this->t('view all tree nodes');
                    else
                        return Gm::t(BACKEND, '{data tree action}', [$params->queryId]);
                };

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, '{' . $this->actionName . ' tree action}')
                );
        }
    }
}
