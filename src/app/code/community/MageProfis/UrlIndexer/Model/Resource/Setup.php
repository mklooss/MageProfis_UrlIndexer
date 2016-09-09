<?php

class MageProfis_UrlIndexer_Model_Resource_Setup
extends Mage_Core_Model_Resource_Setup
{
    /**
     * truncate core_url_rewrite_redirects
     * truncate core_url_rewrite_category
     */
    public function truncateNewTables()
    {
        $this->getConnection()->delete($this->getTable('urlindexer/url_rewrite_redirects'));
        $this->getConnection()->delete($this->getTable('urlindexer/url_rewrite_category'));
        $this->getConnection()->truncateTable($this->getTable('urlindexer/url_rewrite_redirects'));
        $this->getConnection()->truncateTable($this->getTable('urlindexer/url_rewrite_category'));
    }

    /**
     * truncate magento core_url_rewrite
     */
    public function truncateMagentoTables()
    {
        $this->getConnection()->delete($this->getTable('core/url_rewrite'));
        $this->getConnection()->truncateTable($this->getTable('core/url_rewrite'));
    }

    /**
     * reindex catalog_url
     */
    public function reindexUrls()
    {
        $indexer = Mage::getModel('index/indexer')->getProcessByCode('catalog_url');
        if ($indexer)
        {
            $indexer->reindexAll();
        }
    }

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

    /**
     * 
     * @return MageProfis_UrlIndexer_Model_Resource_Setup
     */
    public function copyAllActiveCategories()
    {
        $originTableName = $this->getTable('core/url_rewrite');
        $newTableName    = $this->getTable('urlindexer/url_rewrite_category');

        $this->getConnection()
                ->query("INSERT INTO `{$newTableName}` (`store_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`, `category_id`, `product_id`) 
SELECT cur.`store_id`, cur.`id_path`, cur.`request_path`, cur.`target_path`, cur.`is_system`, cur.`options`, cur.`description`, cur.`category_id`, cur.`product_id` FROM `{$originTableName}` cur
WHERE id_path LIKE 'category/%'
ON DUPLICATE KEY UPDATE id_path = cur.id_path");
        return $this;
    }
}