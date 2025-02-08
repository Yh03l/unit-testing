<?php

namespace Tests\Contract\Provider;

use GuzzleHttp\Psr7\Uri;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfig;
use PhpPact\Standalone\ProviderVerifier\Verifier;
use PHPUnit\Framework\TestCase;

class ApiProviderTest extends TestCase
{
    public function testPactVerifyProvider()
    {
        $config = new VerifierConfig();
        $config->getProviderInfo()
            ->setName('ServiceProvider')
            ->setHost('localhost')
            ->setPort(8000);

        if ($level = \getenv('PACT_LOGLEVEL')) {
            $config->setLogLevel($level);
        }

        $verifier = new Verifier($config);

        // Verificar el contrato de servicios
        $verifier
            ->addFile(__DIR__ . '/../../../tests/Contract/pacts/serviceconsumer-serviceprovider.json');

        $verifyResult = $verifier->verify();

        $this->assertTrue($verifyResult, 'Pact Verification failed');
    }
} 