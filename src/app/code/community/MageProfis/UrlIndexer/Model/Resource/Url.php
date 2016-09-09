<?php

class MageProfis_UrlIndexer_Model_Resource_Url
extends Mage_Catalog_Model_Resource_Url
{
    public function saveRewrite($rewriteData, $rewrite)
    {
        parent::saveRewrite($rewriteData, $rewrite);
        if (Mage::getStoreConfigFlag('dev/index/optimize_categorie_leftjoin', (int) $rewriteData['store_id']))
        {
            $this->_saveUrlIndexerRewrite($rewriteData, $rewrite);
        }
        return $this;
    }
    
    /**
     * Save urlindexer rewrite URL
     *
     * @param array $rewriteData
     * @param int|Varien_Object $rewrite
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    protected function _saveUrlIndexerRewrite($rewriteData, $rewrite, $retry = true)
    {
        if (isset($rewriteData['category_id']) && !empty($rewriteData['category_id'])
               && strstr($rewriteData['id_path'], 'category/'))
        {
            $adapter = $this->_getWriteAdapter();
            try {
                $adapter->insertOnDuplicate($this->getTable('urlindexer/url_rewrite_category'), $rewriteData, array('request_path', 'target_path', 'category_id'));
            } catch (Exception $e) {
                // sometimes, there is an issue with the unqiue key
                // so we retry it direct
                if (!$retry)
                {
                    Mage::logException($e);
                    Mage::throwException(Mage::helper('urlindexer')->__('An error occurred while saving the URL rewrite in urlindexer'));
                } else {
                    $where = array(
                        'store_id    = ?' => (int)$rewriteData['store_id'],
                        'category_id = ?' => (int)$rewriteData['category_id'],
                        'id_path = ?'     => $rewriteData['id_path'],
                    );
                    $adapter->delete(
                        $this->getTable('urlindexer/url_rewrite_category'),
                        $where
                    );
                    $this->_saveUrlIndexerRewrite($rewriteData, $rewrite, false);
                }
            }
            
            // delete old entry!
            if ($rewrite && $rewrite->getId()) {
                if ($rewriteData['request_path'] != $rewrite->getRequestPath()) {
                    // Update existing rewrites history and avoid chain redirects
                    $where = array('target_path = ?' => $rewrite->getRequestPath());
                    if ($rewrite->getStoreId()) {
                        $where['store_id = ?'] = (int)$rewrite->getStoreId();
                    }
                    $adapter->delete(
                        $this->getTable('urlindexer/url_rewrite'),
                        $where
                    );
                }
            }
        }
    }
}