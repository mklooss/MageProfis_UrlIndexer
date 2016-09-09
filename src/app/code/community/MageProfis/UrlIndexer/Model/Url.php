<?php

class MageProfis_UrlIndexer_Model_Url
extends Mage_Catalog_Model_Url
{
    /**
     * Refresh product rewrite
     *
     * @param Varien_Object $product
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshProductRewrite(Varien_Object $product, Varien_Object $category)
    {
        if ($category->getId() == $category->getPath()) {
            return $this;
        }

        if ($this->_hasProductUrl($product, $category))
        {
            return $this;
        }
        return parent::_refreshProductRewrite($product, $category);
    }

    /**
     * Refresh category rewrite
     *
     * @param Varien_Object $category
     * @param string $parentPath
     * @param bool $refreshProducts
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshCategoryRewrites(Varien_Object $category, $parentPath = null, $refreshProducts = true)
    {
        if ($this->_hasCategoryUrl($category))
        {
            return $this;
        }
        return parent::_refreshCategoryRewrites($category, $parentPath, $refreshProducts);
    }

    /**
     * 
     * @param Varien_Object $category
     * @return boolean
     */
    protected function _hasCategoryUrl(Varien_Object $category)
    {
        if (Mage::getStoreConfigFlag('dev/index/regen_category_url', $category->getStoreId()))
        {
            return false;
        }
        $query = $this->_connection()->select()
                ->from($this->_resource()->getTableName('core_url_rewrite'), 'id_path')
                ->where('store_id = ?', (int) $category->getStoreId())
                ->where('category_id = ?', $category->getId())
                ->where('id_path = ?', 'category/'.$category->getId())
                ->limit(1)
        ;

        $result = $this->_connection()->fetchOne($query);
        if (!empty($result))
        {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param Varien_Object $product
     * @param Varien_Object $category
     * @return boolean
     */
    protected function _hasProductUrl(Varien_Object $product, Varien_Object $category)
    {
        $categoryId = null;
        $storeId = (int) $category->getStoreId();
        $path = 'product/'.$product->getId();
        if ($category->getLevel() > 1) {
            $categoryId = $category->getId();
            $path = $path.'/'.$categoryId;
        }

        // if category id is set do not generate an url
        if (intval($categoryId) > 0
                && !Mage::getStoreConfigFlag('catalog/seo/product_use_categories', $category->getStoreId()))
        {
            return true;
        }

        $query = $this->_connection()->select()
                ->from($this->_resource()->getTableName('core_url_rewrite'), 'id_path')
                ->where('store_id = ?', (int) $storeId)
                ->where('product_id = ?', $product->getId())
                ->where('id_path = ?', $path)
                ->limit(1)
        ;
        if (!isset($categoryId) || is_null($categoryId))
        {
            $query->where('category_id IS NULL');
        } else {
            $query->where('category_id = ?', $categoryId);
        }
        $result = $this->_connection()->fetchOne($query);

        if (!empty($result))
        {
            return true;
        }
        return false;
    }

    /**
     * @return Mage_Core_Model_Resource
     */
    protected function _resource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * 
     * @param string $type
     * @return Varien_Db_Adapter_Interface
     */
    protected function _connection($type = 'core_read')
    {
        return $this->_resource()->getConnection($type);
    }
}