<?php

class Brontosoftware_Connector_Model_Impl_Core_CategoryResolverBridge implements Brontosoftware_Magento_Core_Catalog_CategoryResolverFactoryInterface
{
    /**
     * @see parent
     */
    public function create($resolver, $product)
    {
        $settings = Mage::getSingleton('brontosoftware_product/categorySettings');
        $params = array(
            'branch' => ';',
            'leaf' => ' ',
            'format' => 'urlKey',
            'resolver' => 'single',
            'level' => null,
            'tiebreaker' => null
        );
        if ($settings) {
            $params['branch'] = $settings->getCategoryEncapsulation('store', $product->getStoreId());
            $params['leaf'] = $settings->getCategoryDelimiter('store', $product->getStoreId());
            $params['format'] = $settings->getCategoryFormat('store', $product->getStoreId());
            $params['level'] = $settings->getCategorySpecificity('store', $product->getStoreId());
            $params['tiebreaker'] = $settings->getCategoryBroadness('store', $product->getStoreId());
            $params['resolver'] = 'single';
            switch ($resolver) {
            case 'tree':
                $params['resolver'] = 'all';
                break;
            case 'all_leaves':
                $params['resolver'] = 'leaves';
                break;
            case 'first_lowest':
                $params['resolver'] = 'single';
                $params['level'] = 'lowest';
            }
        }
        return new Brontosoftware_Magento_Core_Catalog_CategoryResolverGeneric(
            $params['branch'],
            $params['leaf'],
            $params['format'],
            $params['resolver'],
            $params['level'],
            $params['tiebreaker']
        );
    }
}
