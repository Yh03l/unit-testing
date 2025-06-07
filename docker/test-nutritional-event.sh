#!/bin/bash

# Publicar mensaje de prueba
docker exec commercial-message-broker rabbitmqadmin \
  --username=commercial \
  --password=commercial123 \
  publish \
  exchange=nutritional.events \
  routing_key="nutritional.domain.events.plan.created" \
  payload='{
    "IdPlanAlimentario": "123e4567-e89b-12d3-a456-426614174000",
    "Nombre": "Plan de pérdida de peso",
    "Tipo": "weight-loss",
    "CantidadDias": 90
  }'

echo "Mensaje enviado. Verifica la cola 'commercial.listen.nutritional.plan.created' en la interfaz de RabbitMQ" 