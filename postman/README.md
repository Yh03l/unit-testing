# Colección Postman: Gestión de Catálogo

Esta colección contiene todos los endpoints necesarios para probar la funcionalidad de Gestión de Catálogo del sistema.

## Configuración

1. Importar la colección `Gestion_Catalogo.postman_collection.json` en Postman
2. Crear un ambiente en Postman con la variable:
   - `base_url`: URL base de tu API (por defecto: `http://localhost:8000`)

## Endpoints Disponibles

### Catálogos

1. **Listar Catálogos**
   - Método: `GET`
   - URL: `/api/catalogs`
   - Respuesta: Lista de todos los catálogos

2. **Obtener Catálogo**
   - Método: `GET`
   - URL: `/api/catalogs/:id`
   - Parámetros: ID del catálogo
   - Respuesta: Detalles del catálogo

3. **Crear Catálogo**
   - Método: `POST`
   - URL: `/api/catalogs`
   - Body:
     ```json
     {
         "nombre": "Catálogo de Servicios Nutricionales",
         "estado": "activo"
     }
     ```

4. **Actualizar Catálogo**
   - Método: `PUT`
   - URL: `/api/catalogs/:id`
   - Body:
     ```json
     {
         "nombre": "Catálogo Actualizado",
         "estado": "activo"
     }
     ```

5. **Eliminar Catálogo**
   - Método: `DELETE`
   - URL: `/api/catalogs/:id`

### Servicios

1. **Listar Servicios Activos**
   - Método: `GET`
   - URL: `/api/services`
   - Respuesta: Lista de servicios activos

2. **Obtener Servicio**
   - Método: `GET`
   - URL: `/api/services/:id`
   - Parámetros: ID del servicio
   - Respuesta: Detalles del servicio

3. **Crear Servicio**
   - Método: `POST`
   - URL: `/api/services`
   - Body:
     ```json
     {
         "nombre": "Consulta Nutricional",
         "descripcion": "Consulta nutricional personalizada",
         "monto": 50.00,
         "moneda": "USD",
         "vigencia": "2024-12-31T23:59:59Z",
         "tipo_servicio_id": "1",
         "catalogo_id": "catalog-id-here"
     }
     ```

4. **Actualizar Servicio**
   - Método: `PUT`
   - URL: `/api/services/:id`
   - Body:
     ```json
     {
         "nombre": "Consulta Nutricional Actualizada",
         "descripcion": "Descripción actualizada"
     }
     ```

5. **Actualizar Estado de Servicio**
   - Método: `PUT`
   - URL: `/api/services/:id/status`
   - Body:
     ```json
     {
         "estado": "inactivo"
     }
     ```
   - Estados válidos: `activo`, `inactivo`, `suspendido`

6. **Actualizar Costo de Servicio**
   - Método: `POST`
   - URL: `/api/services/:id/costs`
   - Body:
     ```json
     {
         "monto": 60.00,
         "moneda": "USD",
         "vigencia": "2025-01-01T00:00:00Z"
     }
     ```

7. **Obtener Historial de Costos**
   - Método: `GET`
   - URL: `/api/services/:id/costs/history`
   - Parámetros: ID del servicio
   - Respuesta: Historial de costos del servicio

## Flujo de Prueba Recomendado

1. Crear un catálogo
2. Verificar que el catálogo se haya creado correctamente en el listado de catálogos
3. Crear un servicio en el catálogo
4. Verificar que el servicio se haya creado correctamente en el listado de servicios
5. Actualizar el costo del servicio
6. Verificar que el costo se haya actualizado correctamente
7. Actualizar el estado del servicio
8. Listar servicios activos para verificar el cambio
9. Actualizar información del servicio
8. Obtener el servicio para verificar los cambios
9. Eliminar el catálogo (opcional)

## Notas

- Todos los endpoints retornan respuestas en formato JSON
- Los errores incluyen un mensaje descriptivo
- Las fechas deben enviarse en formato ISO 8601
- Los montos deben ser números decimales positivos
- Las monedas deben ser códigos de 3 letras (ejemplo: USD, EUR, BOB) 