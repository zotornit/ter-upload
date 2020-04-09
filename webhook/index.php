<?php

require 'Webhook.php';
require 'GitHubWebhook.php';
require 'config.php';

try {

    // Currently only github is supported. However should not be difficult to add your own custom webhook.
    $hookStack[] = new \ZOTORN\Webhook\GitHubWebhook($WH_GITHUB_SECRET);

    foreach ($hookStack as $hook) {

        /*
         * Handle push tag event
         */
        if ($hook->handleEvent('push_tag', $WH_WORK_DIR)) {
            continue;
        }

//        if ($hook->handleEvent('your_custom_event')) {
//            continue;
//        }

    }

} catch (Exception $e) {
    error_log($e->getMessage());
    exit(1);
}

echo "Think ...";













