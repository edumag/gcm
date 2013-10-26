/**
 * @file mapas.js
 * @brief Presentación de mapas
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

   map = new google.maps.Map(document.getElementById(container), {
      center: new google.maps.LatLng(38, 15),
       zoom: 2,
       mapTypeId: 'terrain'
   });

   var marcadores = [];
   var ventanas   = [];

   if (markers) {
      for (var level in markers) {
         for (var i = 0; i < markers[level].length; i++) {
            var details = markers[level][i];
            var id = details.name;
            contenidos[id] = '<div id="content">'+
               '<div id="siteNotice">'+
               '</div>'+
               '<h1 id="firstHeading" class="firstHeading">'+details.name+'</h1>'+
               '<div id="bodyContent">'+
               '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>'+
               '</div>'+
               '</div>';
            // ventanas[i] = new google.maps.InfoWindow({
            //    content: contentString
            // });
            marcadores[i] = new google.maps.Marker({
               map: map,
               title: details.name,
               position: new google.maps.LatLng(
                  details.location[0], details.location[1]),
               clickable: true,
               draggable: false,
               visible:   true,
               flat: true
            });
            console.log(level+' '+i);
            console.log(details);
            console.log(marcadores[i]);
            console.log(ventanas[i]);
            google.maps.event.addListener(marcadores[i], 'click', function(e) {
               // ventanas[i].open(map,marcadores[i]);
               // var infobox = new SmartInfoWindow({position: marcadores[i].getPosition(), map: map, content: contenidos[i]});
               console.log(this);
               var id = this.title;
               console.log('id: '+id);
               presenta_info_mapa(container,id);
            });
         }
      }
      console.log(ventanas);
      console.log(marcadores);
   }
}

function presenta_info_mapa(container,id) {
   console.log(contenidos);
   var caja = document.getElementById(container+'_info');
   caja.innerHTML = contenidos[id];
   }
