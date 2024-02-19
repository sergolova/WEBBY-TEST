<?php

namespace Model;

class User
{
    public int $id;
    public string $username;
    public string $hash;
    public string $salt;

    public static function FromArray(array $row): User
    {
        $user = new User();
        $user->id = $row['id'];
        $user->username = $row['username'];
        $user->salt = $row['salt'];
        $user->hash = $row['hash'];

        return $user;
    }

    public function can(string|array $code = ''): bool
    {
        return true;
    }
}