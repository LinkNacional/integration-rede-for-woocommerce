<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;

class LknIntegrationRedeForWoocommerceTransactionException extends Exception
{
    private $additionalData;

    public function __construct($message, $code = 0, $additionalData = [])
    {
        parent::__construct($message, $code);
        $this->additionalData = $additionalData;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
