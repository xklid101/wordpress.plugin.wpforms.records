<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

class FrontForm
{
    private $config;

    private $db;

    public function __construct(
        Config $config,
        Database $db
    ) {
        $this->config = $config;
        $this->db = $db;
    }

    public function getErrors($errors, $formData): array
    {
        $wpdb = $this->db->getDb();

        $config = $this->config->getWp();

        $formId = $formData['id'] ?? 0;
        $postData = $_POST['wpforms'] ?? [];

        $tbl = $this->db->getFormTable($formId);
        $this->db->createTable($formId);

        /**
         * check all form records maxcount option
         */
        $maxCountAll = isset($config[$formId]['maxcount'])
            ? (int) $config[$formId]['maxcount']
            : -1;

        if ($maxCountAll >= 0) {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "
                        SELECT COUNT(*)
                        FROM `{$tbl}`
                    ",
                    $colValue
                )
            );
            if ($count >= $maxCountAll) {
                if (!isset($errors[$formId]['header'])) {
                    $errors[$formId]['header'] = '';
                }
                $errors[$formId]['header'] .= __('Formulář již nelze odeslat! Počet záznamů dosáhl maxima!');
            }

        }

        /**
         * check specific fields
         */
        foreach ($formData['fields'] ?? [] as $value) {
            $fieldId = $value['id'] ?? 0;
            $colValue = $postData['fields'][$fieldId] ?? '';

            $this->db->addColumn($formId, $fieldId);

            /**
             * check field records maxcount option
             */
            $maxCountField = isset($config[$formId]['fields'][$fieldId]['maxcount'])
                ? (int) $config[$formId]['fields'][$fieldId]['maxcount']
                : -1;

            if ($maxCountField >= 0) {
                $count = $wpdb->get_var(
                    $wpdb->prepare(
                        "
                            SELECT COUNT(`{$fieldId}`)
                            FROM `{$tbl}`
                            WHERE `{$fieldId}` = %s
                        ",
                        $colValue
                    )
                );
                if ($count >= $maxCountField) {
                    $errors[$formId][$fieldId] .= __('Formulář již nelze odeslat! Počet unikátních záznamů pro toto pole dosáhl maxima!');
                }

            }
        }
        return $errors;
    }

    public function setRecords($fields, $entry, $formId, $formData): void
    {
        $wpdb = $this->db->getDb();

        $tbl = $this->db->getFormTable($formId);
        $this->db->createTable($formId);

        $toInsert = [];
        foreach ($fields ?: [] as $field) {
            $fieldId = $field['id'] ?? 0;
            $colValue = $field['value'] ?? '';

            $this->db->addColumn($formId, $fieldId);

            $toInsert[$fieldId] = $colValue;
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $toInsert['__url'] = $_SERVER['HTTP_REFERER'];
        }
        if ($toInsert) {
            $wpdb->insert($tbl, $toInsert);
        }
    }
}



