<?php

namespace Controller;

class PublicController extends CustomController
{
    public function home()
    {
        $m = \DatabaseManager::getMovie(2);
        $this->getTemplate('PubHomeTemplate', ['movies' => [$m, $m, $m]]);
    }

    public function about()
    {
        echo 'Це сторінка "Про нас".';
    }
}