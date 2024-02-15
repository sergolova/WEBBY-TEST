<?php

class UserManager {
    public function init() {

    }

    public function login($username, $password) {
        // Логіка для авторизації користувача
    }

    public function logout() {
        // Логіка для виходу з системи
    }

    public function isLoggedIn(): bool {
        return false;
    }

    public function register($username, $password) {
        // Перевірка, чи користувач з таким ім'ям вже існує (може бути зроблено через роботу з базою даних)
        if ($this->userExists($username)) {
            return false; // Користувач із вказаним ім'ям вже існує
        }

        // Логіка для збереження нового користувача (може бути зроблено через роботу з базою даних)
        // Наприклад, ви можете створити таблицю "users" та вставити нового користувача
        // INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword');

        return true; // Користувач успішно зареєстрований
    }

    private function userExists(string $username): bool {
        // Логіка для перевірки існування користувача (може бути зроблено через роботу з базою даних)
        // Наприклад, можна використати запит SELECT COUNT(*) FROM users WHERE username = '$username'
        return false; // Припускається, що користувача з таким ім'ям немає
    }
}