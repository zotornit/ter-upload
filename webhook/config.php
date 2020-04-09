<?php

/*
 * Adjust to your needs
 * This configurations are ignored when the corresponding ENV VAR is set. Usually when running in a docker environment
 */
$config = [
    /**
     *
     */
    'WH_GITHUB_SECRET' => '',
    /**
     * The folder where the incoming webhooks are temporary stored.
     */
    'WH_WORK_DIR' => ''
];

/*
 * DO NOT EDIT BELOW
 */

$WH_WORK_DIR = getenv('WH_WORK_DIR');
$WH_WORK_DIR = (is_string($WH_WORK_DIR) && !empty($WH_WORK_DIR)) ? $WH_WORK_DIR : $config['WH_WORK_DIR'];

$WH_GITHUB_SECRET = getenv('WH_GITHUB_SECRET');
$WH_GITHUB_SECRET = (is_string($WH_GITHUB_SECRET) && !empty($WH_GITHUB_SECRET)) ? $WH_GITHUB_SECRET : $config['WH_GITHUB_SECRET'];
