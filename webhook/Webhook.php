<?php

namespace ZOTORN\Webhook;

interface Webhook
{
    /**
     *
     * @param string $event
     * @param string $storagePath
     * @return bool
     */
    function handleEvent(string $event, string $storagePath): bool;
}
