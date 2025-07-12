angular.module('ordenesApp').controller('OrdenController', function($scope, OrdenService) { //Definicion del controlador
  $scope.ordenes = [];                //Todas las órdenes
  $scope.ordenesFiltradas = [];       //Todas las órdenes filtradas
  $scope.busqueda = '';               //Texto de búsqueda   
  $scope.formulario = {};             //Datos del formulario para crear/editar órdenes
  $scope.mostrarFormulario = false;   //Controla si se muestra el formulario o no
  $scope.esEditar = false;            //Indica si se está o no editando una orden

  $scope.paginaActual = 1;            //Página inicial de la paginacion
  $scope.registrosPorPagina = 5;      //Registros que se mostraran por pagina

  
  function cargarOrdenes() { // Cargar todas las órdenes desde el OrdenService
    OrdenService.getTodas().then(function(res) {
      $scope.ordenes = res.data.ordenes || [];
      $scope.filtrarOrdenes();
    }, function(err) {
      console.error("Error al cargar órdenes:", err);
    });
  }

  $scope.filtrarOrdenes = function () {
    const texto = $scope.busqueda.toLowerCase().trim();

    // Expresión para extraer filtros tipo "campo:valor, ejemplo: cliente:"Jairo preciado"
    const filtros = {};
    const regex = /(\w+):("[^"]+"|\S+)/g; // permite espacios entre comillas: cliente:"juan pérez"
    let match;
    while ((match = regex.exec(texto)) !== null) {
      const campo = match[1];
      let valor = match[2];
      if (valor.startsWith('"') && valor.endsWith('"')) {
        valor = valor.slice(1, -1); // eliminar comillas
      }
      filtros[campo] = valor;
    }

  // Si no hay filtros explícitos, buscar en todos los campos que hay
  const busquedaLibre = Object.keys(filtros).length === 0 ? texto : null;

  $scope.ordenesFiltradas = $scope.ordenes.filter(orden => {
    // Filtro por campo específico
    for (let campo in filtros) {
      if (!orden.hasOwnProperty(campo)) return false;

      const valorCampo = orden[campo]?.toString().toLowerCase();
      if (!valorCampo.includes(filtros[campo])) return false;
    }

    // Filtro libre (cuando no se usa campo:valor)
    if (busquedaLibre) {
      return Object.values(orden).some(val =>
        val?.toString().toLowerCase().includes(busquedaLibre)
      );
    }

    return true;
  });

  $scope.totalPaginas = Math.ceil($scope.ordenesFiltradas.length / $scope.registrosPorPagina);
  $scope.paginaActual = 1;
};

  // Avanzar en la paginación
  $scope.siguientePagina = function () {
    if ($scope.paginaActual < $scope.totalPaginas) {
      $scope.paginaActual++;
    }
  };
  
  // Retroceder en la paginación
  $scope.paginaAnterior = function () {
    if ($scope.paginaActual > 1) {
      $scope.paginaActual--;
    }
  };

  // Escuchar cambios en el input de búsqueda
  $scope.$watch('busqueda', function() {
    $scope.filtrarOrdenes();
  });

  
  // Mostrar formulario para nueva orden
  $scope.nuevaOrden = function() {
    $scope.formulario = {};
    $scope.mostrarFormulario = true;
    $scope.esEditar = false;
  };

  // Editar una orden existente
  $scope.editarOrden = function(orden) {
    $scope.formulario = angular.copy(orden);
    $scope.mostrarFormulario = true;
    $scope.esEditar = true;
  };

  // Cancelar formulario
  $scope.cancelar = function() {
    $scope.formulario = {};
    $scope.mostrarFormulario = false;
    $scope.esEditar = false;
  };

  // Guardar nueva orden o actualizar
  $scope.guardarOrden = function() {
  if ($scope.formulario.fecha_estimada instanceof Date) {
    $scope.formulario.fecha_estimada = $scope.formulario.fecha_estimada.toISOString().substring(0, 10);
  }
    
    if ($scope.esEditar) {
      OrdenService.actualizar($scope.formulario.id, $scope.formulario).then(function(res) {
        cargarOrdenes();
        $scope.cancelar();
      }, function(err) {
        alert("Error al actualizar: " + (err.data?.error || "desconocido"));
      });
    } else {
      OrdenService.crear($scope.formulario).then(function(res) {
        cargarOrdenes();
        $scope.cancelar();
      }, function(err) {
        alert("Error al crear: " + (err.data?.error || "desconocido"));
      });
    }
  };

  // Eliminar una orden
  $scope.eliminarOrden = function(id) {
    if (confirm("¿Estás seguro de eliminar esta orden?")) {
      OrdenService.eliminar(id).then(function(res) {
        cargarOrdenes();
      }, function(err) {
        alert("Error al eliminar: " + (err.data?.error || "desconocido"));
      });
    }
  };

   cargarOrdenes();
});
