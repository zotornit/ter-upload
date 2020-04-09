<?php

namespace ZOTORN\Upload\Struct;

class ExtensionData implements Struct
{

    private $extConf = [];

    public function __construct(string $extFolderPath, string $comment = '')
    {
        $this->extConf = [];

        $extFolderPath = rtrim($extFolderPath, '/') . '/';

        if (!is_dir($extFolderPath) || !is_readable($extFolderPath)) {
            throw new \Exception('Error: 1586356877');
        }

        include $extFolderPath . 'ext_emconf.php';

        if (!isset($EM_CONF)) {
            throw new \Exception('Error: 1586356878');
        }

        $extensionKey = array_key_first($EM_CONF);


        $emConf = $EM_CONF[$extensionKey];
        if (empty($emConf['title']) || empty($emConf['version'])) {
            throw new \Exception('Invalid $EM_CONF');
        }

        $this->extConf = array(
            'extensionKey' => $extensionKey,
            'version' => $emConf['version'],
            'metaData' => array(
                'title' => $emConf['title'],
                'description' => $emConf['description'] ?? '',
                'category' => $emConf['category'] ?? '',
                'state' => $emConf['state'] ?? '',
                'authorName' => $emConf['author'] ?? '',
                'authorEmail' => $emConf['author_email'] ?? '',
                'authorCompany' => $emConf['author_company'] ?? '',
            ),
            'technicalData' => array(
                'dependencies' => $this->buildDependencyArray($emConf),
                'loadOrder' => $emConf['loadOrder'] ?? '',
                'uploadFolder' => $emConf['uploadfolder'] ?? FALSE,
                'createDirs' => $emConf['createDirs'] ?? '',
                'shy' => $emConf['shy'] ?? FALSE,
                'modules' => $emConf['module'] ?? '',
                'modifyTables' => $emConf['modify_tables'] ?? '',
                'priority' => $emConf['priority'] ?? '',
                'clearCacheOnLoad' => isset($emConf['clearCacheOnLoad']) ? (boolean)intval($emConf['clearCacheOnLoad']) : FALSE,
                'lockType' => $emConf['lockType'] ?? '',
                'doNotLoadInFE' => $emConf['doNotLoadInFE'] ?? '',
                'docPath' => $emConf['docPath'] ?? '',
                'autoload' => $emConf['autoload'] ?? [],
            ),
            'infoData' => array(
                'codeLines' => 0,
                'codeBytes' => 0,
                'codingGuidelinesCompliance' => $emConf['CGLcompliance'] ?? '',
                'codingGuidelinesComplianceNotes' => $emConf['CGLcompliance_note'] ?? '',
                'uploadComment' => $comment,
                'techInfo' => array(),
            ),
        );

        array_walk_recursive($this->extConf, function (&$value) {
            if (is_string($value)) {
                $value = utf8_encode($value);
            }
        });
    }

    private function buildDependencyArray($emConf)
    {
        $dependenciesArr = [];
        if (!isset($emConf['constraints']) || !is_array($emConf['constraints'])) {
            return $dependenciesArr;
        }

        if (isset($emConf['constraints']['depends']) && is_array($emConf['constraints']['depends'])) {
            $dependenciesArr = array_merge($dependenciesArr, $this->deppArrFor($emConf['constraints']['depends'], 'depends'));

        }
        if (isset($emConf['constraints']['conflicts']) && is_array($emConf['constraints']['conflicts'])) {
            $dependenciesArr = array_merge($dependenciesArr, $this->deppArrFor($emConf['constraints']['conflicts'], 'conflicts'));
        }
        // suggests not required, is not part of the old EM_CONF structure
        return $dependenciesArr;
    }


    private function deppArrFor($arr, $key)
    {
        $res = [];
        foreach ($arr as $extKey => $version) {
            if (strlen($extKey)) {
                $res[] = array(
                    'kind' => $key,
                    'extensionKey' => utf8_encode($extKey),
                    'versionRange' => utf8_encode($version),
                );
            }
        }
        return $res;
    }

    function asArray(): array
    {
        return $this->extConf;
    }
}
