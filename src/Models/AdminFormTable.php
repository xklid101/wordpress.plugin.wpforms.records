<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Models;

use Xklid101\Wprecords\Services\Database;
use Xklid101\Wprecords\Services\AdminFormTableFactory;
use WPForms\WPForms;
use WP_List_Table;

class AdminFormTable extends WP_List_Table
{
    private $wpforms;

    private $db;

    private $id;

    private $columns;

    public function __construct(
        array $args,
        WPForms $wpforms,
        Database $db,
        $id
    ) {
        $this->wpforms = $wpforms;
        $this->db = $db;
        $this->id = $id;

        parent::__construct($args);
    }

    public function getId()
    {
        return $this->id;
    }

    public function column_cb($item)
    {
        // var_dump($item);
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%s" />',
            $item['__id'] ?? ''
        );
    }

    public function get_columns()
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        $formsList = $this->wpforms->form->get() ?: [];
        $dbCols = $this->db->getColumns($this->getId());
        $colsSpec = [
            'cb' => '<input type="checkbox" />',
            $this->getColNameFromDbCol('__id') => 'Id',
            $this->getColNameFromDbCol('__time_add') => __('Datum'),
            $this->getColNameFromDbCol('__url') => __('Stránka'),
            $this->getColNameFromDbCol('__note') => __('Poznámka')
        ];
        $formFields = [];
        foreach ($formsList as $value) {
            $item = wpforms_decode($value->post_content);
            if ($item['id'] == $this->getId()) {
                foreach ($item['fields'] ?? [] as $value2) {
                    $formFields[(string) $value2['id']] = $value2['label'];
                }
                break;
            }
        }

        $cols = [];
        foreach ($colsSpec as $key => $value) {
            $cols[$key] = $value;
        }
        $colsVar = [];
        foreach ($dbCols as $dbCol) {
            $dbCol = (string) $dbCol;
            if (isset($formFields[$dbCol])) {
                $colsVar[$this->getColNameFromDbCol($dbCol)] = $formFields[$dbCol];
            }
        }

        /**
         * mysql help to sort better
         */
        $wpdb = $this->db->getDb();
        $wpdb->query("
            CREATE TEMPORARY TABLE tmp (
                `key` text NOT NULL,
                `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL
            )
        ");
        foreach ($colsVar as $key => $value) {
            $wpdb->insert('tmp', ['key' => $key, 'value' => $value]);
        }
        $resColsVarsSorted = $wpdb->get_results("SELECT * FROM tmp ORDER BY value ASC");
        foreach ($resColsVarsSorted as $values) {
            $cols[$values->key] = $values->value;
        }

        $this->columns = $cols;
        return $this->columns;
    }

    private function getColNameFromDbCol($dbCol): string
    {
        return 'col_' . $this->getId() . '_' . $dbCol;
    }

    private function getDbColFromColName($colName): string
    {
        return str_replace('col_' . $this->getId() . '_', '', $colName);
    }

    public function ajax_user_can()
    {
        /**
         * should return or throw error or what??? undocumented
         */
        return current_user_can('edit_pages');
    }

    public function get_sortable_columns()
    {
        $cols = [];
        foreach (array_keys($this->get_columns()) as $value) {
            if ($value === 'cb') {
                continue;
            }
            $cols[$value] = [
                $this->getDbColFromColName($value),
                false
            ];
        }
        return $cols;
    }

    // public function get_hidden_columns()
    // {
    //     // Setup Hidden columns and return them
    //     return [];
    // }

    public function get_primary_column_name()
    {
        return '__id';
    }

    public function column_default($item, $colName)
    {
        $dbCol = $this->getDbColFromColName($colName);
        switch ($dbCol) {
            case '__id':
                return $item[$dbCol];
            case '__time_add':
                return $item[$dbCol]
                    ? date('j.n.Y', strtotime($item[$dbCol]))
                        . ' <small>(' . date('H:i', strtotime($item[$dbCol]))
                        . ')</small>'
                    : '';
            case '__url':
                return $item[$dbCol]
                    ? '<a href="' . esc_attr($item[$dbCol]) . '" title="' . esc_attr($item[$dbCol]) . '" target="_blank">'
                        . '...' . esc_html(substr($item[$dbCol], -30))
                        . '</a>'
                    : '';
            default:
                return '<div class="read">
                            ' . esc_html($item[$dbCol]) . '
                        </div>
                        <div class="edit" style="display: none">
                            <textarea
                                name="edit[' . $item['__id'] . '][' . $dbCol . ']"
                            >'
                                . esc_textarea($item[$dbCol])
                            . '</textarea>
                        </div>';
                break;
        }
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => __('Delete')
        ];
        return $actions;
    }

    // public function processAction()
    // {
    //     if (strtolower($_SERVER['REQUEST_METHOD'] ?? '') !== 'post') {
    //         return;
    //     }

    //     $nonce = $_REQUEST['_wpnonce'];
    //     if (!wp_verify_nonce($nonce, 'bulkx-' . $this->_args['plural'])) {
    //         $this->template->flashMessage('Nonce not verified!', 'error');
    //         // wp_die('Nonce not verified!');
    //         return;
    //     }
    //     if (isset($_POST['asubmit-all-changes'])) {
    //         # code...
    //     } else if ($this->current_action() === 'bulk-deletex') {
    //         $this->deleteRecords($_POST['ids'] ?? []);
    //     }
    // }
    /**
    * Delete a records
    * * @param array $ids records ids
    */
    // private function deleteRecords(array $ids)
    // {
    //     $wpdb = $this->db->getDb();
    //     $tbl = $this->db->getFormTable($this->getId());
    //     foreach ($ids as $id) {
    //         $wpdb->delete($tbl, ['__id' => $id], ['%d']);
    //     }
    // }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        // $this->processAction();

        $perpage = $this->get_items_per_page(AdminFormTableFactory::OPTION_PER_PAGE);

        $wpdb = $this->db->getDb();
        $tbl = $this->db->getFormTable($this->getId());
        $sels = [];
        foreach (array_keys($this->get_columns()) as $value) {
            if ($value === 'cb') {
                continue;
            }
            $sels[] = "`" . $this->getDbColFromColName($value) . "`";
        }
        $query = "SELECT " . implode(',', $sels) . " FROM {$tbl}";

        $search = [];
        $queryParams = [];
        if (isset($_REQUEST['s'])) {
            foreach (array_keys($this->get_columns()) as $value) {
                if ($value === 'cb') {
                    continue;
                }
                $search[] = '`' . $this->getDbColFromColName($value) . '` LIKE %s';
                $queryParams[] = '%' . $_REQUEST['s'] . '%';
            }
        }
        if ($search) {
            $query .= ' WHERE ' . implode(' OR ', $search);
        }

        $sortableColumns = $this->get_sortable_columns();
        $orderby = isset($sortableColumns[$this->getColNameFromDbCol($_GET['orderby'] ?? '')])
            ? $_GET['orderby']
            : $this->get_primary_column_name();
        $order = strtolower($_GET["order"] ?? '') === 'asc' ? 'ASC' : 'DESC';

        $query .= ' ORDER BY `' . $orderby . '` ' . $order;

        if ($queryParams) {
            $totalitems = $wpdb->query(
                $wpdb->prepare(
                    $query,
                    $queryParams
                )
            );
        } else {
            $totalitems = $wpdb->query($query);
        }

        $page = $this->get_pagenum();;
        $totalpages = ceil($totalitems / $perpage);
        $offset = (int) (($page - 1) * $perpage);
        $query .= ' LIMIT ' . $offset . ',' . $perpage;

        $this->set_pagination_args(
            [
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page" => $perpage,
            ]
        );

        if ($queryParams) {
            $this->items = $wpdb->get_results(
                $wpdb->prepare(
                    $query,
                    $queryParams
                ),
                ARRAY_A
            );
        } else {
            $this->items = $wpdb->get_results(
                $query,
                ARRAY_A
            );
        }
    }
}

