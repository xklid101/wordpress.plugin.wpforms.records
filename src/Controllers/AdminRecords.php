<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Controllers;

use Xklid101\Wprecords\Services\Template;
use WPForms\WPForms;

class AdminRecords
{
    private $template;

    private $wpforms;

    public function __construct(
        Template $template,
        WPForms $wpforms
    ) {
        if (!current_user_can('edit_pages')) {
            wp_die(__( 'You are not authorized to access this page.'));
        }
        $this->template = $template;
        $this->wpforms = $wpforms;
    }

    public function render()
    {
        $formsData = [];
        $formsList = $this->wpforms->form->get() ?: [];
        $i = 0;
        foreach ($formsList as $value) {
            $item = wpforms_decode($value->post_content);
            $formsData[$i]['id'] = $item['id'];
            $formsData[$i]['title'] = $item['settings']['form_title'];
            $formsData[$i]['fields'] = [];
            $n = 0;
            foreach ($item['fields'] ?? [] as $value2) {
                $formsData[$i]['fields'][$n]['id'] = $value2['id'];
                $formsData[$i]['fields'][$n]['label'] = $value2['label'];
                $n++;
            }
            $i++;
        }

        $this->template->render(
            'adminRecords',
            [
                'formsData' => $formsData
            ]
        );
    }

    public function submit()
    {
        var_dump('submitteed');
    }
}

