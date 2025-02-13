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
use Gm\Panel\Data\Model\FormModel;

/**
 * Базовая модель файла.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\FileManager\Model
 * @since 1.0
 */
class FileModel extends FormModel
{
    /** @var string Тип элемента "Папка" */
    public const TYPE_FOLDER = 'folder';

    /** @var string Тип элемента "Файл" */
    public const TYPE_FILE = 'file';

    /**
     * @var string Событие, возникшее перед выполненим действия на файлом / папкой.
     */
    public const EVENT_BEFORE_RUN = 'beforeRun';

    /**
     * @var string Событие, возникшее после выполнения действия на файлом / папкой.
     */
    public const EVENT_AFTER_RUN = 'afterRun';

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): mixed
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function get(mixed $identifier = null): ?static
    {
        return null;
    }

    /**
     * Выполняет обновление записи.
     * 
     * @param bool $useValidation Использовать проверку атрибутов (по умолчанию `false`).
     * @param array $attributes Имена атрибутов с их значениями, если не указаны - будут 
     * задействованы атрибуты записи (по умолчанию `null`).
     * 
     * @return bool Если `false`, ошибка выполнения запроса.
     */
    public function run(bool $useValidation = false, array $attributes = null): bool
    {
        if ($useValidation && !$this->validate($attributes)) {
            return false;
        }
        return $this->runProcess($attributes);
    }

    /**
     * Возвращает локализованные сообщения в виде пар "ключ - значение".
     * 
     * Ключ применяют {@see FormModel::saveMessage()} и {@see FormModel::deleteMessage()}
     * для формирования сообщений на действие над записью.
     *
     * @return array
     */
    protected function getActionMessages(): array
    {
        return [
            'titleRun'        => $this->module->t('Action'),
            'msgSuccessRun'   => $this->module->t('The action on the file / folder was completed successfully'),
            'msgUnsuccessRun' => $this->module->t('Error performing an action on a file / folder')
        ];
    }

    /**
     * Возвращает сообщение полученное при сохранении записи
     * события {@see EVENT_AFTER_SAVE} метода {@see afterSave()}.
     *
     * @param bool $isInsert Если true, метод вызывается при вставке записи, иначе
     *     при обновлении записи.
     * @param int $result Если результат больше чем 0, запись обновлена или добавлена.
     * 
     * @return array Сообщение имеет вид:
     *     [
     *         "success" => true,
     *         "message" => "Record successfully added",
     *         "title"   => "Adding record",
     *         "type"    => "accept"
     *     ]
     */
    public function runMessage($result): array
    {
        $messages = $this->getActionMessages();
        if ($result)
            $message = $messages['msgSuccessRun'];
        else
            $message = $messages['msgUnsuccessRun'];
        return [
            'success'  => $result, // успех измнения записи
            'message'  => $message, // сообщение
            'title'    => $messages['titleRun'], // заголовок сообщения
            'type'     => $result ? 'accept' : 'error' // тип сообщения
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRun(): bool
    {
        $this->trigger(self::EVENT_BEFORE_RUN);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterRun(array $attributes = null, mixed $result = null): void
    {
        /** @var bool|int $result */
        $this->trigger(
            self::EVENT_AFTER_RUN,
            [
                'attributes' => $attributes,
                'result'     => $result,
                'message'    => $this->lastEventMessage = $this->runMessage($result)
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcess(array $attributes = null): bool
    {
        if (!$this->beforeRun()) {
            return false;
        }

        // возвращает атрибуты без псевдонимов (если они были указаны)
        $unmasked = $this->unmaskedAttributes($this->attributes);

        // изменение записи
        $this->result = $this->runFile($unmasked);
        $this->setOldAttributes($this->attributes);
        $this->afterRun($unmasked, $this->result);
        return $this->result;
    }

    /**
     * updateFile
     * 
     * @param array $attributes
     * 
     * @return bool
     */
    public function runFile(array $attributes): bool
    {
        return true;
    }
}
