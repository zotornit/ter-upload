<?php

namespace ZOTORN\Upload;

use ZOTORN\Upload\Struct\AccountData;
use ZOTORN\Upload\Struct\ExtensionData;
use ZOTORN\Upload\Struct\FilesData;

class SoapClient
{
    private const UPLOAD_URL = "https://extensions.typo3.org/typo3conf/ext/ter/tx_ter_wsdl.php";

    private $client;

    public function __construct()
    {
        $this->client = new \SoapClient(self::UPLOAD_URL);
    }


    public function uploadExtension(AccountData $accountData, ExtensionData $extensionData, FilesData $filesData)
    {
        return $this->client->__soapCall('uploadExtension', array(
            'accountData' => $accountData->asArray(),
            'extensionData' => $extensionData->asArray(),
            'filesData' => $filesData->asArray(),
        ));
    }

    // Need to be administrator, for deleteExtension
    public function deleteExtension(AccountData $accountData, string $extensionKey, string $version)
    {
        return $this->client->__soapCall('deleteExtension', array(
            'accountData' => $accountData->asArray(),
            'extensionKey' => $extensionKey,
            'version' => $version,
        ));
    }
}
