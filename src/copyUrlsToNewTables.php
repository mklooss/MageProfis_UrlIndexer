<?php

/**
 * this script can be used to add all old data to the new table 
 * and reindex the new categories
 * 
 * only use this script for the initial action
 */

$dir = __DIR__;
if (isset($_SERVER['SCRIPT_FILENAME']))
{
    $dir = dirname($_SERVER['SCRIPT_FILENAME']);
}
chdir($dir);

require_once 'app/Mage.php';

Mage::app('admin')->setUseSessionInUrl(false);

$installer = new MageProfis_UrlIndexer_Model_Resource_Setup();

echo date('Y-m-d H:i:s')." - Truncate New Tables\n";
$installer->truncateNewTables();

echo date('Y-m-d H:i:s')." - Copy All Category URLs to Category Index Table\n";
$installer->copyAllActiveCategories();

echo date('Y-m-d H:i:s')." - Copy All URLs\n";
$installer->copyAllItemsToRedirectTable();

echo date('Y-m-d H:i:s')." - Truncate Magento URL Index Table\n";
$installer->truncateMagentoTables();

echo date('Y-m-d H:i:s')." - Add Database Index\n";
$installer->addIndexElementToTable();

echo date('Y-m-d H:i:s')." - Reindex Catalog URL\n";
$installer->reindexUrls();

echo date('Y-m-d H:i:s')." - Done\n";
