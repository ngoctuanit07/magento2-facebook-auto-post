<?php

/**
 * @Author: Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Creation time: 2017-06-07 18:37:39
 * @Last modified time: 2017-06-11 05:57:37
 * @link: http://www.giaphugroup.com
 *
 */

namespace PHPCuong\FacebookAutoPost\Observer\Facebook;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PostToWall implements ObserverInterface
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     *
     * @var \PHPCuong\FacebookAutoPost\Model\Source\FacebookSettings
     */
    protected $facebookSettings;

    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\Store $store,
        \PHPCuong\FacebookAutoPost\Model\Source\FacebookSettings $facebookSettings,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_store = $store;
        $this->facebookSettings = $facebookSettings;
        $this->messageManager = $messageManager;
    }

    /**
     * Handler for 'controller_action_catalog_product_save_entity_after' event.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');
        $productName = $product->getName();
        $productId = $product->getEntityId();
        $urlKey = $product->getUrlKey();
        foreach ($product->getStoreIds() as $storeId) {
            // get the first store id
            break;
        };
        $productUrl = $this->_storeManager->getStore($storeId)->getBaseUrl().$urlKey.$this->getCatalogSeoProductUrlSuffix($storeId);
        $short_description = $product->getShortDescription();
        if (empty($short_description)) {
            $short_description = $product->getDescription();
        }
        $short_description = $this->cut_text(strip_tags($short_description));
        $imageUrl = $this->_storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA). 'catalog/product' . $product->getImage();
        $app_id = $this->facebookSettings->getFacebookSetting('catalog/facebook_auto_post/application_id');
        $app_secret = $this->facebookSettings->getFacebookSetting('catalog/facebook_auto_post/application_secret');
        $pages = $this->facebookSettings->getFacebookSetting('catalog/facebook_auto_post/pages');
        $access_token = 'EAAQJ3VSq79YBAIW1triazViJ3P5Syd9PODvDDi0aMhkvdcA8GTo8y29EYITmSQwXgK6zN2bMmggTXjdUp3WknJLVvmpACknWa58nI5cYn6QlZBCCJkeZAvWE9XRzE5LxkedSZBDAWOvICEEQtmNXq0DLj1PtWn3mzSEgjgLxAZDZD';
        //$access_token = 'EAAQJ3VSq79YBADZCu3nhbQbefzCLLQZAhA2MILcy4ltSoIUx8bpyYV4WD6qDfYJwTU4BBG2r2vh3xhkRQsqXCK49kZBGWo8ZB1Tj27FMhtWSWlJMGQYnHAwMfCDEZA9VtQfapjNDwE8FiYgxU9dZBAvVvSHOXM6HqHoZBgEz3vBOwZDZD';
    //     $token_url = "https://graph.facebook.com/v2.8/oauth/access_token?"
    // . "client_id=" . $app_id . "&redirect_uri=http://magento.giaphugroup.com/admin/&client_secret=" . $app_secret.'&code=AQASSt_XnYugooZidSu8VJcfoAoahhTWaXHL6J3c1EjpuxSakGqCfq--4NIpqAPxcly7uO8SH4ew2qw2uIhzfvDsWk6cJrIvfniBlkPVpdL9rins2t_dxo0YBQvFw5ZcPNCw4jWy5etGRt7NsMCKHZGYwOa_ukAzRvbSlhkVzRUyVdpFQVYcZS-isCe0kf23Pxl-WVeUcDlNnWQ6cmfmRtjXkwccl84WsafAW8mTmNPO8y6fGry-tdrREqxBt0nxFFpXcoWZi6hg8TSKLuC1Mvn_ELHWpO8egbTZMVHEz0Emoal9AeN4sThSDwNh97EIhx-0XlczVu-quFhxl9UsH5cv#_=_';
        $fb = new \Facebook\Facebook([
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'cookie' => false
        ]);
        $linkData = array(
            "message" => $short_description,
            "link" => $productUrl,
            "picture" => $imageUrl,
            "name" => $productName,
            "caption" => "www.pontikis.net",
            "description" => $short_description
        );
        try {
          // Returns a `Facebook\FacebookResponse` object
          $response = $fb->post('/me/feed', $linkData, $access_token);
          exit();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          echo 'Graph returned an error: ' . $e->getMessage();
          exit();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit();
        }

        $graphNode = $response->getGraphNode();

        echo 'Posted with id: ' . $graphNode['id'];

    }

    /**
     * Get catalog seo product url suffix
     *
     * @return string
     */
    protected function getCatalogSeoProductUrlSuffix($storeId)
    {
        $scope_stores = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $suffix = $this->_scopeConfig->getValue('catalog/seo/product_url_suffix', $scope_stores, $storeId);

        if ($suffix == null) {
            $websiteId = $this->_store->load($storeId)->getWebsiteId();
            $websites = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
            $suffix = $this->_scopeConfig->getValue('catalog/seo/product_url_suffix', $websites, $websiteId);
        }

        if ($suffix == null) {
            $suffix = $this->_scopeConfig->getValue('catalog/seo/product_url_suffix');
        }

        if ($suffix) {
            return $suffix;
        }

        return '';
    }

    /**
     * Cut the long string
     *
     * @return string
     */
    protected function cut_text($string)
    {
        $exp = explode(' ', $string, 160);
        unset($exp[159]);
        return implode(" ", $exp);
    }
}
