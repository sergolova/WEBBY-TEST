<?php

namespace Controller;

class AuthenticatedController extends CustomController
{
    public function home()
    {
        $this->getTemplate('AuthHomeTemplate');
    }

    public function dashboard()
    {
        echo 'Ласкаво просимо на ваш особистий кабінет!';
    }


}