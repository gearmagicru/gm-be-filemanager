<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\FileManager\Model;

use Gm;
use SplFileInfo;
use Gm\Helper\Json;
use Gm\Config\Mimes;
use Gm\Panel\Data\Model\FilesGridModel;

/**
 * Модель данных сетки / списка отображения файлов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class Files extends FilesGridModel
{
    /**
     * @var int Отображение в виде сетки.
     */
    public const GRID_VIEW = 'grid';

    /**
     * @var int Отображение в виде списка.
     */
    public const LIST_VIEW = 'list';

    /**
     * {@inheritdoc}
     */
    public array $attributes = [
        self::ATTR_ID     => 'id',
        self::ATTR_NAME   => 'name',
        self::ATTR_RNAME  => 'relName',
        self::ATTR_TYPE   => 'type',
        self::ATTR_ACTIME => 'accessTime',
        self::ATTR_CHTIME => 'changeTime',
        self::ATTR_PERMS  => 'permissions',
        self::ATTR_SIZE   => 'size',
        self::ATTR_MIME   => 'mimeType',
    ];

    /**
     * {@inheritdoc}
     */
    public array $defaultOrder = ['type' => self::SORT_DESC];

    /**
     * Вид отображения файлов и папок.
     * 
     * @var string
     */
    public string $view;

    /**
     * Параметр передаваемый HTTP-запросом для отображения файлов и папок.
     * 
     * Параметр передаётся с помощью метода POST и определяется {@see BaseGridModel::defineView()}.
     * Если значение параметра `false`, тогда будет применяться значение {@see BaseGridModel::$defaultView}.
     * 
     * @var string|false
     */
    public string|false $viewParam = 'view';

    /**
     * Определяет, что парамтер $view получен из HTTP-запроса.
     * 
     * @see BaseGridModel::defineView()
     * 
     * @var bool
     */
    protected bool $hasView = false;

    /**
     * Значение отображения файлов и папок по умолчанию.
     * 
     * Используется в том случаи, если значение параметра {@see BaseGridModel::$viewParam} 
     * отсутствует в HTTP-запросе.
     * 
     * @var string
     */
    public $defaultView = self::GRID_VIEW;

    /**
     * @var Mimes
     */
    protected Mimes $mimes;

    /**
     * Значки файлов
     * 
     * @var array
     */
    protected array $icons = [];

    /**
     * Перекрытие значка папок.
     * 
     * @var array
     */
    protected array $overlays = [];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->settings = $this->getSettings();

        parent::init();

        // настройки
        if ($this->settings) {
            $this->showVCSFiles  = $this->settings->getValue('showVCSFiles', $this->showVCSFiles);
            $this->showDotFiles  = $this->settings->getValue('showDotFiles', $this->showDotFiles);
            $this->showOnlyFiles = $this->settings->getValue('showOnlyFiles', $this->showOnlyFiles);
            $this->usePermsAttr  = $this->settings->getValue('showPermissionsColumn', $this->usePermsAttr);
            $this->useSizeAttr   = $this->settings->getValue('showSizeColumn', $this->useSizeAttr);
            $this->showUnreadableDirs = $this->settings->getValue('showUnreadableDirs', $this->showUnreadableDirs);
            $this->useAccessTimeAttr  = $this->settings->getValue('showAccessTimeColumn', $this->useAccessTimeAttr);
            $this->useChangeTimeAttr  = $this->settings->getValue('showChangeTimeColumn', $this->useChangeTimeAttr);
        }

        $this->view     = $this->defineView();
        $this->icons    = $this->settings->getValue('icons', []);
        $this->overlays = $this->settings->getValue('overlays', []);

        $this
            ->on(self::EVENT_AFTER_DELETE, function ($someRows, $result, $message) {
                /** @var \Gm\Panel\Http\Response\JsongMetadata $meta */
                $meta = $this->response()->meta;
                // всплывающие сообщение
                $meta->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                // обновляем список файлов
                $meta->cmdComponent($this->module->viewId('view'), 'reload');
            })
            ->on(self::EVENT_AFTER_SET_FILTER, function ($filter) {
                $this->response()
                    ->meta
                        ->cmdComponent($this->module->viewId('view'), 'reload');
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'filter' => [
                'name' => ['operator' => '='],
                'path' => ['operator' => '='],
                'type' => ['operator' => '=']
            ]
        ];
    }

    /**
     * {@inheritdoc}
     * 
     * Т.к. указанный путь из HTTP-запроса может быть идентификатор корневой папки 
     * из дерева, то определяем его как ''. 
     * Избавляемся от идентификатора для получения абсолютного пути через defineRealPath().
     */
    protected function definePath(): string
    {
        $path = parent::definePath();

        if ($this->settings && $this->settings->folderRootId === $path) {
            $path = '';
        }
        return $path;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineRowsId(): array
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->rowsId)) {
            return $this->rowsId;
        }

        // если запрещено получать значение из HTTP-запроса
        if ($this->rowsIdParam === false) {
            return [];
        }

        $rowsId = Gm::$app->request->getPost($this->rowsIdParam);
        if ($rowsId) {
            $rowsId = Json::tryDecode($rowsId);
            if (Json::error()) {
                // TODO: debug
                $rowsId = [];
            }
        } else
            return [];
        // параметр был получен из запроса
        $this->hasRowsId = true;
        return $rowsId;
    }

    /**
     * Определяет вид отображения файлов и папок.
     * 
     * - если значение указано в параметрах конфигурации, тогда возвратит {@see BaseGridModel::$view};
     * - если не указан параметр запроса или сам параметр отсутствует в запросе, 
     * тогда возвратит {@see BaseGridModel::$defaultView};
     * - если значение параметра является не допустимым, 
     * тогда возвратит {@see BaseGridModel::$defaultView}.
     * 
     * @see BaseGridModel::$view
     * 
     * @return int
     */
    protected function defineView(): string
    {
        // если значение указано в параметрах конфиграции
        if (isset($this->view)) {
            return $this->view;
        }

        // если запрещено получать значение из HTTP-запроса
        if ($this->viewParam === false) {
            return $this->defaultView;
        }

        $view = Gm::$app->request->getPost($this->viewParam, null);
        if ($view === null) {
            return $this->defaultView;
        }
        return $view;
    }

    /**
     * {@inheritdoc}
     */
    public function getSafePath(string $path): false|string
    {
        return $this->module->getSafePath($path);
    }

    /**
     * @var string
     */
    protected string $url;

    /**
     * Возвращает URL-адрес для выбранной из дерева папки (текущий путь).
     *
     * @return string
     */
    public function getUrl(): string
    {
        if (!isset($this->url)) {
            $this->url = $this->module->getSafeUrl($this->path);
        }
        return $this->url;
    }

    /**
     * @var string
     */
    protected string $iconUrl;

    /**
     * Возвращает URL-адрес к значкам файлов.
     *
     * @return string
     */
    public function getIconUrl(): string
    {
        if (!isset($this->iconUrl)) {
            $this->iconUrl = $this->module->getFileIconsUrl() . $this->view . '/';
        }
        return $this->iconUrl;
    }

    /**
     * @var string
     */
    protected string $overlaysUrl;

    /**
     * Возвращает URL-адрес к перекрытием значков папок.
     *
     * @return string
     */
    public function getOverlaysUrl(): string
    {
        if (!isset($this->overlaysUrl)) {
            $this->overlaysUrl = $this->module->getFileOverlaysUrl();
        }
        return $this->overlaysUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFetchRows(): void
    {
        $this->mimes = new Mimes();
        $this->getUrl();
        $this->getIconUrl();
        $this->getOverlaysUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRow(array $row, SplFileInfo $file): array
    {
        $row = parent::fetchRow($row, $file);

        $isDir  = $file->isDir();
        // только имя файла
        $name = $row['name'];

        $row['preview'] = null;
        $row['overlay'] = null;
        $row['icon'] = $this->iconUrl . $row['type'] . '.svg';
        $row['isFolder'] = $isDir;
        $row['isImage'] = false;
        $row['isArchive'] = false;
        $row['popupMenuItems'] = [
            [0, $isDir ? 'disabled' : 'enabled'], // для папки запретить просмотр
            [1, $isDir ? 'disabled' : 'enabled'] // для папки запретить редактирование
        ];

        // если файл
        if (!$isDir) {
            /** @var string $extension Расширение файла */
            $extension = strtolower($file->getExtension());
            $row['icon'] = $this->iconUrl . ($this->icons[$extension] ?? 'file') . '.svg';

            // если файл - изображение
            $row['isImage'] = $this->mimes->exists($extension, null, 'image');
            if ($row['isImage']) {
                $row['preview'] = $this->url . '/' . $name;
            }
            // если файл - архив
            $row['isArchive'] = $this->mimes->exists($extension, null, 'archive');
        // если папка
        } else {
            // если список
            if ($this->view === self::LIST_VIEW) {
                // есть ли есть перекрытие значка папки
                if (isset($this->overlays[$name])) {
                    $row['overlay'] = $this->overlaysUrl . '/' . $this->overlays[$name] . '.svg';
                }
            }
        }

        if ($this->view === self::GRID_VIEW) {
            $row['popupMenuTitle'] = '<img width="13px" src="'  . $row['icon'] . '" align="absmiddle"> ' . $name;
        }
        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRowsQuery($builder, array $filter): bool
    {
        // название папки или файла
        $name = $filter['name'] ?? '';
        // место поиска
        $path = $filter['path'] ?? '';
        if ($path) {
            $path = $this->getSafePath($path);
        }

        // если указано, что искать
        if ($name && $path) {
            $builder->in($path);
            // вид поиска
            $type = $filter['type'] ?? 'file';
            // если папка
            if ($type === 'folder') {
                $builder->directories()->name($name);
            // если файл
            } else
                $builder->files()->name($name);
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMessage(bool $someRows, int $result): array
    {
        $type     = 'accept';
        $message  = '';
        $selected = $this->getSelectedCount();
        $missed   = $selected - $result;
        // файлы / папки удалены
        if ($result > 0) {
            // файлы / папки удалены частично
            if ($missed > 0) {
                $message = $this->deleteMessageText(
                    'partiallySome',
                    [
                        'deleted' => $result, 'nDeleted' => $result,
                        'selected' => $selected, 'nSelected' => $selected
                    ]
                );
                $type = 'warning';
            // файлы / папки удалены полностью
            } else
                $message = $this->deleteMessageText(
                    'successfullySome',
                    ['n' => $result, 'N' => $result]
                );
        // файлы / папки не удалены
        } else {
            $message = $this->deleteMessageText(
                'unableSome',
                ['n' => $selected, 'N' => $selected]
            );
            $type = 'error';
        }
        return [
            'selected' => $selected, // количество выделенных файлов / папок
            'deleted'  => $result, // количество удаленных файлов / папок
            'missed'   => $missed, // количество пропущенных файлов / папок
            'success'  => $missed == 0, // успех удаления файлов / папок
            'message'  => $message, // сообщение
            'title'    => Gm::t(BACKEND, 'Deletion'), // загаловок сообщения
            'type'     => $type // тип сообщения
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteMessageText(string $type, array $params): string
    {
        switch ($type) {
            // выбранные файлы / папки удалены частично
            case 'partiallySome':
                return $this->module->t(
                    'The records were partially deleted, from the selected {nSelected} {selected, plural, =1{record} other{records}}, {nDeleted} were deleted, the rest were omitted',
                    $params
                );
            // выбранные файлы / папки удалены полностью
            case 'successfullySome':
                return $this->module->t(
                    'Successfully deleted {N} {n, plural, =1{record} other{records}}',
                    $params
                );
            // выбранные файлы / папки не удалены
            case 'unableSome':
                return $this->module->t(
                    'Unable to delete {N} {n, plural, =1{record} other{records}}, no records are available',
                    $params
                );
            // все файлы / папки удалены частично
            case 'partiallyAll':
                return $this->module->t(
                    'Records have been partially deleted, {nDeleted} deleted, {nSkipped} {skipped, plural, =1{record} other{records}} skipped',
                    $params
                );
            // все файлы / папки удалены полностью
            case 'successfullyAll':
                return $this->module->t(
                    'Successfully deleted {N} {n, plural, =1{record} other{records}}',
                    $params
                );
            // все выбранные файлы / папки не удалены
            case 'unableAll':
                return $this->module->t(
                    'Unable to delete {n, plural, =1{record} other{records}}, no {n, plural, =1{record} other{records}} are available',
                    $params
                );
            default:
                return '';
        }
    }
}
