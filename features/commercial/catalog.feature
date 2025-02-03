# language: es
Característica: Gestión del Catálogo Comercial
  Como usuario del sistema
  Quiero poder consultar el catálogo de servicios
  Para ver la oferta comercial disponible

  Escenario: Obtener lista de catálogo
    Cuando hago una petición GET a "/api/v1/catalogs"
    Entonces debo recibir una respuesta con código 200
    Y la respuesta debe contener una lista de elementos del catálogo 