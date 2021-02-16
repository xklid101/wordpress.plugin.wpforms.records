<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Controllers;

use Xklid101\Wprecords\Services\Template;
use Xklid101\Wprecords\Services\Database;
use Xklid101\Wprecords\Services\AdminFormTableFactory;
use WPForms\WPForms;

class AdminRecords
{
    private $template;

    private $wpforms;

    private $db;

    private $formTableFactory;

    public function __construct(
        Template $template,
        WPForms $wpforms,
        Database $db,
        AdminFormTableFactory $formTableFactory
    ) {
        if (!current_user_can('edit_pages')) {
            wp_die(__( 'You are not authorized to access this page.'));
        }
        $this->template = $template;
        $this->wpforms = $wpforms;
        $this->db = $db;
        $this->formTableFactory = $formTableFactory;
    }

    public function render()
    {
        $formsData = [];
        $formsList = $this->wpforms->form->get() ?: [];

        $wpdb = $this->db->getDb();
        $tbls = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}" . Database::TBL_PREFIX . "%'");
        $i = 0;
        foreach ($tbls as $tbl) {
            if (!preg_match('#_([^_]+)$#', $tbl, $matches)) {
                continue;
            }
            $formId = $matches[1];
            $formsData[$i]['id'] = $formId;
            $formsData[$i]['title'] = '--notfound--';
            foreach ($formsList as $value) {
                $item = wpforms_decode($value->post_content);
                if ($item['id'] == $formId) {
                    $formsData[$i]['title'] = $item['settings']['form_title'];
                }
            }
            $i++;
        }

        foreach ($formsList as $value) {
            $item = wpforms_decode($value->post_content);
            foreach ($formsData as $value2) {
                if ($item['id'] == $value2['id']) {
                    continue 2;
                }
            }
            $formsData[$i]['id'] = $item['id'];
            $formsData[$i]['title'] = $item['settings']['form_title'];
            $i++;
        }

        $formTableSelected = $this->getFormTableSelected($_GET['formid'] ?? 0);
        if ($formTableSelected) {
            $formTableSelected->prepare_items();
        }

        $this->template->render(
            'adminRecords',
            [
                'formsData' => $formsData,
                'formTableSelected' => $formTableSelected
            ]
        );
    }

    private function getFormTableSelected($id)
    {
        if (!$id) {
            return null;
        }
        return $this->formTableFactory->create($id);
    }

    public function submit()
    {

        $id = $_POST['id'] ?? 0;
        $formTableSelected = $this->getFormTableSelected($id);
        if (!$formTableSelected) {
            $this->template->flashMessage('FormTable not found by ID "' . $id . '"!', 'error');
            return;
        }

        if (isset($_POST['export-all-table'])) {
            $this->exportAllTable($id);
            return;
        }

        $nonce = $_REQUEST['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'bulk-' . $formTableSelected->_args['plural'])) {
            $this->template->flashMessage('Token not verified! Reload page and try again', 'error');
            return;
        }

        $wpdb = $this->db->getDb();
        $tbl = $this->db->getFormTable($id);
        if (isset($_POST['submit-all-changes'])) {
            foreach ($_POST['edit'] as $idRecord => $values) {
                $wpdb->update($tbl, $values, ['__id' => $idRecord]);
            }
            $this->template->flashMessage('Ok');
        } elseif ($formTableSelected->current_action() === 'bulk-delete') {
            foreach (($_POST['ids'] ?? []) as $id) {
                $wpdb->delete($tbl, ['__id' => $id], ['%d']);
            }
            $this->template->flashMessage('Ok');
        }
    }

    private function exportAllTable($formId)
    {
        $wpdb = $this->db->getDb();
        $tbl = $this->db->getFormTable($formId);
        $dbFieldsMap = [];

        $formsList = $this->wpforms->form->get() ?: [];
        foreach ($formsList as $value) {
            $item = wpforms_decode($value->post_content);
            if ($item['id'] == $formId) {
                foreach (($item['fields'] ?? []) as $value2) {
                    $dbFieldsMap[$value2['id']] = $value2['label'];
                }
                break;
            }
        }

        $res = $wpdb->get_results(
            "
                SELECT *
                FROM {$tbl}
            ",
            ARRAY_A
        );

        $toCsv = [];
        $i = 0;
        foreach ($res as $row) {
            if(!$i) {
                foreach ($row as $field => $value) {
                    $toCsv[$i][] = isset($dbFieldsMap[$field]) ? $dbFieldsMap[$field] : $field;
                }
                $i++;
            }
            foreach ($row as $field => $value) {
                $toCsv[$i][] = $value;
            }
            $i++;
        }

        $filename = 'export.' . $tbl . '.csv';
        $delimiter = ',';
        $enclosure = '"';
        $f = fopen('php://memory', 'w');
        foreach($toCsv as $row) {
            fputcsv($f, $row, $delimiter, $enclosure);
        }
        fseek($f, 0);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);
        fclose($f);
        exit();
    }
}

