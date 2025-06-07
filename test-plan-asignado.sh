#!/bin/bash

# Crear el JSON de prueba
JSON='{"idContrato":"9a7b05c3-b070-4ab2-82cb-b8f7e272ed94","idPlanAlimentario":"9a7b05c3-b070-4ab2-82cb-b8f7e272ed94"}'

# Publicar mensaje de prueba
docker exec commercial-message-broker rabbitmqadmin \
  --username=commercial \
  --password=commercial123 \
  publish \
  exchange=plan-alimentario-asignado \
  routing_key="" \
  payload="$JSON"

echo "Mensaje enviado: $JSON"
echo "Verifica la cola 'comercial.plan-alimentario-asignado' en la interfaz de RabbitMQ" 