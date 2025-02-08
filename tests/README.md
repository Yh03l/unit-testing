# Estrategia de Testing - Microservicio Comercial

## Estructura de Tests
```
tests/
├── Unit/
│   ├── Domain/
│   │   ├── Aggregates/
│   │   ├── ValueObjects/
│   │   └── Events/
│   └── Application/
│       ├── Commands/
│       └── Queries/
├── Contract/
│   ├── Consumer/
│   │   └── ServiceConsumerTest.php
│   ├── Provider/
│   │   └── ApiProviderTest.php
│   └── pacts/
│       └── serviceconsumer-serviceprovider.json
└── TestCase.php
```

## Convenciones y Buenas Prácticas

### Nomenclatura
- Los archivos de test deben terminar en `Test.php`
- Usar nombres descriptivos que indiquen qué se está probando
- Seguir el patrón: `{ClaseAProbar}Test.php`

### Métodos de Test
- Nombres descriptivos que indiquen:
  1. El escenario que se prueba
  2. El comportamiento esperado
- Formato: `test{Comportamiento}When{Escenario}`
- Ejemplo: `testThrowsExceptionWhenEmailIsInvalid()`

### Estructura de cada Test
```php
public function testXXX(): void
{
    // Arrange (Preparación)
    // - Configurar el escenario
    // - Preparar los datos
    // - Configurar mocks si es necesario

    // Act (Acción)
    // - Ejecutar el método a probar

    // Assert (Verificación)
    // - Verificar el resultado
    // - Verificar efectos secundarios si aplica
}
```

## Lista de Tests Unitarios a Implementar

### Domain
#### ValueObjects
- [ ] `EmailTest`
  - `testCreateValidEmail`
  - `testThrowsExceptionWhenEmailIsInvalid`
  - `testEmailEquality`

#### Aggregates
- [ ] `UserTest`
  - `testCreateUser`
  - `testUpdateUserInformation`
  - `testUpdateEmail`
  - `testUpdateEstado`

- [ ] `AdministratorTest`
  - `testCreateAdministrator`
  - `testUpdateCargo`
  - `testUpdateDepartamento`
  - `testGetTipoUsuario`

### Application
#### Queries
- [ ] `GetUserByEmailHandlerTest`
  - `testHandleReturnsUserWhenExists`
  - `testHandleReturnsNullWhenUserNotFound`
  - `testHandleWithInvalidEmail`

- [ ] `GetServiceDetailsHandlerTest`
  - `testHandleReturnsServiceWhenExists`
  - `testThrowsExceptionWhenServiceNotFound`

- [ ] `GetCatalogHandlerTest`
  - `testHandleReturnsCatalogWhenExists`
  - `testThrowsExceptionWhenCatalogNotFound`

- [ ] `ListContractsByPacienteHandlerTest`
  - `testHandleReturnsContractsWhenExist`
  - `testHandleReturnsEmptyArrayWhenNoContracts`

#### Commands
- [ ] `CreateUserHandlerTest`
  - `testHandleCreatesUserSuccessfully`
  - `testThrowsExceptionWhenEmailAlreadyExists`
  - `testPublishesUserCreatedEvent`

## Ejemplos de Implementación

### Ejemplo de Test de Value Object
```php
class EmailTest extends TestCase
{
    public function testCreateValidEmail(): void
    {
        $emailString = 'test@example.com';
        $email = Email::fromString($emailString);
        
        $this->assertEquals($emailString, $email->getValue());
    }

    public function testThrowsExceptionWhenEmailIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('invalid-email');
    }
}
```

### Ejemplo de Test de Query Handler
```php
class GetUserByEmailHandlerTest extends TestCase
{
    private UserRepository $repository;
    private GetUserByEmailHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->handler = new GetUserByEmailHandler($this->repository);
    }

    public function testHandleReturnsUserWhenExists(): void
    {
        // Arrange
        $email = Email::fromString('test@example.com');
        $expectedUser = User::create('123', 'John', 'Doe', $email, 'ACTIVE');
        
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($expectedUser);

        $query = new GetUserByEmailQuery('test@example.com');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('John', $result->nombre);
        $this->assertEquals('Doe', $result->apellido);
        $this->assertEquals('test@example.com', $result->email);
    }
}
```

## Contract Testing con Pact

### Descripción
Los tests de contrato nos permiten verificar que los consumidores y proveedores de una API mantienen un contrato consistente. Utilizamos Pact como framework para implementar estos tests.

### Estructura de Contract Tests
- `Consumer/`: Tests que definen las expectativas del consumidor
- `Provider/`: Tests que verifican que el proveedor cumple con las expectativas
- `pacts/`: Archivos JSON generados que contienen los contratos

### Implementación de Tests de Contrato

#### 1. Test del Consumidor
```php
class ServiceConsumerTest extends TestCase
{
    public function testGetServices(): void
    {
        $matcher = new Matcher();
        
        // Definir la solicitud esperada
        $request = new ConsumerRequest();
        $request
            ->setMethod('GET')
            ->setPath('/api/v1/services')
            ->addHeader('Accept', 'application/json');

        // Definir la respuesta esperada
        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'data' => $matcher->eachLike([
                    'id' => $matcher->uuid('e842ca01-8e65-493b-a983-59814b298cb5'),
                    'nombre' => $matcher->like('Consulta Nutricional'),
                    // ... otros campos
                ])
            ]);

        // Configurar y verificar la interacción
        $this->builder
            ->uponReceiving('Una solicitud GET a la lista de servicios')
            ->with($request)
            ->willRespondWith($response);
            
        // Realizar la prueba
        $client = new Client(['base_uri' => $this->config->getBaseUri()]);
        $actualResponse = $client->get('/api/v1/services', [
            'headers' => ['Accept' => 'application/json']
        ]);
        
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $this->assertTrue($this->builder->verify());
    }
}
```

#### 2. Test del Proveedor
```php
class ApiProviderTest extends TestCase
{
    public function testPactVerifyProvider()
    {
        $config = new VerifierConfig();
        $config->getProviderInfo()
            ->setName('ServiceProvider')
            ->setHost('localhost')
            ->setPort(8000);

        $verifier = new Verifier($config);
        $verifier->addFile(__DIR__ . '/../pacts/serviceconsumer-serviceprovider.json');
        
        $verifyResult = $verifier->verify();
        $this->assertTrue($verifyResult, 'Pact Verification failed');
    }
}
```

### Ejecución de Tests de Contrato

1. **Generar el Contrato (Consumer)**:
```bash
vendor\bin\phpunit tests\Contract\Consumer\ServiceConsumerTest.php
```

2. **Verificar el Contrato (Provider)**:
```bash
# Asegúrate de que tu API esté corriendo en localhost:8000
php artisan serve

# En otra terminal, ejecuta las pruebas del proveedor
vendor\bin\phpunit tests\Contract\Provider\ApiProviderTest.php
```

### Matchers Disponibles
- `like()`: Verifica el tipo de dato
- `eachLike()`: Array de elementos con estructura similar
- `term()`: Coincidencia por expresión regular
- `integer()`: Números enteros
- `decimal()`: Números decimales
- `uuid()`: Identificadores UUID
- `datetime()`: Fechas y horas

### Buenas Prácticas
1. **Definición de Contratos**:
   - Usar matchers apropiados para cada tipo de dato
   - Incluir todos los campos requeridos
   - Documentar el propósito de cada interacción

2. **Mantenimiento**:
   - Mantener los contratos actualizados
   - Versionar los contratos junto con el código
   - Ejecutar las pruebas de contrato en el CI/CD

3. **Organización**:
   - Un archivo de test por conjunto de endpoints relacionados
   - Nombres descriptivos para las interacciones
   - Documentar los casos de prueba

## Ejecución de Tests
```bash
# Ejecutar todos los tests
./vendor/bin/phpunit

# Ejecutar tests de un directorio específico
./vendor/bin/phpunit tests/Unit/Domain

# Ejecutar un test específico
./vendor/bin/phpunit tests/Unit/Domain/ValueObjects/EmailTest.php

# Ejecutar con cobertura de código
./vendor/bin/phpunit --coverage-html coverage
``` 