<?php

class MageProfis_UrlIndexer_Model_Observer
extends Mage_Core_Model_Abstract
{
    /**
     * 
     * @mageEvent catalog_category_prepare_save
     * @param Varien_Event_Observer $event
     */
    public function catalogCategorySave(Varien_Event_Observer $event)
    {
        $category = $event->getCategory();
        /* @var $category Mage_Catalog_Model_Category */
        if ($category && $category->getId() && $category->getData('save_rewrites_history'))
        {
            $this->copyCategoryUrlToRedirectTable($category->getId(), true);
        }
    }

    /**
     * @mageEvent catalog_product_prepare_save
     * @param Varien_Event_Observer $event
     */
    public function catalogProductSave(Varien_Event_Observer $event)
    {
        $product = $event->getProduct();
        /* @var $product Mage_Catalog_Model_Product */
        if ($product && $product->getId() && $product->getData('save_rewrites_history'))
        {
            $this->copyProductUrlToRedirectTable($product->getId(), true);
        }
    }

    /**
     * 
     * @param type $id
     * @param type $remove
     */
    protected function copyCategoryUrlToRedirectTable($id, $remove = false)
    {
        $id = (int) abs($id);
        if ($id)
        {
            $sql = $this->_connection()->select()
                    ->from($this->_resource()->getTableName('core/url_rewrite'))
                    ->where('category_id = ?', $id)
                    ->where('id_path = ?', 'category/'.$id)
            ;
            foreach ($this->_connection()->fetchAll($sql) as $_item)
            {                
                $data = array(
                    'store_id'      => (int) $_item['store_id'],
                    'id_path'       => md5($_item['id_path'].date('r')),
                    'request_path'  => $_item['request_path'],
                    'target_path'   => $_item['target_path'],
                    'is_system'     => (int) $_item['is_system'],
                    'options'       => $_item['options'],
                    'description'   => $_item['description'],
                    'category_id'   => (int) $_item['category_id'],
                    'product_id'    => $_item['product_id'],
                );
                $this->_connection('core_write')
                        ->insertOnDuplicate($this->_resource()->getTableName('urlindexer/url_rewrite_redirects'), $data, array('request_path', 'target_path', 'category_id'));
                if ($remove)
                {
                    $where = $this->_connection()->quoteInto('url_rewrite_id = ?', (int) $_item['url_rewrite_id']);
                    $this->_connection('core_write')
                            ->delete($this->_resource()->getTableName('core/url_rewrite'), $where);
                }
            }
        }
    }

    /**
     * 
     * @param type $id
     * @param type $remove
     */
    protected function copyProductUrlToRedirectTable($id, $remove = false)
    {
        $id = (int) abs($id);
        if ($id)
        {
            $sql = $this->_connection()->select()
                    ->from($this->_resource()->getTableName('core/url_rewrite'))
                    ->where('product_id = ?', $id)
                    ->where('id_path = ?', 'product/'.$id)
            ;
            foreach ($this->_connection()->fetchAll($sql) as $_item)
            {                
                $data = array(
                    'store_id'      => (int) $_item['store_id'],
                    'id_path'       => md5($_item['id_path'].date('r')),
                    'request_path'  => $_item['request_path'],
                    'target_path'   => $_item['target_path'],
                    'is_system'     => (int) $_item['is_system'],
                    'options'       => $_item['options'],
                    'description'   => $_item['description'],
                    'category_id'   => $_item['category_id'],
                    'product_id'    => (int) $_item['product_id'],
                );

                $this->_connection('core_write')
                        ->insertOnDuplicate($this->_resource()->getTableName('urlindexer/url_rewrite_redirects'), $data, array('request_path', 'target_path', 'product_id'));
                if ($remove)
                {
                    $where = $this->_connection()->quoteInto('url_rewrite_id = ?', (int) $_item['url_rewrite_id']);
                    $this->_connection('core_write')
                            ->delete($this->_resource()->getTableName('core/url_rewrite'), $where);
                }
            }
        }
    }

    /**
     * Daily CronTask, to prevent Incorrect Data in the Table,
     * an keep the Database clean
     */
    public function renewCategoryUrlRewriteTable()
    {
        if (Mage::getStoreConfigFlag('dev/index/optimize_categorie_leftjoin'))
        {
            Mage::helper('urlindexer')->copyAllCategories();
        }
    }

    /**
     * @return Mage_Core_Model_Resource
     */
    protected function _resource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * @param string $name
     * @return Varien_Db_Adapter_Interface
     */
    protected function _connection($name = 'core_read')
    {
        return $this->_resource()->getConnection($name);
    }
}