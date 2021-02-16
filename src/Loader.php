<?php

declare(strict_types=1);

namespace Xklid101\Wprecords;

use WP_Error;
use RuntimeException;
use Xklid101\Wprecords\Di\Container;
use Xklid101\Wprecords\Services\Routing;
use Xklid101\Wprecords\Services\FrontForm;
use Xklid101\Wprecords\Services\AdminFormTableFactory;


class Loader
{
    private $container;

    /**
     * Class constructor
     *
     * @param Container $container  [description]
     * @param string $version The current plugin version
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function loadPlugin()
    {
        /* General Administration functions */
        if (is_admin()) {
            add_action(
                'admin_menu',
                [$this, 'addAdminSubMenuPage']
            );

            add_filter(
                'set-screen-option',
                [$this->container->get(AdminFormTableFactory::class), 'setScreenOption'],
                10,
                3
            );
        }

        /**
         * wpform save actions seems to be sending to the admin part
         * so better to register filter/action everytime
         */
        add_filter(
            'wpforms_process_initial_errors',
            [$this->container->get(FrontForm::class), 'getErrors'],
            10,
            2
        );
        add_action(
            'wpforms_process_entry_save',
            [$this->container->get(FrontForm::class), 'setRecords'],
            10,
            4
        );
    }

    private function getRouting()
    {
        return $this->container->get(Routing::class);
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

            if ($this->getRouting()->isPluginUrl() && ($_GET['formid'] ?? '')) {
                /**
                 * Adding the checkboxes for hiding/showing the columns is done by WordPress automatically.
                 * You just have to make sure that your derived class is instantiated
                 * before the screen option panel is rendered so that the parent class can retrieve the column names.
                 * To accomplish this the corresponding code is moved into the method add_options():
                 */
                add_action(
                    'load-' . $hook,
                    function() {
                        add_screen_option(
                            'per_page',
                            [
                                // 'label' => __('Záznamů na stránku'),
                                'default' => 99,
                                'option' => AdminFormTableFactory::OPTION_PER_PAGE
                            ]
                        );
                        $this->container->get(AdminFormTableFactory::class)->create($_GET['formid']);
                    }
                );
            }
        } catch (RuntimeException $e) {
            new WP_Error($e->getCode(), $e->getMessage());
        }
    }
}
