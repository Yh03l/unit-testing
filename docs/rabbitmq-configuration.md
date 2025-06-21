# Configuración de RabbitMQ

Este documento describe cómo configurar RabbitMQ para el sistema comercial y qué hacer cuando no está disponible.

## Configuración Básica

### Variables de Entorno

Agrega las siguientes variables a tu archivo `.env`:

```env
# RabbitMQ Configuration
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

# Timeouts (opcional)
RABBITMQ_CONNECTION_TIMEOUT=3.0
RABBITMQ_READ_TIMEOUT=3.0
RABBITMQ_WRITE_TIMEOUT=3.0

# Keepalive y Heartbeat (opcional)
RABBITMQ_KEEPALIVE=false
RABBITMQ_HEARTBEAT=0

# Fallback Configuration
RABBITMQ_USE_FALLBACK=true
RABBITMQ_FALLBACK_TO_MEMORY=true
```

### Instalación de RabbitMQ

#### En Ubuntu/Debian:
```bash
# Instalar RabbitMQ
sudo apt update
sudo apt install rabbitmq-server

# Habilitar y iniciar el servicio
sudo systemctl enable rabbitmq-server
sudo systemctl start rabbitmq-server

# Verificar el estado
sudo systemctl status rabbitmq-server
```

#### En macOS con Homebrew:
```bash
# Instalar RabbitMQ
brew install rabbitmq

# Iniciar el servicio
brew services start rabbitmq
```

#### En Windows:
1. Descargar RabbitMQ desde: https://www.rabbitmq.com/download.html
2. Instalar siguiendo las instrucciones del instalador
3. El servicio se iniciará automáticamente

### Verificar la Conexión

Usa el comando de artisan para verificar si RabbitMQ está funcionando:

```bash
php artisan commercial:check-rabbitmq
```

Si la conexión es exitosa, verás:
```
✅ Conexión RabbitMQ establecida correctamente
```

Si hay problemas, verás:
```
❌ No se pudo establecer conexión con RabbitMQ
```

## Fallback Automático

El sistema está diseñado para funcionar incluso cuando RabbitMQ no está disponible:

### Comportamiento del Fallback

1. **En entorno de testing**: Siempre usa `InMemoryEventBus`
2. **En producción/desarrollo**: 
   - Intenta conectar a RabbitMQ
   - Si falla, automáticamente usa `InMemoryEventBus`
   - Los eventos se almacenan en memoria

### Ventajas del Fallback

- ✅ El sistema sigue funcionando sin RabbitMQ
- ✅ No hay errores de conexión
- ✅ Los eventos se procesan localmente
- ✅ Fácil desarrollo sin dependencias externas

### Limitaciones del Fallback

- ❌ Los eventos no se comparten entre instancias
- ❌ No hay persistencia de eventos
- ❌ No hay distribución de eventos

## Solución de Problemas

### Error: "Connection refused"

**Causa**: RabbitMQ no está ejecutándose o no es accesible.

**Solución**:
```bash
# Verificar si RabbitMQ está ejecutándose
sudo systemctl status rabbitmq-server

# Si no está ejecutándose, iniciarlo
sudo systemctl start rabbitmq-server

# Verificar que el puerto esté abierto
netstat -tlnp | grep 5672
```

### Error: "Access refused"

**Causa**: Credenciales incorrectas o usuario no tiene permisos.

**Solución**:
```bash
# Crear un usuario con permisos
sudo rabbitmqctl add_user mi_usuario mi_password
sudo rabbitmqctl set_user_tags mi_usuario administrator
sudo rabbitmqctl set_permissions -p / mi_usuario ".*" ".*" ".*"

# Actualizar .env con las nuevas credenciales
RABBITMQ_USER=mi_usuario
RABBITMQ_PASSWORD=mi_password
```

### Error: "Channel closed"

**Causa**: Problemas de configuración del canal o exchange.

**Solución**:
- Verificar que el exchange esté declarado
- Revisar los permisos del usuario
- Verificar la configuración del vhost

## Monitoreo

### Logs de Laravel

Los logs de Laravel mostrarán información sobre la conexión:

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep -i rabbitmq
```

### Comandos Útiles

```bash
# Verificar conexión
php artisan commercial:check-rabbitmq

# Publicar eventos pendientes
php artisan commercial:publish-events

# Ver estado de RabbitMQ (si está instalado)
sudo rabbitmqctl status
```

## Configuración Avanzada

### Clúster RabbitMQ

Para configurar un clúster RabbitMQ:

1. Configurar múltiples nodos
2. Usar un load balancer
3. Configurar replicación de exchanges

### SSL/TLS

Para conexiones seguras:

```env
RABBITMQ_SSL=true
RABBITMQ_SSL_VERIFY=false
RABBITMQ_SSL_CERT=
RABBITMQ_SSL_KEY=
```

## Desarrollo Local

Para desarrollo local, puedes:

1. **Usar Docker**:
```bash
docker run -d --name rabbitmq -p 5672:5672 -p 15672:15672 rabbitmq:management
```

2. **Usar solo InMemoryEventBus**:
```env
RABBITMQ_USE_FALLBACK=true
RABBITMQ_FALLBACK_TO_MEMORY=true
```

3. **Deshabilitar RabbitMQ completamente**:
```env
RABBITMQ_HOST=invalid-host
```

El sistema seguirá funcionando normalmente con cualquiera de estas opciones. 