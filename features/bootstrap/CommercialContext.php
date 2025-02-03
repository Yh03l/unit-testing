<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use GuzzleHttp\Client;

class CommercialContext implements Context
{
    private $response;
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://127.0.0.1:8000',
            'http_errors' => false
        ]);
    }

    /**
     * @When hago una petición GET a :path
     */
    public function hagoUnaPeticionGetA($path)
    {
        $this->response = $this->client->get($path);
    }

    /**
     * @Then debo recibir una respuesta con código :code
     */
    public function deboRecibirUnaRespuestaConCodigo($code)
    {
        Assert::assertEquals(
            (int) $code,
            $this->response->getStatusCode(),
            sprintf(
                'Se esperaba el código de estado %d pero se recibió %d',
                $code,
                $this->response->getStatusCode()
            )
        );
    }

    /**
     * @Then la respuesta debe contener una lista de elementos del catálogo
     */
    public function laRespuestaDebeContenerUnaListaDeElementosDelCatalogo()
    {
        $content = $this->response->getBody()->getContents();
        $data = json_decode($content, true);
        
        Assert::assertIsArray(
            $data,
            sprintf('Se esperaba un array pero se recibió: %s', $content)
        );
        Assert::assertNotEmpty($data, 'La lista de elementos del catálogo está vacía');
    }
} 