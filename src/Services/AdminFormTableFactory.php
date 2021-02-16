<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

use Xklid101\Wprecords\Services\Database;
use Xklid101\Wprecords\Models\AdminFormTable;
use Xklid101\Wprecords\Services\Config;
use WPForms\WPForms;

class AdminFormTableFactory
{
    const OPTION_PER_PAGE = Config::CONFIG_PARAM_NAME . '_tbl_per_page';

    private $wpforms;

    private $db;

    private $stack = [];

    public function __construct(
        WPForms $wpforms,
        Database $db
    ) {
        $this->wpforms = $wpforms;
        $this->db = $db;
    }

    public function create($id): AdminFormTable
    {
        $idx = $id;
        if (isset($this->stack[$idx])) {
            return $this->stack[$idx];
        }

        $this->stack[$idx] = new AdminFormTable(
            [
                'singular' => Database::TBL_PREFIX . 'form',
                'plural' => Database::TBL_PREFIX . 'forms',
                'ajax' => true
            ],
            $this->wpforms,
            $this->db,
            $id
        );
        return $this->stack[$idx];
    }
}

