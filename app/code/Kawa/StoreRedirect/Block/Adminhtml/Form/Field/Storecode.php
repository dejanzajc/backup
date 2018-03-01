<?php

namespace Kawa\StoreRedirect\Block\Adminhtml\Form\Field;

class Storecode extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Store Manager Model
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Activation constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $value
     * @return \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $attributes = $this->getWebsitesList();

            foreach ($attributes as $attribute) {
                $this->addOption($attribute['value'], $attribute['label']);
            }
        }

        return parent::_toHtml();
    }

    public function getWebsitesList()
    {
        $out = [];
        foreach ($this->storeManager->getStores($withDefault = false) as $store) {
            $storeGroup   = $this->storeManager->getGroup($store->getStoreGroupId());
            $storeWebsite = $this->storeManager->getWebsite($store->getWebsiteId());
            $out[]        = [
                'value' => $store->getId(),
                'label' => $storeWebsite->getName() . '/' . substr($storeGroup->getName(), 0, -6) . '/' . $store->getName(),
            ];
        }
        return $out;
    }
}