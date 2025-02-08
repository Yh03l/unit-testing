<?php

namespace Tests\Contract\Consumer;

use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerConfig;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ServiceConsumerTest extends TestCase
{
    private $builder;
    private $config;

    protected function setUp(): void
    {
        $this->config = new MockServerConfig();
        $this->config
            ->setConsumer('ServiceConsumer')
            ->setProvider('ServiceProvider')
            ->setPactDir(__DIR__.'/../../../tests/Contract/pacts');

        if ($logLevel = \getenv('PACT_LOGLEVEL')) {
            $this->config->setLogLevel($logLevel);
        }

        $this->builder = new InteractionBuilder($this->config);
    }

    public function testGetServices(): void
    {
        $matcher = new Matcher();

        // Crear la solicitud esperada del consumidor
        $request = new ConsumerRequest();
        $request
            ->setMethod('GET')
            ->setPath('/api/v1/services')
            ->addHeader('Accept', 'application/json');

        // Crear la respuesta esperada del proveedor
        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'data' => $matcher->eachLike([
                    'id' => $matcher->uuid('e842ca01-8e65-493b-a983-59814b298cb5'),
                    'nombre' => $matcher->like('Consulta Nutricional'),
                    'descripcion' => $matcher->like('Consulta nutricional personalizada'),
                    'monto' => $matcher->integer(60),
                    'moneda' => $matcher->like('USD'),
                    'tipo_servicio_id' => $matcher->like('asesoramiento'),
                    'estado' => $matcher->like('activo'),
                    'catalogo_id' => $matcher->uuid('7af9380c-d420-4b07-852c-109ec67163be'),
                    'vigencia' => [
                        'date' => $matcher->like('2025-01-01 00:00:00.000000'),
                        'timezone_type' => $matcher->integer(3),
                        'timezone' => $matcher->like('UTC')
                    ]
                ])
            ]);

        // Configurar la interacción en el mock server
        $this->builder
            ->uponReceiving('Una solicitud GET a la lista de servicios')
            ->with($request)
            ->willRespondWith($response);

        // Realizar la solicitud real
        $client = new Client(['base_uri' => $this->config->getBaseUri()]);
        $actualResponse = $client->get('/api/v1/services', [
            'headers' => ['Accept' => 'application/json']
        ]);

        // Verificar la respuesta
        $this->assertEquals(200, $actualResponse->getStatusCode());
        
        // Verificar que todas las interacciones ocurrieron como se esperaba
        $this->assertTrue($this->builder->verify());
    }

    public function testCreateService(): void
    {
        $matcher = new Matcher();

        // Crear la solicitud esperada del consumidor
        $request = new ConsumerRequest();
        $request
            ->setMethod('POST')
            ->setPath('/api/v1/services')
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'nombre' => 'Consulta Nutricional Testing',
                'descripcion' => 'Consulta nutricional personalizada Testing',
                'monto' => 250.00,
                'moneda' => 'USD',
                'vigencia' => '2025-03-31T23:59:59Z',
                'tipo_servicio_id' => 'asesoramiento',
                'catalogo_id' => '7af9380c-d420-4b07-852c-109ec67163be'
            ]);

        // Crear la respuesta esperada del proveedor
        $response = new ProviderResponse();
        $response
            ->setStatus(201)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'message' => $matcher->like('Servicio creado exitosamente')
            ]);

        // Configurar la interacción en el mock server
        $this->builder
            ->uponReceiving('Una solicitud POST para crear un nuevo servicio')
            ->with($request)
            ->willRespondWith($response);

        // Realizar la solicitud real
        $client = new Client(['base_uri' => $this->config->getBaseUri()]);
        $actualResponse = $client->post('/api/v1/services', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'nombre' => 'Consulta Nutricional Testing',
                'descripcion' => 'Consulta nutricional personalizada Testing',
                'monto' => 250.00,
                'moneda' => 'USD',
                'vigencia' => '2025-03-31T23:59:59Z',
                'tipo_servicio_id' => 'asesoramiento',
                'catalogo_id' => '7af9380c-d420-4b07-852c-109ec67163be'
            ]
        ]);

        // Verificar la respuesta
        $this->assertEquals(201, $actualResponse->getStatusCode());
        
        // Verificar que todas las interacciones ocurrieron como se esperaba
        $this->assertTrue($this->builder->verify());
    }
} 