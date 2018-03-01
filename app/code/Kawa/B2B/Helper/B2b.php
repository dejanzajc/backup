<?php

namespace Kawa\B2B\Helper;

class B2b extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_B2B_IS_B2B_SHOP = 'B2B/general/is_b2b_shop';

    public function isB2bShop()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_B2B_IS_B2B_SHOP);
    }
}