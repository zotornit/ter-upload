<?php

/*
 * This script is meant to run from command line
 * It accepts the following args
 *      extension_dir - directory of the extension - overruled by TYPO3_EXTENSION_DIR env var
 *      upload_comment - last upload comment, overruled by TYPO3_UPLOAD_COMMENT / TYPO3_UPLOAD_COMMENT_FILE env vars
 *      upload_comment_file - file with the last upload comment - overrules upload_comment arg, overruled by TYPO3_UPLOAD_COMMENT_FILE env var
 *      user - typo3 username for typo3.org - overruled by TYPO3_USER env var
 *      password - typo3 password for typo3.org - overruled by TYPO3_PASSWORD env var
 *
 *      Run script with args like this `key=value`
 *
 *      php upload.php extension_dir=/some/path upload_comment="This is my comment" user="userxy" password=mysecretpassword123
 */


require 'Structs/Struct.php';
require 'Structs/AccountData.php';
require 'Structs/ExtensionData.php';
require 'Structs/FilesData.php';
require 'SoapClient.php';

$params = $argv;
unset($params[0]);

$parsedParams = [];
foreach ($params as $param) {
    $pArr = explode('=', $param);
    $parsedParams[$pArr[0]] = $pArr[1];
}


if (!isset($parsedParams['user'])) $parsedParams['user'] = '';
if (!isset($parsedParams['password'])) $parsedParams['password'] = '';
if (!isset($parsedParams['extension_dir'])) $parsedParams['extension_dir'] = '';
if (!isset($parsedParams['upload_comment'])) $parsedParams['upload_comment'] = '';
if (!isset($parsedParams['upload_comment_file'])) $parsedParams['upload_comment_file'] = '';

$TYPO3_USER = getenv('TYPO3_USER');
$TYPO3_USER = (is_string($TYPO3_USER) && !empty($TYPO3_USER)) ? $TYPO3_USER : $parsedParams['user'];

$TYPO3_PASSWORD = getenv('TYPO3_PASSWORD');
$TYPO3_PASSWORD = (is_string($TYPO3_PASSWORD) && !empty($TYPO3_PASSWORD)) ? $TYPO3_PASSWORD : $parsedParams['password'];

$TYPO3_EXTENSION_DIR = getenv('TYPO3_EXTENSION_DIR');
$TYPO3_EXTENSION_DIR = (is_string($TYPO3_EXTENSION_DIR) && !empty($TYPO3_EXTENSION_DIR)) ? $TYPO3_EXTENSION_DIR : $parsedParams['extension_dir'];

$TYPO3_UPLOAD_COMMENT_FILE = getenv('TYPO3_UPLOAD_COMMENT_FILE');
$TYPO3_UPLOAD_COMMENT_FILE = (is_string($TYPO3_UPLOAD_COMMENT_FILE) && !empty($TYPO3_UPLOAD_COMMENT_FILE)) ? $TYPO3_UPLOAD_COMMENT_FILE : $parsedParams['upload_comment_file'];

$TYPO3_UPLOAD_COMMENT = getenv('TYPO3_UPLOAD_COMMENT');
$TYPO3_UPLOAD_COMMENT = trim((is_string($TYPO3_UPLOAD_COMMENT) && !empty($TYPO3_UPLOAD_COMMENT)) ? $TYPO3_UPLOAD_COMMENT : $parsedParams['upload_comment']);

if (empty($TYPO3_USER)) {
    error_log('TYPO3.org username not provided');
    exit(-1);
}
if (empty($TYPO3_PASSWORD)) {
    error_log('TYPO3.org password not provided');
    exit(-1);
}
if (empty($TYPO3_EXTENSION_DIR)) {
    error_log('Extension directory not set');
    exit(-1);
}
if (empty($TYPO3_UPLOAD_COMMENT) && empty($TYPO3_UPLOAD_COMMENT_FILE)) {
    error_log('Extension upload comment not set');
    exit(-1);
}

if (!is_dir($TYPO3_EXTENSION_DIR)) {
    error_log('Extension dir is not a directory');
    exit(-1);
}

if (!empty($TYPO3_UPLOAD_COMMENT_FILE)) {
    if (!is_file($TYPO3_UPLOAD_COMMENT_FILE)) {
        error_log('Upload comment file not found');
        exit(-1);
    }
    $TYPO3_UPLOAD_COMMENT = trim(file_get_contents($TYPO3_UPLOAD_COMMENT_FILE));
    if (empty($TYPO3_UPLOAD_COMMENT)) {
        error_log('Upload comment file empty');
        exit(-1);

    }
}

try {
    $client = new \ZOTORN\Upload\SoapClient();
    $accountData = new \ZOTORN\Upload\Struct\AccountData($TYPO3_USER, $TYPO3_PASSWORD);
    $extensionData = new \ZOTORN\Upload\Struct\ExtensionData($TYPO3_EXTENSION_DIR, $TYPO3_UPLOAD_COMMENT);
    $filesData = new \ZOTORN\Upload\Struct\FilesData($TYPO3_EXTENSION_DIR);
    $client->uploadExtension($accountData, $extensionData, $filesData);
} catch (Exception $exception) {
    error_log($exception->getMessage());
    exit(-1);
}

echo "Extension upload successful\n";
