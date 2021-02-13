<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Controllers;

use Xklid101\Wprecords\Services\Routing;


class AdminConfig
{
    private string $baseSrcDir;

    private $routing;

    public function __construct(Routing $routing, string $baseSrcDir)
    {
        if (!current_user_can('manage_options')) {
            wp_die(__( 'You are not authorized to access this page.'));
        }
        $this->baseSrcDir = $baseSrcDir;
        $this->routing = $routing;
    }

    public function render()
    {
        require_once $this->baseSrcDir . '/views/adminConfig.php';
    }

    public function submit()
    {
        var_dump('submitteed');
    }
}

