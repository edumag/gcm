/**
 * @file mapas.js
 * @brief Presentación de mapas
 * @ingroup modulo_mapas
 */

contenidos = [];

/**
 * Inicializamos mapa
 *
 * @param container Identificador donde mostrar el mapa
 * @param mapa      Array con las opciones para el mapa
 *    latitud, longitud, zoom, tipo ,etc...
 * @param markers   Array con los marcadores
 */

function inicia_mapa(container,mapa,markers) {

   console.log(mapa);

   map = new google.maps.Map(document.getElementById(container), {
      center: new google.maps.LatLng(mapa['latitud'], mapa['longitud']),
      zoom: mapa['zoom'],
      //mapTypeId: google.maps.MapTypeId.ROADMAP
   });

   var marcadores = [];
   var infowindow = [];
   var salida = '' ; // Salida para caja_iconos

   console.log(markers);

   if (markers) {
      for (var level in markers) {
         for (var i = 0; i < markers[level].length; i++) {
            var details = markers[level][i];
            console.log('details');
            console.log(details);
            // var id = details.name.replace(' ','_');
            var id = details.name.replace(/ /g,'_');
            var id = id.replace(/\'/g,'');
            var id = id.replace(/"/g,'');
            contenidos[id] = details.contenido;
            marcadores[i] = new google.maps.Marker({
               map: map
               ,title: details.name
               ,position: new google.maps.LatLng(details.location[0], details.location[1])
               ,clickable: true
               ,draggable: false
               ,visible:   true
               ,flat: true
               ,icon: details.icon
            });
            infowindow[id] = new google.maps.InfoWindow({
                content: details.contenido
               ,maxWidth: 590
               });
            google.maps.event.addListener(marcadores[i], 'click', function(e) {
               var id = this.title.replace(/ /g,'_');
               var id = id.replace(/\'/g,'');
               var id = id.replace(/"/g,'');
               // infowindow[id].open(map,marcadores[i]); // Cancelamos infowindows
               presenta_info_mapa(container,id);
            });

            var caja_iconos = document.getElementById('iconos_mapa');
            if ( caja_iconos ) {
              salida += '<a onmouseover="presenta_info_mapa(\''+container+'\',\''+id+'\')">';
              salida += '<img src="'+marcadores[i]['icon']+'"/>';
              salida += '</a>';

            }
         }
      }

      if ( salida ) caja_iconos.innerHTML = salida;
      console.log(contenidos);
   }
}

/**
 * Presentar información del marcador
 *
 * Por defecto se muestra caja modal con la información. Pero podemos definir
 * un div con id=NOMBRE_MAPA_info para que se presente en él.
 * 
 * @param container Identificador de la caja donde se presenta el contenido 
 * @param id Identificador del marcador que contiene el contenido
 */

function presenta_info_mapa(container,id) {
   var caja = document.getElementById(container+'_info');
   if ( caja ) {

      caja.innerHTML = contenidos[id];

   } else {

      var caja = document.getElementById(container+'_info_default');

      var contenido = '<section class="semantic-content" id="modal-text" ';
      contenido += ' tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true">';
      contenido += '<div class="modal-inner">';
      contenido += '<div class="modal-content">';
      contenido += '<p>'+contenidos[id]+'</p>';
      contenido += '</div>';
      contenido += '</div>';
      contenido += '</section>';

      caja.innerHTML = contenido;
      $("#"+container+"_info_default").modal({
         escapeClose: true
         ,clickClose: true
         ,showClose: true
         });

      }

   }
