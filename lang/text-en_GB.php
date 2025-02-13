<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Пакет английской (британской) локализации.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    '{name}'        => 'File manager',
    '{description}' => 'Manage files and folders in a server-side storage',
    '{permissions}' => [
        'any'  => ['Full access', 'File and folder management'],
        'view' => ['View', 'View files and folders'],
        'read' => ['Reading', 'Reading files and folders']
    ],

    // Files: панель инструментов
    'Expand all folders' => 'Expand all folders',
    'Collapse all folders' => 'Collapse all folders',
    'Home' => 'Home',
    'Go up one level' => 'Go up one level',
    'Create folder' => 'Create folder',
    'Create file' => 'Create file',
    'Delete selected folders / files' => 'Delete selected folders / files',
    'Delete' => 'Delete',
    'Refresh' => 'Refresh',
    'Search for folder / file' => 'Search for folder / file',
    'Find' => 'Find',
    'Reset' => 'Reset',
    'Profiling a folder / file' => 'Profiling a folder / file',
    'Select all' => 'Select all',
    'Invert selection' => 'Invert selection',
    'Remove selection' => 'Remove selection',
    'Upload file' => 'Upload file',
    'Download selected folders / files' => 'Download selected folders / files',
    'Download' => 'Download',
    'Archive' => 'Archive',
    'Extract from archive' => 'Extract from archive',
    'Rename' => 'Rename',
    'Edit file' => 'Edit file',
    'Edit' => 'Edit',
    'View file' => 'View file',
    'View' => 'View',
    'Permissions' => 'Permissions',
    'Copy selected folders / files' => 'Copy selected folders / files',
    'Copy' => 'Copy',
    'Cut' => 'Cut',
    'Move selected folders / files to clipboard' => 'Move selected folders / files to clipboard',
    'Paste' => 'Paste',
    'Paste the contents of the buffer into the current folder' => 'Paste the contents of the buffer into the current folder',
    'Information about the selected folder/file' => 'Information about the selected folder / file',
    'Information' => 'Information',
    'Grid' => 'Grid',
    'List' => 'List',
    'Help' => 'Help',
    'Settings' => 'Settings',
    // Files: фильтр
    'Search name' => 'Search name',
    'Search location' => 'Search location',
    'find File' => 'find File',
    'find Path' => 'find Path',
    // Files: столбцы
    'Name' => 'Name',
    'Type' => 'Type',
    'Folder' => 'Folder',
    'File' => 'File',
    'MIME type' => 'MIME type',
    'Size' => 'Size',
    'Access time' => 'Access time',
    'File last accessed time' => 'File last accessed time',
    'Change time' => 'Change time',
    'File last modified time' => 'File last modified time',
    // Files: сообщения / удаление
    'Only one file or folder needs to be selected' => 'Only one file or folder needs to be selected!',
    'You must select a file or folder' => 'You must select a file or folder!',
    'You must select a file' => 'You must select a file!',
    'Are you sure you want to delete the selected files / folders ({0} pcs)? {1}' 
        => 'Are you sure you want to delete the selected files / folders ({0} pcs)? {1}',
    'Are you sure you want to delete the selected files ({0} pcs)? {1}' 
        => 'Are you sure you want to delete the selected files ({0} pcs)? {1}',
    'Are you sure you want to delete the file "{0}"?' => 'Are you sure you want to delete the file "{0}"?',
    'Are you sure you want to delete the selected folders ({0} pcs)? {1}' 
        => 'Are you sure you want to delete the selected folders ({0} pcs)? {1}',
    'Are you sure you want to delete the folder "{0}"?' => 'Are you sure you want to delete the folder "{0}"?',
    'Cannot delete selected files or folders' => 'Cannot delete selected files or folders.',
    // Files: сообщения / удаление
    'The records were partially deleted, from the selected {nSelected} {selected, plural, =1{record} other{records}}, {nDeleted} were deleted, the rest were omitted' => 
        'The records were partially deleted, from the selected «<b>{nSelected}</b>» {selected, plural, =1{record} other{records}}, «<b>{nDeleted}</b>» were deleted, the rest were omitted',
    'Records have been partially deleted, {nDeleted} deleted, {nSkipped} {skipped, plural, =1{record} other{records}} skipped' =>
        'Files/folders were partially deleted, {deleted, plural, =1{deleted «<b>1</b>» file/folder} few{deleted «<b>{nDeleted}</b>» files/folders} '
      . 'many{deleted «<b>{nDeleted}</b>» files/folders} other{deleted «<b>{nDeleted}</b>» files/folders}}, '
      . '{skipped, plural, =1{skipped «<b>1</b>» file/folder} few{skipped «<b>{nSkipped}</b>» files/folders} '
      . 'many{skipped «<b>{nSkipped}</b>» files/folders} other{skipped «<b>{nSkipped}</b>» files/folders}}.',
    'Unable to delete {N} {n, plural, =1{record} other{records}}, no records are available' =>
        'Unable to delete  {n, plural, =1{file/folder, file/folder not accessible} other{«<b>{N}</b>» files/folders, files/folders are not available}}.',
    'Unable to delete {n, plural, =1{record} other{records}}, no {n, plural, =1{record} other{records}} are available' =>
        'Unable to delete {n, plural, =1{file/folder, file/folder not available} few{files/folders, files/folders are not available} '
      . 'many{files/folders, files/folders are not available} other{files/foldes, files/folders are not available}}.',
    'Successfully deleted {N} {n, plural, =1{record} other{records}}' => 
        'Successfully {n, plural, =1{deleted «<b>1</b>» file/folder} few{deleted «<b>{N}</b>» files/folders} '
      . 'many{deleted «<b>{N}</b>» files/folders} other{deleted «<b>{N}</b>» files/folders}}.',

    // PreviewForm: сообщения
    'The selected file "{0}" cannot be viewed' => 'The selected file "{0}" cannot be viewed.',

    // EditForm
    '{edit.title}' => 'Edit file "{0}"',
    // EditForm: поля
    'File content' => 'File content',
    // EditForm: сообщения / заголовок
    'Saving a file' => 'Saving a file',
    // EditForm: сообщения
    'The selected file "{0}" cannot be edited' => 'The selected file "{0}" cannot be edited.',
    'The file has been successfully modified' => 'The file has been successfully modified.',
    'Error writing to file' => 'Error writing to file.',

    // Settings
    '{settings.title}' => 'Module settings',
    // Settings: поля
    'Root folder ID' => 'Root folder ID',
    'Path to root folder' => 'Path to root folder',
    'Base URL' => 'Base URL',
    'Absolute path to the root folder' => 'Absolute path to the root folder',
    'Used to preview images and must match the base path' 
        => 'Used to preview images and must match the base path',
    'show folders without access' => 'show folders without access',
    'show VCS files' => 'show VCS files',
    'show files and folders with a dot' => 'show files and folders with a dot',
    'Folder tree panel' => 'Folder tree panel',
    'show folder icons' => 'show folder icons',
    'show system folder icons' => 'show system folder icons',
    'show toolbar' => 'show toolbar',
    'show root folder' => 'show root folder',
    'show panel' => 'show panel',
    'resize panel' => 'resize panel',
    'show arrows' => 'show arrows',
    'sort folders' => 'sort folders',
    'number of folders to expand' => 'number of folders to expand',
    'panel size' => 'panel size, px',
    'Width of the folder tree panel in pixels' => 'idth of the folder tree panel in pixels',
    'panel position' => 'panel position',
    'left' => 'left',
    'right' => 'right',
    'Files grid' => 'Files grid',
    'show only files' => 'show only files',
    'double click on folder/file' => 'double click on folder/file',
    'show lines between columns' => 'show lines between columns',
    'show lines between lines' => 'show lines between lines',
    'line alternation' => 'line alternation',
    'show icons' => 'show icons',
    'show popup menus' => 'show popup menus',
    'Show columns' => 'Show columns',
    'column "Size"' => 'column "Size"',
    'column "Type"' => 'column "Type"',
    'column "MIME"' => 'column "MIME"',
    'column "Permissions"' => 'column "Permissions"',
    'column "Access time"' => 'column "Access time"',
    'column "Change time"' => 'column "Change time"',
    'number of files and folders per page' => 'number of files and folders per page',

    // CreateForm
    '{create.folder.title}' => 'Create folder',
    '{create.file.title}' => 'Create file',
    // CreateForm: поля
    'folder name' => 'Name',
    'file name' => 'Name',
    'Create' => 'Create',
    'Path' => 'Path',
    // CreateForm: сообщения / заголовки
    'Creation' => 'Creation',
    // CreateForm: сообщения
    'File created successfully' => 'File created successfully.',
    'Folder created successfully' => 'Folder created successfully.',
    // CreateForm: сообщения / ошибки
    'Error creating file' => 'Error creating file.',
    'Error creating file "{0}"' => 'Error creating file "{0}"!',
    'Error creating folder' => 'Error creating folder.',
    'Error creating folder "{0}"' => 'Error creating folder "{0}"!',
    'The specified folder "{0}" does not exist' => 'The specified folder "{0}" does not exist!',
    'The specified folder "{0}" already exists' => 'The specified folder "{0}" already exists!',
    'The specified file "{0}" does not exist' => 'The specified file "{0}" does not exist!',
    'The specified file "{0}" already exists' => 'The specified file "{0}" already exists!',

    // RenameForm
    '{rename.folder.title}' => 'Rename folder "{0}"',
    '{rename.file.title}' => 'Rename file "{0}"',
    // RenameForm: поля
    'New name' => 'New name',
    'Rename' => 'Rename',
    // RenameForm: сообщения / заголовки
    'Renaming' => 'Renaming',
    // RenameForm: сообщения
    'The file was successfully renamed' => 'The file was successfully renamed.',
    'The folder was successfully renamed' => 'The folder was successfully renamed.',
    'Error renaming folder' => 'Error renaming folder',
    'Error renaming file' => 'Error renaming file',
    // RenameForm: сообщения / ошибки
    'The extension of the new file is incorrect' => 'The extension of the new file is incorrect',
    'The new file name is incorrect' => 'The new file name is incorrect',
    'The new folder name is incorrect' => 'The new folder name is incorrect',
    'A file with the new name "{0}" already exists' => 'A file with the new name "{0}" already exists',
    'A folder with the new name "{0}" already exists' => 'A folder with the new name "{0}" already exists',
    'Cannot rename file or folder "{0}"' => 'Cannot rename file or folder "{0}".',
    'The selected file or folder does not exist' => 'The selected file or folder does not exist.',

    // AttributesForm
    '{attributes.folder.title}' => 'Folder information "{0}"',
    '{attributes.file.title}' => 'File information "{0}"',
    // AttributesForm: поля
    'impossible to determine' => 'impossible to determine',
    'dir' => 'folder',
    'file' => 'file',
    'link' => 'link',
    'unknown' => 'unknown',
    'Owner ID' => 'Owner ID',
    'Group ID' => 'Group ID',
    'Changing a file' => 'Changing a file',
    'Changing a folder' => 'Changing a folder',
    'Readable' => 'Readable',
    'Writable' => 'Writable',
    'The configuration file' => 'The configuration file',
    'System folder' => 'System folder',
    'MIME-type' => 'MIME-type',
    'Archive type' => 'Archive type',
    'Files in the archive' => 'Files in the archive',
    'Width' => 'Width, px',
    'Height' => 'Height?, px',
    'Color' => 'Color',
    'yes' => 'yes',
    'no' => 'no',

    // PermissionsForm
    '{permissions.folder.title}' => 'Configuring folder access "{0}"',
    '{permissions.file.title}' => 'Configuring file access "{0}"',
    // PermissionsForm: поля
    'File / folder name' => 'File / folder name',
    'Owner permission' => 'Owner permission',
    'Group permission' => 'Group permission',
    'World permission' => 'World permission',
    'Permissions' => 'Permissions',
    'Read' => 'Read',
    'Write' => 'Write',
    'Execution' => 'Execution',
    'Numerical value' => 'Numerical value',
    // PermissionsForm: панель инструментов
    'Apply' => 'Apply',
    // PermissionsForm: сообщения
    'Unable to determine permissions for "{0}"' => 'Unable to determine permissions for "{0}".',
    'Permissions have been successfully set' => 'Permissions have been successfully set.',
    'For OS Windows, the permissions set do not matter' => 'For OS Windows, the permissions set do not matter.',
    'Error setting permissions' => 'Error setting permissions',

    // Download
    'The folder or file with the same name "{0}" does not exist' => 'The folder or file with the same name "{0}" does not exist!',
    'Unable to get file ID' => 'Unable to get file ID!',
    'PHP module "{0}" is not installed' => 'PHP module "{0}" is not installed.',
    'Unable to compress selected files for download' => 'Unable to compress selected files for download.',

    // UploadForm
    '{upload.title}' => 'File upload',
    // UploadForm: панель инструментов
    'Upload' => 'Upload',
    // UploadForm: поля
    'File name' => 'File name',
    '(more details)' => '(more details)',
    'The file(s) will be downloaded according to the profile "{0}" of the media folder' 
        => 'The file(s) will be downloaded according to the profile <em>"{0}"</em> of the media folder {1}',
    'The file(s) will be downloaded according to the parameters for downloading resources to the server {0}' 
        => 'The file(s) will be downloaded according to the <em>"parameters for downloading resources to the server"</em> {0}' ,
    // UploadForm: сообшения / заголовок
    'Uploading a file' => 'Uploading a file',
    // UploadForm: сообшения / текст
    'File uploading error' => 'File uploading error',
    'File uploaded successfully' => 'File uploaded successfully.',
    'No file selected for upload' => 'No file selected for upload!',
    'The downloaded file "{0}" is already on the server, should I replace it?' 
        => 'The downloaded file "{0}" is already on the server, should I replace it?',
    'Unable to upload file to current folder' => 'Unable to upload file to current folder.',
    'Continue file upload?' => 'Continue file upload?',

    // CompressForm
    '{compress.title}' => 'Compress file / fodler',
    // CompressForm: панель инструментов
    // CompressForm: поля
    'Compress' => 'Compress',
    'Archive name' => 'Archive name',
    'Archive type' => 'Archive type',
    'ZIP format (.zip)' => 'ZIP format (.zip)',
    'TAR/ZIP format (.tar.gz)' => 'TAR/ZIP format (.tar.gz)',
    'TAR/ZIP format (.tar.bz2)' => 'TAR/ZIP format (.tar.bz2)',
    'TAR/ZIP format (.tar)' => 'TAR/ZIP format (.tar)',
    // CompressForm: сообшения / заголовок
    'Compression' => 'Compression',
    // CompressForm: сообшения
    'File / folder compression completed successfully' => 'File / folder compression completed successfully.',
    'Error in compressing files / folders' => 'Error in compressing files / folders.',
    'No files selected for compression' => 'No files selected for compression.',
    'Archive file "{0}" is not writable' => 'Archive file "{0}" is not writable.',
    'Archive file "{0}" already exists' => 'Archive file "{0}" already exists.',
    'Unable to compress files to current folder' => 'Unable to compress files to current folder.',

    // ExtractForm
    '{extract.title}' => 'Extract files from archive "{0}"',
    // ExtractForm: панель инструментов
    'Extract' => 'Extract',
    // ExtractForm: поля
    'To a separate folder' => 'To a separate folder',
    'To current folder' => 'To current folder',
    'Delete archive after extraction' => 'Delete archive after extraction',
    'Folder name' => 'Folder name',
    'Where' => 'Where',
    // ExtractForm: сообшения / заголовок
    'Extraction' => 'Extraction',
    // ExtractForm: сообшения
    'You only need to select the archive file' => 'You only need to select the archive file',
    'Files extracted from archive successfully' => 'Files extracted from archive successfully.',
    'Error extracting files from archive' => 'Error extracting files from archive.',
    'The specified file is not an archive' => 'The specified file is not an archive.',
    'Cannot create directory "{0}"' => 'Cannot create directory "{0}".',
    'You only need to select the archive file' => 'You only need to select the archive file.',

    // Copy, Cut
    'Files / folders copied to clipboard' => 'Files / folders copied to clipboard.',
    'Files / folders cut to clipboard' => 'Files / folders cut to clipboard.',

    // Clipboard: сообшения / заголовок
    'Clipboard' => 'Clipboard',
    // Clipboard: сообшения
    'Error copying file from "{0}" to "{1}"' => 'Error copying file from "{0}" to "{1}".',
    'Error copying folder from "{0}" to "{1}"' => 'Error copying folder from "{0}" to "{1}".',
    'Error getting from file / folder ID "{0}"' => 'Error getting from file / folder ID "{0}".',
    'Successfully copied "{0}" files / folders' => 'Successfully copied "{0}" files / folders.',
    'Successfully cut "{0}" files / folders' => 'Successfully cut "{0}" files / folders.',
    'Cannot paste files where they were copied or cut from' => 'Cannot paste files where they were copied or cut from.',
    'Error deleting folder "{0}"' => 'Error deleting folder "{0}".',
    'Error deleting file "{0}"' => 'Error deleting file "{0}".',

    // Run: сообщения / заголовки
    'Action' => 'Action',
    // Run: сообщения
    'The action on the file / folder was completed successfully' => 'The action on the file / folder was completed successfully.',
    'Error performing an action on a file / folder' => 'Error performing an action on a file / folder.'
];