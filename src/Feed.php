<?php

namespace Michaeljennings\Feed;

use Michaeljennings\Feed\Contracts\Notifiable;
use Michaeljennings\Feed\Contracts\Notification;
use Michaeljennings\Feed\Contracts\PullFeed;
use Michaeljennings\Feed\Contracts\PushFeed;
use Michaeljennings\Feed\Contracts\Repository;
use Michaeljennings\Feed\Events\NotificationAdded;

class Feed implements PushFeed, PullFeed
{
    /**
     * The amount to limit the feed by.
     *
     * @var int|string|null
     */
    protected $limit = null;

    /**
     * The amount to offset the feed by.
     *
     * @var int|string|null
     */
    protected $offset = null;

    /**
     * The notification repository implementation.
     *
     * @var Repository
     */
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Push the provided notification to the notifiable members.
     *
     * @param string|array     $notification
     * @param array|Notifiable $notifiable
     */
    public function push($notification, $notifiable)
    {
        if ( ! is_array($notifiable)) {
            $notifiable = [$notifiable];
        }

        if (is_string('notification')) {
            $notification = ['body' => $notification];
        }

        foreach ($notifiable as $toBeNotified) {
            $notification = $this->makeNotification($notification);

            $this->pushNotification($notification, $toBeNotified);
        }
    }

    /**
     * Push a notification to a member.
     *
     * @param string|array $notification
     * @param Notifiable   $notifiable
     */
    protected function pushNotification($notification, Notifiable $notifiable)
    {
        $notifiable->notifications()->save($notification);

        event(new NotificationAdded($notification, $notifiable));
    }

    /**
     * Get all of the unread notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pull($notifiable)
    {
        if ( ! is_array($notifiable)) {
            $notifiable = func_get_args();
        }

        $types = array_map(function ($notifiable) {
            return get_class($notifiable);
        }, $notifiable);

        $ids = array_map(function ($notifiable) {
            return $notifiable->getKey();
        }, $notifiable);

        return $this->repository->getNotifications($types, $ids, $this->limit, $this->offset);
    }

    /**
     * Get all of the read notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pullRead($notifiable)
    {
        if ( ! is_array($notifiable)) {
            $notifiable = func_get_args();
        }

        $types = array_map(function ($notifiable) {
            return get_class($notifiable);
        }, $notifiable);

        $ids = array_map(function ($notifiable) {
            return $notifiable->getKey();
        }, $notifiable);

        return $this->repository->getReadNotifications($types, $ids, $this->limit, $this->offset);
    }

    /**
     * Set the amount to limit the feed by.
     *
     * @param int|string $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set the amount to offset the feed by.
     *
     * @param int|string $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Mark the provided notification as read.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function read(Notification $notification)
    {
        return $this->repository->read($notification);
    }

    /**
     * Alias for the read function.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function markAsRead(Notification $notification)
    {
        return $this->read($notification);
    }

    /**
     * Mark the provided notification as unread.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function unread(Notification $notification)
    {
        return $this->repository->unread($notification);
    }

    /**
     * Alias for the unread function.
     *
     * @param Notification $notification
     * @return mixed
     */
    public function markAsUnread(Notification $notification)
    {
        return $this->unread($notification);
    }

    /**
     * Create a new notification.
     *
     * @param array $notification
     * @return Notifications\Notification
     */
    protected function makeNotification(array $notification)
    {
        if (is_string($notification)) {
            $notification = ['body' => $notification];
        }

        return $this->repository->newNotification($notification);
    }
}