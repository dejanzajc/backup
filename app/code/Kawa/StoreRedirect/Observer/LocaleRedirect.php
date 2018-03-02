<?php

namespace Kawa\StoreRedirect\Observer;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Store\Model\StoreCookieManager;
use Magento\Store\Api\StoreResolverInterface;

class LocaleRedirect implements \Magento\Framework\Event\ObserverInterface
{
    const XML_PATH_STORE_REDIRECT = 'store_redirect/general/store_redirect';

    const COOKIE_NAME     = 'language';
    const COOKIE_DURATION = 60 * 60 * 24 * 30;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->scopeConfig           = $scopeConfig;
        $this->storeManager          = $storeManager;
        $this->logger                = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controllerAction = $observer->getControllerAction();

        if ($this->cookieManager->getCookie(self::COOKIE_NAME)
            || $this->cookieManager->getCookie(StoreCookieManager::COOKIE_NAME)
            || $controllerAction->getRequest()->getParam(StoreResolverInterface::PARAM_NAME)
        ) {
            return;
        }

        try {
            // Set cookie
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration(self::COOKIE_DURATION);
            $this->cookieManager->setPublicCookie(self::COOKIE_NAME, 1, $metadata);

            // Redirect logic
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
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    protected function getStoreRedirects()
    {
        $storeRedirects = json_decode($this->scopeConfig->getValue(
            self::XML_PATH_STORE_REDIRECT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ), true);

        usort($storeRedirects, $this->buildSorter('region'));

        return $storeRedirects;
    }

    protected function buildSorter($key)
    {
        return function ($a, $b) use ($key) {
            return -1 * strnatcmp($a[$key], $b[$key]);
        };
    }
}
