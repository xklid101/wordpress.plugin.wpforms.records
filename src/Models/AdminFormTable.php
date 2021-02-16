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
        return sprintf(
            '<input type="checkbox" name="record[]" value="%s" />', $item['__id'] ?? ''
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
            '__note' => __('Poznámka')
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
        foreach ($dbCols as $dbCol) {
            $dbCol = (string) $dbCol;
            if (isset($formFields[$dbCol])) {
                $cols['col_' . $dbCol] = $formFields[$dbCol];
            }
        }

        $this->columns = $cols;
        return $this->columns;
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
            $cols[$value] = str_replace('col_', '', $value);
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
        return $item[str_replace('col_', '', $colName)] ?? '';
    }

    public function get_bulk_actions()
    {
        $actions = ['bulk-delete' => 'Delete'];
        return $actions;
    }

    public function process_bulk_action()
    {
        // Detect when a bulk action is being triggered...
        // if ('delete' === $this->current_action()) {
        //     // In our file that handles the request, verify the nonce.
        //     $nonce = esc_attr($_REQUEST['_wpnonce']);
        //     if (!wp_verify_nonce($nonce, 'bx_delete_records')) {
        //         die('Nonce not verified!');
        //     }
        //     else {
        //         $this->delete_records(absint($_GET['record']));
        //         $redirect = admin_url('admin.php?page=codingbin_records');
        //         wp_redirect($redirect);
        //         exit;
        //     }
        // }

        // // If the delete bulk action is triggered
        // if ((isset($_POST['action']){
        //     $_POST['action'] == 'bulk-delete') || (isset($_POST['action2']) & amp; & amp;
        //     $_POST['action2'] == 'bulk-delete')) {
        //     $delete_ids = esc_sql($_POST['bulk-delete']);
        //     // loop over the array of record IDs and delete them
        //     foreach($delete_ids as $id) {
        //         self::delete_records($id);
        //     }

        //     $redirect = admin_url('admin.php?page=codingbin_records');
        //     wp_redirect($redirect);
        //     exit;
        //     exit;
        // }
    }
    /**
    * Delete a record record.
    * * @param int $id customer ID
    */
    public static function delete_records($id)
    {
        // global $wpdb;
        // $wpdb->delete("custom_records", ['id' => $id], ['%d']);
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();
        /**
         * @todo - fix: not working with predefined option (in Loader)
         *  like here https://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
         */
        $perpage = $this->get_items_per_page(AdminFormTableFactory::OPTION_PER_PAGE);

        $wpdb = $this->db->getDb();
        $tbl = $this->db->getFormTable($this->getId());
        $sels = [];
        foreach (array_keys($this->get_columns()) as $value) {
            if ($value === 'cb') {
                continue;
            }
            $sels[] = "`" . str_replace('col_', '', $value) . "`";
        }
        $query = "SELECT " . implode(',', $sels) . " FROM {$tbl}";

        $search = [];
        $queryParams = [];
        if (isset($_REQUEST['s'])) {
            foreach (array_keys($this->get_columns()) as $value) {
                if ($value === 'cb') {
                    continue;
                }
                $search[] = '`' . str_replace('col_', '', $value) . '` LIKE %s';
                $queryParams[] = '%' . $_REQUEST['s'] . '%';
            }
        }
        if ($search) {
            $query .= ' WHERE ' . implode(' OR ', $search);
        }

        $orderby = in_array(($_GET['orderby'] ?? ''), $this->get_sortable_columns(), true)
            ? $_GET['orderby'] : $this->get_primary_column_name();
        $order = strtolower($_GET["order"] ?? '') === 'desc' ? 'DESC' : 'ASC';

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

