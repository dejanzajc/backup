<?php

namespace Kawa\StoreRedirect\Observer;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class LocaleRedirect implements \Magento\Framework\Event\ObserverInterface
{
    const XML_PATH_STORE_REDIRECT = 'store_redirect/general/store_redirect';
    const COOKIE_NAME             = 'language';
    const COOKIE_DURATION         = 60 * 60 * 24 * 30;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * Store Manager Model
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LocaleRedirect constructor.
     *
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->scopeConfig           = $scopeConfig;
        $this->storeManager          = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controllerAction = $observer->getControllerAction();
        if (($this->cookieManager->getCookie(self::COOKIE_NAME))
            || ($this->cookieManager->getCookie('store'))
            || ($controllerAction->getRequest()->getParam('___store'))
        ) {
            return $this;
        }

        // set cookie
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION);
        $this->cookieManager
            ->setPublicCookie(self::COOKIE_NAME, 1, $metadata);

        // redirect logic
        $locale = new \Zend_Locale();
        foreach ($this->getStoreRedirects() as $redirect) {
            $lang   = strtolower($redirect['lang']);
            $region = strtolower($redirect['region']);

            if (($lang == strtolower($locale->getLanguage()))
                && ($region == strtolower($locale->getRegion()) || empty($region))
            ) {
                /** @var \Magento\Store\Model\Store $store */
                $store = $this->storeManager->getStore($redirect['storecode']);
                if ($store->getIsActive()) {
                    return $controllerAction->getResponse()
                        ->setRedirect($store->getCurrentUrl(false));
                }
            }
        }

        return $this;
    }

    private function getStoreRedirects()
    {
        $array = json_decode($this->scopeConfig->getValue(
            self::XML_PATH_STORE_REDIRECT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ), true);

        usort($array, $this->buildSorter('region'));

        return array_reverse($array);
    }

    private function buildSorter($key)
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
}
