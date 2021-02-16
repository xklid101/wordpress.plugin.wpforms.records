<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

class FrontForm
{
    private $config;

    private $db;

    private $template;

    public function __construct(
        Config $config,
        Database $db,
        Template $template
    ) {
        $this->config = $config;
        $this->db = $db;
        $this->template = $template;
    }

    public function disableSubmitByJs($formData, $form)
    {
        $formId = $formData['id'];
        if (!$this->isFormMaxCountOk($formId)) {
            $this->template->render('frontFormDisableFormSubmitByJs', [
                'message' => $this->getFormErrorMsg($formId, 'maxcount-message'),
                'formId' => $formId,
                'doDisable' => true
            ]);
        }
    }

    public function getDisabledFieldsPropsBefore($properties, $field, $formData)
    {
        $formId = $formData['id'];
        $fieldId = $field['id'];

        /**
         * check if all inputs (checkboxes, radio, select > options)
         *     are overlimit. if yes, disable all field
         *
         * There should be all combinations for one container with multiple checkboxes
         * and possibility to choose more values (or select multiple)
         * but this is useless because we do not know format of saved values and
         * for slightly bigger sets this would be very "resource consuming"
         */
        $isAllItemsOverlimit = true;
        foreach ($properties['inputs'] ?? [] as $key => $value) {
            $isItemOverLimit = false;
            if (isset($properties['inputs'][$key]['label']['text'])) {
                $colValue = $properties['inputs'][$key]['label']['text'];
                if (!$this->isFieldMaxCountOk($formId, $fieldId, $colValue)) {
                    $isItemOverLimit = true;
                }
            }
            if (!$isItemOverLimit) {
                $isAllItemsOverlimit = false;
                break;
            }
        }
        /**
         * disable field with error message
         */
        if ($isAllItemsOverlimit) {
            $errorMessage = $this->getFieldErrorMsg($formId, $fieldId, 'maxcount-message');
            $isErrorMessageAdded = false;
            if (isset($properties['label']['attr'])) {
                $properties['label']['attr']['style'] = $properties['label']['attr']['style'] ?? '';
                $properties['label']['attr']['style'] .= 'color: #cdcdcd';
            }
            // selectbox specific
            if ($field['type'] == 'select' && isset($properties['description']['value'])) {
                $br = $properties['description']['value'] ? '<br>' : '';
                $properties['description']['value'] = $br . '<span style="color:#990000">' . __($errorMessage) . '</span>';
                $isErrorMessageAdded = true;
            }
            if (isset($properties['input_container']['attr'])) {
                $properties['input_container']['attr']['disabled'] = 'disabled';
            }
            foreach ($properties['inputs'] as $key => $value) {
                if (isset($properties['inputs'][$key]['label']['attr'])) {
                    $style = $properties['inputs'][$key]['label']['attr']['style'] ?? '';
                    $properties['inputs'][$key]['label']['attr']['style'] = trim($style . ';color: #cdcdcd', ';');
                }
                // input checkbox (and... ?) specific
                if (!$isErrorMessageAdded && isset($properties['inputs'][$key]['label']['text'])) {
                    $properties['inputs'][$key]['label']['text'] .= '<br><span style="color:#990000">' . __($errorMessage) . '</span>';
                    $isErrorMessageAdded = true;
                }
                if (isset($properties['inputs'][$key]['attr'])) {
                    $properties['inputs'][$key]['attr']['disabled'] = 'disabled';
                }
            }
            if (!$isErrorMessageAdded && isset($properties['error']['value'])) {
                $properties['error']['value'] = __($errorMessage);
            }
        }

        return $properties;
    }

    private function isFormMaxCountOk($formId)
    {
        $wpdb = $this->db->getDb();
        $config = $this->config->getWp();
        $tbl = $this->db->getFormTable($formId);

        $maxCountAll = isset($config[$formId]['maxcount'])
            ? (int) $config[$formId]['maxcount']
            : -1;

        if ($maxCountAll >= 0) {
            $count = $wpdb->get_var(
                "
                    SELECT COUNT(*)
                    FROM `{$tbl}`
                "
            );
            if ($count >= $maxCountAll) {
                return false;
            }

        }
        return true;
    }

    private function getFormErrorMsg($formId, $key)
    {
        $config = $this->config->getWp();

        $default = 'Error!';
        if ($key === 'maxcount-message') {
            $default = Config::ERRORMSG_FORM_MAXCOUNT_DEFAULT;
        }

        return $config[$formId][$key] ?? $default;
    }

    private function isFieldMaxCountOk($formId, $fieldId, $colValue)
    {
        $wpdb = $this->db->getDb();
        $config = $this->config->getWp();
        $tbl = $this->db->getFormTable($formId);

        $maxCountField = isset($config[$formId]['fields'][$fieldId]['maxcount'])
            ? (int) $config[$formId]['fields'][$fieldId]['maxcount']
            : -1;

        // check uniqueness only if some value is provided
        //  because unchecked groups have empty values etc
        if ($maxCountField >= 0 && $colValue) {
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
                return false;
            }

        }
        return true;
    }

    private function isFieldReqgroupOk($formId, $fieldId, $formFields, $postDataFields)
    {
        $config = $this->config->getWp();

        $colValue = $postDataFields[$fieldId] ?? '';
        $reqGroupField = $config[$formId]['fields'][$fieldId]['reqgroup'] ?? '';
        if ($reqGroupField && !$colValue) {
            $reqGroupFieldOk = false;
            foreach ($formFields as $value2) {
                $fieldId2 = $value2['id'] ?? 0;
                if ($fieldId === $fieldId2) {
                    continue;
                }
                $reqGroupField2 = $config[$formId]['fields'][$fieldId2]['reqgroup'] ?? '';
                if ($reqGroupField === $reqGroupField2) {
                    $colValue2 = $postDataFields[$fieldId2] ?? '';
                    if ($colValue2) {
                        $reqGroupFieldOk = true;
                        break;
                    }
                }
            }
            if (!$reqGroupFieldOk) {
                return false;
            }
        }
        return true;
    }

    private function getFieldErrorMsg($formId, $fieldId, $key)
    {
        $config = $this->config->getWp();

        $default = 'Error!';
        if ($key === 'maxcount-message') {
            $default = Config::ERRORMSG_FIELD_MAXCOUNT_DEFAULT;
        } elseif ($key === 'reqgroup-message') {
            $default = Config::ERRORMSG_FIELD_REQGROUP_DEFAULT;
        }

        return $config[$formId]['fields'][$fieldId][$key] ?? $default;
    }

    public function getErrors($errors, $formData): array
    {
        $formId = $formData['id'] ?? 0;
        $postData = $_POST['wpforms'] ?? [];

        $this->db->createTable($formId);

        /**
         * check all form records maxcount option
         */
        if (!$this->isFormMaxCountOk($formId)) {
            if (!isset($errors[$formId]['header'])) {
                $errors[$formId]['header'] = '';
            }
            $errors[$formId]['header'] .= ' ' . __($this->getFormErrorMsg($formId, 'maxcount-message'));
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
            if (!$this->isFieldMaxCountOk($formId, $fieldId, $colValue)) {
                if (!isset($errors[$formId][$fieldId])) {
                    $errors[$formId][$fieldId] = '';
                }
                $errors[$formId][$fieldId] .= ' ' . __($this->getFieldErrorMsg($formId, $fieldId, 'maxcount-message'));
            }
            /**
             * check reqgroup option
             */
            if (!$this->isFieldReqgroupOk($formId, $fieldId, $formData['fields'], $postData['fields'])) {
                if (!isset($errors[$formId][$fieldId])) {
                    $errors[$formId][$fieldId] = '';
                }
                $errors[$formId][$fieldId] .= ' ' . __($this->getFieldErrorMsg($formId, $fieldId, 'reqgroup-message'));
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



