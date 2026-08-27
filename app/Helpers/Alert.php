<?php

namespace App\Helpers;

use Livewire\Component;

class Alert
{
    const EVENT_INFO = 'SwalInfo';

    const EVENT_CONFIRMATION = 'SwalConfirm';

    const EVENT_INFORMATION = 'SwalInformation';

    const EVENT_CONSOLE_LOG = 'ConsoleLog';

    const EVENT_LOADING = 'SwalLoading';

    const ICON_QUESTION = 'question';

    const ICON_ERROR = 'error';

    const ICON_SUCCESS = 'success';

    const ICON_WARNING = 'warning';

    const ICON_INFO = 'info';

    public static function success(Component $component, $title, $message)
    {
        $component->dispatch(self::EVENT_INFO, self::ICON_SUCCESS, $title, $message);
    }

    public static function fail(Component $component, $title, $message)
    {
        $component->dispatch(self::EVENT_INFO, self::ICON_ERROR, $title, $message);
    }

    public static function confirmation(
        Component $component,
        $icon,
        $title,
        $message,
        $eventConfirmName,
        $eventCancelName,
        $confirmText = 'Iya',
        $cancelText = 'Tidak',
    ) {
        $component->dispatch(self::EVENT_CONFIRMATION, $icon, $title, $message, $confirmText, $cancelText, $eventConfirmName, $eventCancelName);
    }

    public static function information(
        Component $component,
        $title,
        $timer = 1500,
    ) {
        $component->dispatch(self::EVENT_INFORMATION, self::ICON_SUCCESS, $title, $timer);
    }

    public static function loading(
        Component $component,
        $title,
    ) {
        $component->dispatch(self::EVENT_LOADING, $title);
    }

    public static function consoleLog(
        Component $component,
        $message,
    ) {
        $component->dispatch(self::EVENT_CONSOLE_LOG, $message);
    }
}
