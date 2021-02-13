<?php

declare(strict_types=1);

namespace Xklid101\Wprecords;

use WP_Error;
use RuntimeException;
use Xklid101\Wprecords\Di\Container;
use Xklid101\Wprecords\Services\Routing;


class Loader
{
    private $container;

    /**
     * Filesystem path to the src directory
     * @var string
     */
    private string $baseSrcDir;

    /**
     * Class constructor
     *
     * @param Container $container  [description]
     * @param string $version The current plugin version
     * @param string $baseSrcDir The main src directory
     */
    public function __construct(Container $container, string $baseSrcDir) {
        $this->container = $container;
        $this->baseSrcDir = $baseSrcDir;
    }

    public function loadPlugin()
    {
        /* General Administration functions */
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addAdminSubMenuPage']);
        }
    }

    private function getRouting()
    {
        return $this->container->get(Routing::class, $this->container, $this->baseSrcDir);
    }

    public function addAdminSubMenuPage()
    {
        try {
            $hook = add_management_page(
                'xklid101 - Wpforms records',
                'xklid101 - Wpforms records',
                'edit_pages',
                $this->getRouting()->getSlug(),
                array($this->getRouting()->getAdminController(), 'render')
            );

            if (strtolower($_SERVER['REQUEST_METHOD'] ?? '') === 'post') {
                add_action(
                    'load-' . $hook,
                    array($this->getRouting()->getAdminController(), 'submit')
                );
            }
        } catch (RuntimeException $e) {
            new WP_Error($e->getCode(), $e->getMessage());
        }
    }
}

