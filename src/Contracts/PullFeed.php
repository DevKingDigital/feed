<?php

namespace Michaeljennings\Feed\Contracts;

interface PullFeed
{
    /**
     * Get all of the unread notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pull($notifiable);

    /**
     * Get all of the read notifications for the provided notifiable members.
     *
     * @param array|Notifiable $notifiable
     * @return mixed
     */
    public function pullRead($notifiable);

    /**
     * Set the amount to limit the feed by.
     *
     * @param int|string $limit
     * @return $this
     */
    public function limit($limit);

    /**
     * Set the amount to offset the feed by.
     *
     * @param int|string $offset
     * @return $this
     */
    public function offset($offset);
}