<?php

declare(strict_types=1);

namespace Osen\Updater\Notifications;

use Osen\Updater\Events\UpdateAvailable;
use Osen\Updater\Events\UpdateFailed;
use Osen\Updater\Events\UpdateSucceeded;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;

final class EventHandler
{
    /** @var Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen($this->allEventClasses(), function ($event) {
            $notification = $this->determineNotification($event);
            $notifiable = $this->determineNotifiable();
            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('updater.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event): Notification
    {
        $eventName = class_basename($event);

        $notificationClass = collect($this->config->get('updater.notifications.notifications'))
            ->keys()
            ->first(function ($notificationClass) use ($eventName) {
                $notificationName = class_basename($notificationClass);

                return $notificationName === $eventName;
            });

        if (! $notificationClass) {
            throw new Exception('Notification could not be sent.');
        }

        return app($notificationClass)->setEvent($event);
    }

    protected function allEventClasses(): array
    {
        return [
            UpdateAvailable::class,
            UpdateSucceeded::class,
            UpdateFailed::class,
        ];
    }
}
