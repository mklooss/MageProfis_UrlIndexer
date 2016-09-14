<?php

$installer = $this;
/* @var $installer MageProfis_UrlIndexer_Model_Resource_Setup */
$installer->startSetup();

$newTableName = $installer->getTable('urlindexer/url_rewrite_redirects');
$incorrectTable = $installer->getTable($newTableName);

// check old installer issue
if ($incorrectTable != $newTableName)
{
    // Drop Table if the other one does not exists
    if ($installer->tableExists($incorrectTable) && !$installer->tableExists($newTableName))
    {
        $installer->getConnection()->renameTable($incorrectTable, $newTableName);
    // Drop Table if booth exists
    } elseif ($installer->tableExists($newTableName) && $installer->tableExists($incorrectTable))
    {
        $installer->getConnection()->dropTable($incorrectTable);
    }
}

$installer->endSetup();