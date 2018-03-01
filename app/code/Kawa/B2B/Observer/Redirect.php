<?php

namespace Kawa\b2b\Observer;

class Redirect implements \Magento\Framework\Event\ObserverInterface
{
    const XML_PATH_B2B_ALLOWED_PAGES = 'B2B/general/allowed_pages';
    const XML_PATH_B2B_REDIRECT_TO   = 'B2B/general/redirect_to';

    /**
     * @var \Kawa\B2B\Helper\B2b
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * Store Manager Model
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Redirect constructor.
     *
     * @param \Kawa\B2B\Helper\B2b $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Kawa\B2B\Helper\B2b $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper       = $helper;
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isB2bShop()) {
            return $this;
        }

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        foreach ($this->getAllowedPages() as $page) {
            $page = explode('/', $page);

            if (!isset($page[1])) {
                $page[1] = 'index';
            }
            if (!isset($page[2])) {
                $page[2] = 'index';
            }

            if (($request->getModuleName() == $page[0])
                && ($request->getControllerName() == $page[1] || $page[1] == '*')
                && ($request->getActionName() == $page[2] || $page[2] == '*')
            ) {
                return $this;
            }
        }

        return $observer->getControllerAction()
            ->getResponse()
            ->setRedirect($this->getRedirectPage());
    }

    private function getAllowedPages()
    {
        return preg_split('/\r\n|\r|\n/', $this->scopeConfig->getValue(
            self::XML_PATH_B2B_ALLOWED_PAGES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    private function getRedirectPage()
    {
        return $this->storeManager->getStore()->getUrl(
            $this->scopeConfig->getValue(
                self::XML_PATH_B2B_REDIRECT_TO,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }
}
