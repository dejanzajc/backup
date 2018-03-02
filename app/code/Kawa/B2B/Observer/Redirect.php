<?php

namespace Kawa\b2b\Observer;

use \Magento\Store\Model\ScopeInterface;

class Redirect implements \Magento\Framework\Event\ObserverInterface
{
    const XML_PATH_B2B_ALLOWED_PAGES = 'b2b/general/allowed_pages';
    const XML_PATH_B2B_REDIRECT_TO   = 'b2b/general/redirect_to';

    /**
     * @var \Kawa\B2B\Helper\Data
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

    public function __construct(
        \Kawa\B2B\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper       = $helper;
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isB2B()) {
            return;
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
                return;
            }
        }

        $observer->getControllerAction()
            ->getResponse()
            ->setRedirect($this->getRedirectPage());
    }

    /**
     * Returns an array of allowed pages.
     *
     * @return array
     */
    protected function getAllowedPages()
    {
        return preg_split('/\r\n|\r|\n/', $this->scopeConfig->getValue(
            self::XML_PATH_B2B_ALLOWED_PAGES,
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get redirect page url.
     *
     * @return string
     */
    protected function getRedirectPage()
    {
        return $this->storeManager->getStore()->getUrl(
            $this->scopeConfig->getValue(
                self::XML_PATH_B2B_REDIRECT_TO,
                ScopeInterface::SCOPE_STORE
            )
        );
    }
}
