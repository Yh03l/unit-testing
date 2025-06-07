#!/bin/bash

# Directorio de la aplicación
APP_DIR="/var/www/html"

# Función para publicar eventos (se ejecuta cada 30 segundos)
publish_events() {
    while true; do
        echo "[$(date)] Publicando eventos pendientes..."
        cd $APP_DIR
        php artisan commercial:publish-events
        echo "[$(date)] Esperando 30 segundos..."
        sleep 30
    done
}

# Función para consumir eventos de plan alimentario asignado
consume_plan_alimentario_asignado() {
    while true; do
        echo "[$(date)] Iniciando consumidor de eventos de plan alimentario asignado..."
        cd $APP_DIR
        php consume-plan-asignado.php
        
        # Si el comando falla, esperamos 5 segundos antes de reintentar
        echo "[$(date)] El consumidor se detuvo inesperadamente. Reiniciando en 5 segundos..."
        sleep 5
    done
}

# Iniciar el consumidor en segundo plano
consume_plan_alimentario_asignado &

# Iniciar el publicador en el proceso principal
publish_events 