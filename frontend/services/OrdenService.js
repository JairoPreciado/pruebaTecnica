angular.module('ordenesApp').service('OrdenService', function($http) { // Servicio para manejar las órdenes 
  const baseUrl = 'http://localhost:8000/ordenes'; // Url del backend

  this.getTodas = function() { // Obtener todas las órdenes del backend
    return $http.get(baseUrl);
  };

  this.getPaginadas = function(limit, offset) { // Obtener órdenes paginadas
    return $http.get(baseUrl + '?limit=' + limit + '&offset=' + offset);
  };

  this.getUna = function(id) { // Obtener una orden específica por ID
    return $http.get(baseUrl + '/' + id);
  };

  this.crear = function(orden) { // Crear una nueva orden
    return $http.post(baseUrl, orden);
  };

  this.actualizar = function(id, orden) { // Actualizar una orden existente
    return $http.put(baseUrl + '/' + id, orden);
  };

  this.eliminar = function(id) { // Eliminar una orden por ID
    return $http.delete(baseUrl + '/' + id);
  };
});
