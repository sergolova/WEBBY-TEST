<?php

namespace Model;
class User
{
    public int $id;
    public string $username;
    public string $hash;
    public string $salt;

    public static function FromRow(array $row): User {
        $user = new User();
        $user->id = @$row['id'];
        $user->username = @$row['username'];
        $user->salt = @$row['salt'];
        $user->hash = @$row['hash'];

        return $user;
    }
}