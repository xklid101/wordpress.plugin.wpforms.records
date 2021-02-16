<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

class Database
{
    const TBL_PREFIX = 'xklid101_wprecords_';

    public function getDb()
    {
        global $wpdb;

        return $wpdb;
    }

    /**
     * get table name for formId
     *
     * @param  [type] $formId [description]
     * @return [type]         [description]
     */
    public function getFormTable($formId): string
    {
        $db = $this->getDb();
        return "{$db->prefix}" . self::TBL_PREFIX . "$formId";
    }

    /**
     * create table for formId if not exists
     *
     * @param  [type] $formId [description]
     * @return [type]         [description]
     */
    public function createTable($formId)
    {
        $tbl = $this->getFormTable($formId);
        $this->getDb()->query("
            CREATE TABLE IF NOT EXISTS `{$tbl}`
            (
                `__id` int NOT NULL AUTO_INCREMENT,
                `__time_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `__time_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `__note` text NULL,
                PRIMARY KEY (`__id`)
            )

        ");
    }

    public function getColumns($formId, $repeatIfEmpty = true)
    {
        $tbl = $this->getFormTable($formId);
        $wpdb = $this->getDb();

        $cols = $wpdb->get_col(
            $wpdb->prepare(
                "
                    SELECT `column_name`
                    FROM information_schema.columns
                    WHERE table_schema = %s
                    AND table_name = %s
                ",
                [
                    $wpdb->dbname,
                    $tbl
                ]

            )
        );
        if (!$cols && $repeatIfEmpty) {
            $this->createTable($formId);
            return $this->getColumns($formId, false);
        }

        return $cols ?: [];
    }

    /**
     * add column for formId if not exists
     *
     * @param [type] $formId  [description]
     * @param [type] $fieldId [description]
     */
    public function addColumn($formId, $fieldId)
    {
        $tbl = $this->getFormTable($formId);
        $wpdb = $this->getDb();

        $col = $wpdb->get_col(
            $wpdb->prepare(
                "
                    SELECT `column_name`
                    FROM information_schema.columns
                    WHERE table_schema = %s
                    AND table_name = %s
                    AND column_name = %s
                ",
                [
                    $wpdb->dbname,
                    $tbl,
                    $fieldId
                ]

            )
        );
        if (!$col) {
            $wpdb->query("ALTER TABLE `{$tbl}` ADD COLUMN `{$fieldId}` TEXT NULL");
        }
    }
}



