<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Controllers;

use Xklid101\Wprecords\Services\Template;
use Xklid101\Wprecords\Services\Config;
use WPForms\WPForms;

class AdminConfig
{
    private $template;

    private $wpforms;

    private $config;

    public function __construct(
        Template $template,
        WPForms $wpforms,
        Config $config
    ) {
        if (!current_user_can('manage_options')) {
            wp_die(__( 'You are not authorized to access this page.'));
        }
        $this->template = $template;
        $this->wpforms = $wpforms;
        $this->config = $config;
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
        $config = $this->config->getWp();
        $this->template->render(
            'adminConfig',
            [
                'formsData' => $formsData,
                'config' => $config
            ]
        );
    }

    public function submit()
    {
        $config = $_POST['config'] ?? null;
        if ($config) {
            $this->config->setWp($config);
            $this->template->flashMessage('Ok', 'success');
            return;
        }
    }
}

