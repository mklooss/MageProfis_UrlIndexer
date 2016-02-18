<?php

class MageProfis_UrlIndexer_Controller_Router
extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();

        $front->addRouter('urlindexer', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $identifier = trim($request->getPathInfo(), '/');

        // so we have different possible urls
        $ident = array(
            $identifier,
            $identifier.'/'
        );

        // check both stores
        $stores = array(
            0,
            (int) Mage::app()->getStore()->getId()
        );

        $query = $this->_getConnection()->select()
                ->from($this->getTableName('urlindexer/url_rewrite_redirects'), array('product_id', 'category_id', 'target_path'))
                ->where('store_id IN (?)', $stores)
                ->where('request_path IN (?)', $ident)
                ->order('store_id DESC')
                ->limit(1)
        ;
        $result = $this->_getConnection()->fetchRow($query);
        $url = null;
        if ($result)
        {
            $url = $this->getProductUrl($result, $url);
            $url = $this->getCategoryUrl($result, $url);
            if ($url)
            {
                Mage::app()->getResponse()
                        ->setRedirect($url, 301)
                        ->sendResponse();
                exit;
            }
        }
    }

    /**
     * get Product Url
     * 
     * @param string $request
     * @param string $url
     * @return string
     */
    protected function getProductUrl($request, $url = null)
    {
        if (!empty($request['product_id']))
        {
            $model = Mage::getModel('catalog/product')->load($request['product_id']);
            /* @var $model Mage_Catalog_Model_Product */
            if ($model->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                &&
                in_array(intval($model->getVisibility()), array(2, 3, 4)))
            {
                return $model->getProductUrl();
            }
        }

        if (is_null($url) && $request['options'] == 'RP')
        {
            return $this->_getUrl($request['target_path']);
        }
        return $url;
    }

    /**
     * 
     * @param mixed $request
     * @param string|null $url
     * @return string
     */
    protected function getCategoryUrl($request, $url = null)
    {
        if (empty($request['product_id']) && !empty($request['category_id']))
        {
            $category = Mage::getModel('catalog/category')->load($request['category_id']);
            /* @var $category Mage_Catalog_Model_Category */
            if ($category->getIsActive())
            {
                return $category->getUrl();
            }
        }

        if (is_null($url) && $request['options'] == 'RP')
        {
            return $this->_getUrl($request['target_path']);
        }
        return $url;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = array())
    {
        $suffix = '/';
        $route = Mage::getUrl($route);
        if (substr($route, -1) == '/')
        {
            $suffix = '/';
        }
        $route = trim($route, '/');
        return $route.$suffix;
    }

    /**
     * 
     * @return Mage_Core_Model_Resource
     */
    protected function _resource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * 
     * @param type $modelEntity
     * @return type
     */
    protected function getTableName($modelEntity)
    {
        return $this->_resource()->getTableName($modelEntity);
    }

    /**
     * 
     * @param string $name
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection($name = 'core_read')
    {
        return $this->_resource()->getConnection($name);
    }
}