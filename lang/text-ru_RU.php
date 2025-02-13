<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Пакет русской локализации.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    '{name}'        => 'Файловый менеджер',
    '{description}' => 'Управление файлами и папками на стороне сервера',
    '{permissions}' => [
        'any'  => ['Полный доступ', 'Управление файлами и папками'],
        'view' => ['Просмотр', 'Просмотр файлов и папок'],
        'read' => ['Чтение', 'Чтение файлов и папок']
    ],

    // Files: панель инструментов
    'Expand all folders' => 'Развернуть все папки',
    'Collapse all folders' => 'Свернуть все папки',
    'Home' => 'Корневая папка',
    'Go up one level' => 'Перейти на один уровень выше',
    'Create folder' => 'Создать папку',
    'Create file' => 'Создать файл',
    'Delete selected folders / files' => 'Удалить выбранные папки / файлы',
    'Delete' => 'Удалить',
    'Refresh' => 'Обновить',
    'Search for folder / file' => 'Поиск папки / файла',
    'Find' => 'Найти',
    'Reset' => 'Сбросить',
    'Profiling a folder / file' => 'Профилирование папки / файла',
    'Select all' => 'Выделить всё',
    'Invert selection' => 'Инвертировать выделение',
    'Remove selection' => 'Убрать выделение',
    'Upload file' => 'Загрузить файл',
    'Download selected folders / files' => 'Скачать выбранные папки / файлы',
    'Download' => 'Скачать',
    'Archive' => 'Архивировать',
    'Extract from archive' => 'Разархивировать',
    'Rename' => 'Переименовать',
    'Edit file' => 'Редактировать файл',
    'Edit' => 'Редактировать',
    'View file' => 'Просмотреть файл',
    'View' => 'Просмотреть',
    'Permissions' => 'Права доступа',
    'Copy selected folders / files' => 'Копировать выделенные папки / файлы',
    'Copy' => 'Копировать',
    'Cut' => 'Вырезать',
    'Move selected folders / files to clipboard' => 'Переместить выделенные папки / файлы в буфер обмена',
    'Paste' => 'Вставить',
    'Paste the contents of the buffer into the current folder' => 'Вставить содержимое буфера в текущую папку',
    'Information about the selected folder/file' => 'Информация о выбранной папке / файле',
    'Information' => 'Информация',
    'Grid' => 'Сетка',
    'List' => 'Список',
    'Help' => 'Справка',
    'Settings' => 'Настройки',
    // Files: фильтр
    'Search name' => 'Название',
    'Search location' => 'Место поиска',
    'find File' => 'найти Файл',
    'find Path' => 'найти Папку',
    // Files: столбцы
    'Name' => 'Имя',
    'Type' => 'Тип',
    'Folder' => 'Папка c файлами',
    'File' => 'Файл',
    'MIME type' => 'MIME-тип',
    'Size' => 'Размер',
    'Permissions' => 'Права доступа',
    'Access time' => 'Последний доступ',
    'File last accessed time' => 'Время последнего доступа к файлу',
    'Change time' => 'Последнее изменение',
    'File last modified time' => 'Время последнего изменения файла',
    // Files: сообщения / удаление
    'Only one file or folder needs to be selected' => 'Необходимо выбрать только один файл/папку!',
    'You must select a file or folder' => 'Необходимо выбрать файл или папку!',
    'You must select a file' => 'Необходимо выбрать только файл!',
    'Are you sure you want to delete the selected files / folders ({0} pcs)? {1}' 
        => 'Вы действительно хотите удалить выбранные файлы / папки ({0} шт.)? {1}',
    'Are you sure you want to delete the selected files ({0} pcs)? {1}' 
        => 'Вы действительно хотите удалить выбранные файлы ({0} шт.)? {1}',
    'Are you sure you want to delete the file "{0}"?' => 'Вы действительно хотите удалить файл "{0}"?',
    'Are you sure you want to delete the selected folders ({0} pcs)? {1}' 
        => 'Вы действительно хотите удалить выбранные папки ({0} шт.)? {1}',
    'Are you sure you want to delete the folder "{0}"?' => 'Вы действительно хотите удалить папку "{0}"?',
    'Cannot delete selected files or folders' => 'Невозможно удалить выбранные файлы или папки.',
    // Files: сообщения / удаление
    'The records were partially deleted, from the selected {nSelected} {selected, plural, =1{record} other{records}}, {nDeleted} were deleted, the rest were omitted' => 
        'Из выбранных файлов/папок «<b>{nSelected}</b>», {deleted, plural, =1{удалён «<b>1</b>» файл/папка} few{удалено «<b>{nDeleted}</b>» файлов/папок} '
      . 'many{удалено «<b>{nDeleted}</b>» файлов/папок} other{удалено «<b>{nDeleted}</b>» файлов/папок}}.',
    'Records have been partially deleted, {nDeleted} deleted, {nSkipped} {skipped, plural, =1{record} other{records}} skipped' =>
        'Файлы/папки были частично удалены, {deleted, plural, =1{удалён «<b>1</b>» файл/папка} few{удалены «<b>{nDeleted}</b>» файлы/папки} '
      . 'many{удалено «<b>{nDeleted}</b>» файлов / папок} other{удалено «<b>{nDeleted}</b>» файлов/папок}}, '
      . '{skipped, plural, =1{пропущена «<b>1</b>» файл/папка} few{пропущено «<b>{nSkipped}</b>» файлов/папок} '
      . 'many{пропущено «<b>{nSkipped}</b>» файлов/папок} other{пропущено «<b>{nSkipped}</b>» файлов/папок}}.',
    'Unable to delete {N} {n, plural, =1{record} other{records}}, no records are available' =>
        'Невозможно выполнить удаление {n, plural, =1{файла/папки, файл/папка не доступы} other{«<b>{N}</b>» файлов/папок, файлы/папки не доступы}}.',
    'Unable to delete {n, plural, =1{record} other{records}}, no {n, plural, =1{record} other{records}} are available' =>
        'Невозможно выполнить удаление {n, plural, =1{файла/папки, файл/папка не доступна} few{файлы/папки, файлы/папки не доступы} '
      . 'many{файлов/папок, файлы/папки не доступы не доступны} other{файлов/папок, файлы/папки не доступы не доступны}}.',
    'Successfully deleted {N} {n, plural, =1{record} other{records}}' => 
        'Успешно {n, plural, =1{удалён «<b>1</b>» файл/папка} few{удалены «<b>{N}</b>» файлы/папки} '
      . 'many{удалено «<b>{N}</b>» файлов/папок} other{удалено «<b>{N}</b>» файлов/папок}}.',

    // PreviewForm: сообщения
    'The selected file "{0}" cannot be viewed' => 'Невозможно просмотреть выбранный файл "{0}".',

    // EditForm
    '{edit.title}' => 'Изменение файла "{0}"',
    // EditForm: поля
    'File content' => 'Содержимое файла',
    // EditForm: сообщения / заголовок
    'Saving a file' => 'Сохранение файла',
    // EditForm: сообщения
    'The selected file "{0}" cannot be edited' => 'Невозможно редактировать выбранный файл "{0}".',
    'The file has been successfully modified' => 'Файл успешно изменён.',
    'Error writing to file' => 'Ошибка записи в файл.',

    // Settings
    '{settings.title}' => 'Настройка модуля',
    // Settings: поля
    'Root folder ID' => 'Идентификатор корневой папки',
    'Path to root folder' => 'Базовый путь',
    'Base URL' => 'Базовый URL-адрес',
    'Absolute path to the root folder' => 'Абсолютный путь к корневой папке',
    'Used to preview images and must match the base path' 
        => 'Применяется для предварительного просмотра изображений и должен соответствовать базовому пути',
    'show folders without access' => 'показывать папки без доступа',
    'show VCS files' => 'показывать файлы VCS',
    'show files and folders with a dot' => 'показывать файлы и папки с точкой',
    'Folder tree panel' => 'Панель дерева папок',
    'show folder icons' => 'показывать значки папок',
    'show system folder icons' => 'показывать значки системных папок',
    'show toolbar' => 'показывать панель инструментов',
    'show root folder' => 'показывать корневую папку',
    'show panel' => 'показывать панель',
    'resize panel' => 'изменять размер панели',
    'show arrows' => 'показывать стрелочки',
    'sort folders' => 'сортировать папки',
    'number of folders to expand' => 'количество раскрываемых папок',
    'panel size' => 'размер панели, пкс',
    'Width of the folder tree panel in pixels' => 'Ширина панели дерева папок в пикселях',
    'panel position' => 'положение панели',
    'left' => 'слева',
    'right' => 'справа',
    'Files grid' => 'Сетка отображения файлов',
    'show only files' => 'показывать только файлы',
    'double click on folder/file' => 'двойной клик на папке / файле',
    'show lines between columns' => 'показывать линии между столбцами',
    'show lines between lines' => 'показывать линии между строками',
    'line alternation' => 'чередование строк',
    'show icons' => 'показывать значки',
    'show popup menus' => 'показывать всплывающие меню',
    "Show columns" => 'Показывать столбцы',
    'column "Size"' => 'столбец "Размер"',
    'column "Type"' => 'столбец "Тип"',
    'column "MIME"' => 'столбец "MIME-тип"',
    'column "Permissions"' => 'столбец "Права доступа"',
    'column "Access time"' => 'столбец "Последний доступ"',
    'column "Change time"' => 'столбец "Последнее обновление"',
    'number of files and folders per page' => 'количество элементов на странице',

    // CreateForm
    '{create.folder.title}' => 'Создание папки',
    '{create.file.title}' => 'Создание файла',
    // CreateForm: поля
    'folder name' => 'Название',
    'file name' => 'Название',
    'Create' => 'Создать',
    'Path' => 'Путь',
    // CreateForm: сообщения / заголовки
    'Creation' => 'Создание',
    // CreateForm: сообщения
    'File created successfully' => 'Файл успешно создан.',
    'Folder created successfully' => 'Папка успешно создана.',
    // CreateForm: сообщения / ошибки
    'Error creating file' => 'Ошибка создания файла.',
    'Error creating file "{0}"' => 'Ошибка создания файла "{0}"!',
    'Error creating folder' => 'Ошибка создания папки.',
    'Error creating folder "{0}"' => 'Ошибка создания папки "{0}"!',
    'The specified folder "{0}" does not exist' => 'Указанная папка "{0}" не существует!',
    'The specified folder "{0}" already exists' => 'Указанная папка "{0}" уже существует!',
    'The specified file "{0}" does not exist' => 'Указанный файл "{0}" не существует!',
    'The specified file "{0}" already exists' => 'Указанный файл "{0}" уже существует!',

    // RenameForm
    '{rename.folder.title}' => 'Переименовать папку "{0}"',
    '{rename.file.title}' => 'Переименовать файл "{0}"',
    // RenameForm: поля
    'New name' => 'Новое название',
    'Rename' => 'Переименовать',
    // RenameForm: сообщения / заголовки
    'Renaming' => 'Переименование',
    // RenameForm: сообщения
    'The file was successfully renamed' => 'Файл успешно переименован.',
    'The folder was successfully renamed' => 'Папка успешно переименована.',
    'Error renaming folder' => 'Ошибка в переименовании папки',
    'Error renaming file' => 'Ошибка в переименовании файла',
    // RenameForm: сообщения / ошибки
    'The extension of the new file is incorrect' => 'Неправильно указано расширение нового файла',
    'The new file name is incorrect' => 'Неправильно указано новое имя файла',
    'The new folder name is incorrect' => 'Неправильно указано новое имя папки',
    'A file with the new name "{0}" already exists' => 'Файл с таким именем "{0}" уже существует',
    'A folder with the new name "{0}" already exists' => 'Папка или файл с таким именем "{0}" уже существует',

    // AttributesForm
    '{attributes.folder.title}' => 'Информация о папке "{0}"',
    '{attributes.file.title}' => 'Информация о файле "{0}"',
    // AttributesForm: поля
    'impossible to determine' => 'невозможно определить',
    'dir' => 'папка',
    'file' => 'файл',
    'link' => 'ссылка',
    'unknown' => 'неизвестно',
    'Owner ID' => 'Идент. владельца',
    'Group ID' => 'Идент. группы',
    'Changing a file' => 'Изменение файла',
    'Changing a folder' => 'Изменение папки',
    'Readable' => 'Доступен для чтения',
    'Writable' => 'Доступен для записи',
    'The configuration file' => 'Файл конфигурации',
    'System folder' => 'Системная папка',
    'MIME-type' => 'MIME-тип',
    'Archive type' => 'Тип архива',
    'Files in the archive' => 'Файлов в архиве',
    'Width' => 'Ширина, пкс.',
    'Height' => 'Высота, пкс.',
    'Color' => 'Цветное',
    'yes' => 'да',
    'no' => 'нет',

    // PermissionsForm
    '{permissions.folder.title}' => 'Настройка доступа папке "{0}"',
    '{permissions.file.title}' => 'Настройка доступа файлу "{0}"',
    // PermissionsForm: поля
    'File / folder name' => 'Название файла / папки',
    'Owner permission' => 'Права владельца',
    'Group permission' => 'Групповые права',
    'World permission' => 'Публичные права',
    'Permissions' => 'Права доступа',
    'Read' => 'Чтение',
    'Write' => 'Запись',
    'Execution' => 'Выполнение',
    'Numerical value' => 'Числовое значение',
    // PermissionsForm: панель инструментов
    'Apply' => 'Применить',
    // PermissionsForm: сообщения
    'Unable to determine permissions for "{0}"' => 'Невозможно определить права доступа для "{0}".',
    'Permissions have been successfully set' => 'Права доступа успешно установлены.',
    'For OS Windows, the permissions set do not matter' => 'Для OC Windows устанавливаемые права доступа не имеют значения.',
    'Error setting permissions' => 'Ошибка установки прав доступа',

    // Download
    'The folder or file with the same name "{0}" does not exist' => 'Папка или файл с таким именем "{0}" не существует!',
    'Unable to get file ID' => 'Невозможно получить идентификатор файл!',
    'PHP module "{0}" is not installed' => 'Модуль PHP "{0}" не установлен.',
    'Unable to compress selected files for download' => 'Невозможно сжать выбранные файлы для скачивания.',

    // UploadForm
    '{upload.title}' => 'Загрузка файла',
    // UploadForm: панель инструментов
    'Upload' => 'Загрузить',
    // UploadForm: поля
    'File name' => 'Имя файла',
    '(more details)' => '(подробнее)',
    'The file(s) will be downloaded according to the parameters for downloading resources to the server {0}' 
        => 'Загрузка файла(ов) будет выполнена согласно <em>"параметрам загрузки ресурсов на сервер"</em> {0}',
    // UploadForm: сообшения / заголовок
    'Uploading a file' => 'Загрузка файла',
    // UploadForm: сообшения / текст
    'File uploading error' => 'Ошибка загрузки файла',
    'File uploaded successfully' => 'Файл успешно загружен.',
    'No file selected for upload' => 'Файл для загрузки не выбран!',
    'The downloaded file "{0}" is already on the server, should I replace it?' => 'Загружаемый файл "{0}" уже есть на сервере, заменить его?',

    // CompressForm
    '{compress.title}' => 'Архивирование файлов / папок',
    // CompressForm: панель инструментов
    'Compress' => 'Архивировать',
    // CompressForm: поля
    'Compress' => 'Архивировать',
    'Archive name' => 'Имя архива',
    'Archive type' => 'Тип архива',
    'ZIP format (.zip)' => 'ZIP-архив (.zip)',
    'TAR/ZIP format (.tar.gz)' => 'TAR/ZIP-архив, сжатие Gzip (.tar.gz)',
    'TAR/ZIP format (.tar.bz2)' => 'TAR/ZIP-архив, сжатие Bzip2 (.tar.bz2)',
    'TAR/ZIP format (.tar)' => 'TAR/ZIP-архив (.tar)',
    // CompressForm: сообшения / заголовок
    'Compression' => 'Архивирование',
    // CompressForm: сообшения
    'File / folder compression completed successfully' => 'Архивирование файлов / папок выполнено успешно.',
    'Error in compressing files / folders' => 'Ошибка в архивировании файлов / папок.',
    'No files selected for compression' => 'Нет выбранных файлов для архивирования.',
    'Archive file "{0}" is not writable' => 'Файл архива "{0}" не доступен для записи.',

    // ExtractForm
    '{extract.title}' => 'Извлечение файлов из архива "{0}"',
    // ExtractForm: панель инструментов
    'Extract' => 'Извлечь',
    // ExtractForm: поля
    'To a separate folder' => 'В отдельную папку',
    'To current folder' => 'В текущую папку',
    'Delete archive after extraction' => 'удалить архив после извлечения',
    'Folder name' => 'Имя папки',
    'Where' => 'Куда',
    // ExtractForm: сообшения / заголовок
    'Extraction' => 'Извлечение',
    // ExtractForm: сообшения
    'You only need to select the archive file' => 'Необходимо выбрать только файл архива',
    'Files extracted from archive successfully' => 'Файлы извлечены из архива успешно.',
    'Error extracting files from archive' => 'Ошибка извлечения файлов из архива.',
    'The specified file is not an archive' => 'Указанный файл не является архивом.',
    'Cannot create directory "{0}"' => 'Невозможно создать каталог "{0}".',

    // Copy, Cut
    'Files / folders copied to clipboard' => 'Файлы / папки скопированы в буфер обмена.',
    'Files / folders cut to clipboard' => 'Файлы / папки вырезаны в буфер обмена.',

    // Clipboard: сообшения / заголовок
    'Clipboard' => 'Буфер обмена',
    // Clipboard: сообшения
    'Error copying file from "{0}" to "{1}"' => 'Ошибка копирования файла из "{0}" в "{1}".',
    'Error copying folder from "{0}" to "{1}"' => 'Ошибка копирования папки из "{0}" в "{1}".',
    'Error getting from file / folder ID "{0}"' => 'Ошибка получения из идентификатора "{0}" файла / папки.',
    'Successfully copied "{0}" files / folders' => 'Успешно скопировано "{0}" файлов / папок.',
    'Successfully cut "{0}" files / folders' => 'Успешно вырезано "{0}" файлов / папок.',
    'Cannot paste files where they were copied or cut from' => 'Невозможно вставить файлы туда, откуда они были скопированы или вырезаны.',
    'Error deleting folder "{0}"' => 'Ошибка удаления папки "{0}".',
    'Error deleting file "{0}"' => 'Ошибка удаления файла "{0}".',

    // Run: сообщения / заголовки
    'Action' => 'Действие',
    // Run: сообщения
    'The action on the file / folder was completed successfully' => 'Действие на файлом / папкой выполнено успешно.',
    'Error performing an action on a file / folder' => 'Ошибка выполнения действия на файлом / папкой.',
];
