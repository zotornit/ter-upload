<?php

namespace ZOTORN\Upload\Struct;

class FilesData implements Struct
{
    private $filesData;

    public function __construct(string $path)
    {
        $path = rtrim($path, '/') . '/';

        if (!is_dir($path) || !is_readable($path)) {
            throw new \Exception('1586357126');
        }

        $fileArr = $this->extensionFilesArray($path);

        $filesData = array();
        foreach ($fileArr as $filePath) {
            $fileName = substr($filePath, strlen($path));
            $content = file_get_contents($filePath);
            $filesData[utf8_encode($fileName)] = array(
                'name' => utf8_encode($fileName),
                'size' => intval(filesize($filePath)),
                'modificationTime' => intval(filemtime($filePath)),
                'isExecutable' => intval(is_executable($filePath)),
                'content' => $content,
                'contentMD5' => md5($content),
                'content_md5' => md5($content),
            );
        }

        $this->filesData = $filesData;
    }

    function extensionFilesArray($path)
    {
        $path = rtrim($path, '/') . '/';
        $list = [];
        $files = scandir($path);
        foreach ($files as $file) {
            if ((string)$file == '' || preg_match('/^(CVS|\..*|.*~|.*\.bak)$/', $file)) {
                continue;
            }
            $fullpath = $path . $file;
            if (is_dir($fullpath)) {
                $list = array_merge($list, $this->extensionFilesArray($fullpath));
            } else if (is_file($fullpath)) {
                $list[] = $fullpath;
            }
        }
        return $list;
    }

    function asArray(): array
    {
        return $this->filesData;
    }
}
