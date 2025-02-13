<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации настройки модуля.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    'folderRootId'         => 'home', // идентификатор корневой папки
    'homePath'             => '@home', // базовый путь
    'homeUrl'              => '@home::', // базовый URL-адрес
    'showUnreadableDirs'   => true, // показывать папки без доступа
    'showVCSFiles'         => true, // показывать файлы VCS
    'showDotFiles'         => true, // показывать файлы и папки с точкой
    'showTreeFolderIcons'  => true, // показывать значки папок
    'showTreeSomeIcons'    => true, // показывать значки системных папок
    'showTreeToolbar'      => true, // показывать панель инструментов
    'showTreeRoot'         => true, // показывать корневую папку
    'showTree'             => true, // показывать панель
    'resizeTree'           => true, // изменять размер панели
    'useTreeArrows'        => true, // показывать стрелочки
    'sortTreeFolders'      => true, // сортировать папки
    'totalExpandedFolders' => 1000, // количество раскрываемых папок
    'treePosition'         => 'left', // положение панели
    'treeWidth'            => 300, // ширина панели дерева папок
    'showOnlyFiles'         => false, // показывать только файлы
    'dblClick'              => true, // двойной клик на папке / файле
    'showGridColumnLines'   => false, // показывать линии между столбцами
    'showGridRowLines'      => false, // показывать линии между строками
    'stripeGridRows'        => false, // чередование строк
    'showGridIcons'         => true, // показывать значки
    'showPopupMenu'         => true, // показывать всплывающие меню
    'gridPageSize'          => 1000, // количество файлов и папок на странице
    'showSizeColumn'        => true, // показывать столбец "Размер"
    'showTypeColumn'        => true, // показывать столбец "Тип"
    'showMimeTypeColumn'    => true, // показывать столбец "MIME-тип"
    'showPermissionsColumn' => true, // показывать столбец "Права доступа"
    'showAccessTimeColumn'  => true, // показывать столбец "Последний доступ"
    'showChangeTimeColumn'  => true, // показывать столбец "Последнее обновление"
    'icons' => [ // значки расширений файлов
        'html'  => 'html',
        'svg'   => 'html',
        'xml'   => 'xml', 
        'json'  => 'json',
        'pjson' => 'json',
        'js'    => 'js',
        'css'   => 'css',
        'php'   => 'php',
        'phtml' => 'php',
        'xaml'  => 'xaml',
        'png'   => 'image',
        'ico'   => 'image',
        'gif'   => 'image',
        'jpg'   => 'image',
        'jpeg'  => 'image',
        'eot'   => 'font',
        'otf'   => 'font',
        'ttf'   => 'font',
        'woff'  => 'font',
        'woff2' => 'font',
        'rar'   => 'archive',
        'zip'   => 'archive',
        'tar'   => 'archive',
        'gz'    => 'archive',
        'bz2'   => 'archive',
        'tgz'   => 'archive',
        '7zip'  => 'archive',
        'md'    => 'md',
        'pdf'   => 'pdf',
        'htaccess' => 'htaccess'
    ],
    'overlays' => [ // перекрытие значка папки
        'runtime'    => 'runtime',
        'public'     => 'public',
        'config'     => 'config',
        'vendor'     => 'script',
        'vendors'    => 'script',
        'js'         => 'script',
        'css'        => 'script',
        'psr'        => 'php',
        'lang'       => 'lang',
        'language'   => 'lang',
        'help'       => 'help',
        'images'     => 'images',
        'img'        => 'images',
        'src'        => 'src',
        'log'        => 'log',
        'gm'         => 'gm',
        'gearmagic'  => 'gm',
        'upload'     => 'uploads',
        'uploads'    => 'uploads',
        'Installer'  => 'installer',
        'Controller' => 'controller',
        'themes'     => 'themes',
        'assets'     => 'assets',
        'views'      => 'views',
        'symfony'    => 'symfony',
        'composer'   => 'composer',
        'phpmailer'  => 'phpmailer',
        'frontend'   => 'frontend',
        'backend'    => 'backend',
        'console'    => 'console',
        '.vscode'    => 'vscode'
    ]
];
