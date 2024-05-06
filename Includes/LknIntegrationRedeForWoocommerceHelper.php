<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;


abstract class LknIntegrationRedeForWoocommerceHelper {

     /**
     * Makes a .log file for each donation.
     *
     * @since 1.0.0
     * @since 2.0.0 verification if debug is enabled is done inside the function.
     * The log is registered as JSON.
     *
     * @param  string|array $log
     * @param  string $configs
     *
     * @return void
     */
    final public static function reg_log($log, $configs): void { //TODO verificar o porque da função não criar os arquivos logs
        if ($configs['debug']) {            

            $jsonLog = json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            error_log($jsonLog, 3, $configs['base']);
            chmod($configs['base'], 0666);

        }
    }

}
