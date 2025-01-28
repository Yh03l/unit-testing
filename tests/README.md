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