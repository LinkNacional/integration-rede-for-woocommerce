<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;

class LknIntegrationRedeForWoocommerceTransactionException extends Exception
{
    private $additionalData;

    public function __construct($message, $code = 0, $additionalData = [], Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->additionalData = $additionalData;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
