<?php

namespace Controller;

use Model\UserManager as UserManager;

class MainController extends CommonController
{
    /** Well, here, it seems, everything is clear
     * @return void
     */
    public function about(): void
    {
        $this->getTemplate('AboutTemplate', [
            'user' => $this->userManager->getCurrentUser(),
            'styles' => ['main'],
        ]);
    }
}