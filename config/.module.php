<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации модуля.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    'translator' => [
        'locale'   => 'auto',
        'patterns' => [
            'text' => [
                'basePath' => __DIR__ . '/../lang',
                'pattern'   => 'text-%s.php'
            ]
        ],
        'autoload' => ['text'],
        'external' => [BACKEND]
    ],

    'accessRules' => [
        // для авторизованных пользователей Панели управления
        [ // разрешение "Полный доступ" (any: view, read)
            'allow',
            'permission'  => 'any',
            'controllers' => [
                'Desk'            => ['view', 'data'], // основная панель
                'Files'           => ['data', 'delete', 'paste', 'filter'], // список файлов / папок
                'FoldersTree'     => ['data'], // дерево папок
                'Download'        => ['file', 'prepare', 'index'], // скачать файл
                'PreviewForm'     => ['view'], // просмотреть файл
                'UploadForm'      => ['view', 'perfom'], // загрузить файл
                'EditForm'        => ['view', 'perfom'], // редактировать файл
                'CompressForm'    => ['view', 'perfom'], // архивировать файлы / папки
                'ExtractForm'     => ['view', 'perfom'], // разархивировать  файлы / папки
                'CreateForm'      => ['file', 'folder', 'perfom'], // создать файл / папку
                'RenameForm'      => ['file', 'folder', 'perfom'], // переименовать файл / папку
                'AttributesForm'  => ['file', 'folder'], // информация о папке / файле
                'PermissionsForm' => ['file', 'folder', 'perfom'], // права доступа к файлу / папке
            ],
            'users' => ['@backend']
        ],
        [ // разрешение "Просмотр" (view)
            'allow',
            'permission'  => 'view',
            'controllers' => [
                'Desk'    => ['view'],
                'Preview' => ['view']
            ],
            'users' => ['@backend']
        ],
        [ // разрешение "Чтение" (read)
            'allow',
            'permission'  => 'read',
            'controllers' => [
                'Desk'    => ['data']
            ],
            'users' => ['@backend']
        ],
        [ // разрешение "Информация о модуле" (info)
            'allow',
            'controllers' => ['Info'],
            'permission'  => 'info',
            'users'       => ['@backend']
        ],
        [ // разрешение "Настройки модуля" (settings)
            'allow',
            'controllers' => ['Settings'],
            'permission'  => 'settings',
            'users'       => ['@backend']
        ],
        [ // для всех остальных, доступа нет
            'deny'
        ]
    ],

    'viewManager' => [
        'id'          => 'gm-filemanager-{name}',
        'useTheme'    => true,
        'useLocalize' => true,
        'viewMap'     => [
            // информации о модуле
            'info' => [
                'viewFile'      => '//backend/module-info.phtml', 
                'forceLocalize' => true
            ],
            'settings' => '/settings.json', // форма настройки модуля
        ]
    ]
];
