<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

use RuntimeException;
use Xklid101\Wprecords\Di\Container;
use Xklid101\Wprecords\Controllers\AdminConfig;
use Xklid101\Wprecords\Controllers\AdminRecords;

class Routing
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
     * @param string $baseSrcDir The main src directory
     */
    public function __construct(Container $container, string $baseSrcDir) {
        $this->container = $container;
        $this->baseSrcDir = $baseSrcDir;
    }

    public function getPluginCurrentUrlPage(): string
    {
        return $this->getPluginUrl([
            'subpage' => $_GET['subpage'] ?? ''
        ]);
    }

    public function isSubpage($name)
    {
        return ($_GET['subpage'] ?? '') === $name;
    }

    public function getPluginUrl(array $params = []): string
    {
        return admin_url(
            'tools.php?page='
            . $this->getSlug()
            . '&'
            . http_build_query($params)
        );
    }

    private function isPluginUrl()
    {
        return ($_GET['page'] ?? '') === $this->getSlug();
    }

    public function getSlug()
    {
        return 'xklid101-forms-records';
    }

    public function getAdminController()
    {
        if ($this->isPluginUrl()) {
            switch ($_GET['subpage'] ?? '--unset--') {
                case 'records':
                    return $this->container->get(AdminRecords::class, $this->baseSrcDir);
                case 'config':
                    return $this->container->get(AdminConfig::class, $this->baseSrcDir);
                case '--unset--':
                    wp_safe_redirect(
                        $this->getPluginUrl(
                            [
                                'subpage' => 'records'
                            ]
                        )
                    );
                    exit;
                default:
                    throw new RuntimeException("Subpage not found!", 404);
            }
        }

        /**
         * get default controller
         * (controllers are loaded on all wordpress init, not just subpage...)
         * we have to return some default controller every time
         */
        return $this->container->get(AdminRecords::class, $this->baseSrcDir);
    }
}



