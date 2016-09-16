<?php

class MageProfis_UrlIndexer_Helper_Data
extends Mage_Core_Helper_Abstract
{
    /**
     * 
     * @return MageProfis_UrlIndexer_Model_Resource_Setup
     */
    public function copyAllCategories()
    {
        $resource = Mage::getSingleton('core/resource');
        /* @var $resource Mage_Core_Model_Resource */
        $writeConn = $resource->getConnection('core_write');
        /* @var $writeConn Varien_Db_Adapter_Interface */
        
        $originTableName = $resource->getTableName('core/url_rewrite');
        $newTableName    = $resource->getTableName('urlindexer/url_rewrite_category');
        
        try {
            $writeConn->truncateTable($newTableName);
        } catch (Exception $e) {
            $writeConn->delete($newTableName);
            $writeConn->truncateTable($newTableName);
        }

        $writeConn
                ->query("INSERT INTO `{$newTableName}` (`store_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`, `category_id`, `product_id`) 
SELECT cur.`store_id`, cur.`id_path`, cur.`request_path`, cur.`target_path`, cur.`is_system`, cur.`options`, cur.`description`, cur.`category_id`, cur.`product_id` FROM `{$originTableName}` cur
WHERE id_path LIKE 'category/%'
ON DUPLICATE KEY UPDATE id_path = cur.id_path");
        return $this;
    }
}