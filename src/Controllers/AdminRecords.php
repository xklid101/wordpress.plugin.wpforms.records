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

        $formTableSelected = $this->getFormTableSelected();
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

    private function getFormTableSelected()
    {
        $id = $_GET['formid'] ?? 0;
        if (!$id) {
            return null;
        }
        return $this->formTableFactory->create($id);
    }

    public function submit()
    {
        // var_dump('submitteed');
    }
}

