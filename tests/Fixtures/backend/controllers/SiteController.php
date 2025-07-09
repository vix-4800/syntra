<?php

declare(strict_types=1);

namespace app\controllers;

class SiteController
{
    /**
     * Home page
     */
    public function actionIndex(string $id, int $page = 1): string
    {
        return 'index';
    }

    public function actionView($slug)
    {
        return 'view';
    }
}
