<?php

namespace ZOTORN\Upload\Struct;

class AccountData implements Struct
{
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;

    public function __construct(string $username, string $password)
    {

        $this->username = $username;
        $this->password = $password;
    }

    function asArray(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password
        ];
    }
}
