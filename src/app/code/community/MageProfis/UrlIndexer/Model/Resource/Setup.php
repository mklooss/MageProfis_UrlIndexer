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
        try {
            $this->getConnection()->truncateTable($this->getTable('urlindexer/url_rewrite_redirects'));
        } catch (Exception $e) {
            $this->getConnection()->delete($this->getTable('urlindexer/url_rewrite_redirects'));
            $this->getConnection()->truncateTable($this->getTable('urlindexer/url_rewrite_redirects'));
        }
        try {
            $this->getConnection()->truncateTable($this->getTable('urlindexer/url_rewrite_category'));
        } catch (Exception $e) {
            $this->getConnection()->delete($this->getTable('urlindexer/url_rewrite_category'));
            $this->getConnection()->truncateTable($this->getTable('urlindexer/url_rewrite_category'));
        }
        $core_url_rewrite = $this->getTables('core_url_rewrite');
        $catalog_product_entity = $this->getTables('catalog_product_entity');
        $catalog_category_entity = $this->getTables('catalog_category_entity');
        try {
            $this->getConnection()->query("DELETE FROM `{$core_url_rewrite}` WHERE product_id IS NOT NULL AND product_id NOT IN(SELECT entity_id FROM {$catalog_product_entity});");
        } catch (Exception $e) { }
        try {
            $this->getConnection()->query("DELETE FROM `{$core_url_rewrite}` WHERE category_id IS NOT NULL AND category_id NOT IN(SELECT entity_id FROM {$catalog_category_entity});");
        } catch (Exception $e) { }
    }

    /**
     * truncate magento core_url_rewrite
     */
    public function truncateMagentoTables()
    {
        try {
            $this->getConnection()->truncateTable($this->getTable('core/url_rewrite'));
        } catch (Exception $e) {
            $this->getConnection()->delete($this->getTable('core/url_rewrite'));
            $this->getConnection()->truncateTable($this->getTable('core/url_rewrite'));
        }
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

    /**
     * 
     * @return boolean
     */
    public function canAddIndexOnInstall()
    {
        $itemsCount = (int) $this->getConnection()->fetchOne('SELECT COUNT(*) as count FROM '.$this->getTable('core/url_rewrite'));
        if ($itemsCount > 2000)
        {
            return false;
        }
        return true;
    }

    /**
     * 
     */
    public function addIndexElementToTable()
    {
        try {
            $this->getConnection()
                ->addIndex(
                        $this->getTable('core/url_rewrite'),
                        $this->getIdxName('core/url_rewrite', array('category_id', 'is_system', 'product_id', 'store_id', 'id_path')),
                                array('category_id', 'is_system', 'product_id', 'store_id', 'id_path'),
                                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
                );
        } catch(Exception $e)
        {
            // ignore result, some stores have this key, some other not, so we add this here
            // and do not check on an error ;-)
            // is just an small performance tweak
        }
        return $this;
    }
}
