<?php

class MageProfis_UrlIndexer_Model_Resource_Setup
extends Mage_Core_Model_Resource_Setup
{
    /**
     * 
     * @return MageProfis_UrlIndexer_Model_Resource_Setup
     */
    public function copyAllItemsToRedirectTable()
    {
        $originTableName = $this->getTable('core/url_rewrite');
        $newTableName    = $this->getTable('urlindexer/url_rewrite_redirects');

        $this->getConnection()
                ->query('INSERT INTO `'.$newTableName.'` (`store_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`, `category_id`, `product_id`) 
SELECT cur.`store_id`, cur.`id_path`, cur.`request_path`, cur.`target_path`, cur.`is_system`, cur.`options`, cur.`description`, cur.`category_id`, cur.`product_id` FROM `'.$originTableName.'` cur
ON DUPLICATE KEY UPDATE id_path = cur.id_path');
        return $this;
    }
}
