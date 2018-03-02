<?php

namespace Kawa\B2B\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_B2B_IS_B2B_SHOP = 'b2b/general/is_b2b_shop';

    /**
     * Check if the current shop is business-to-business.
     *
     * @return bool
     */
    public function isB2B()
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_B2B_IS_B2B_SHOP,
            ScopeInterface::SCOPE_STORE
        );
    }
}
