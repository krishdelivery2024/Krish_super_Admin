/**
 * @license
 * Copyright 2019 Google LLC. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */
// [START maps_event_click_latlng]
function initMap() {
var input = document.getElementById('store_address');
var autocomplete = new google.maps.places.Autocomplete(input);
var marker = new google.maps.Marker();
  const myLatlng = { lat: 20.5937, lng: 78.9629 };
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 4,
    center: myLatlng,
  });
  // Create the initial InfoWindow.
  let infoWindow = new google.maps.InfoWindow();

  infoWindow.open(map);
  
  
  // [START maps_event_click_latlng_listener]
  // Configure the click listener.
  map.addListener("click", (mapsMouseEvent) => {
    // Close the current InfoWindow.
    infoWindow.close();
    // Create a new InfoWindow.
    infoWindow = new google.maps.InfoWindow({
      position: mapsMouseEvent.latLng,
    });
    infoWindow.setContent(
      JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2)
    );
    var latlans=mapsMouseEvent.latLng.toJSON();
    //console.log(latlans);
    document.getElementById("store_lattitude").value = latlans.lat;
    document.getElementById("store_longitude").value = latlans.lng;
    infoWindow.open(map);
  });
  // [END maps_event_click_latlng_listener]
  
  
    
    //var infowindow = new google.maps.InfoWindow();
    autocomplete.addListener("place_changed", () => { 
      
    const place = autocomplete.getPlace();
    document.getElementById("map").style.display = 'block';

    if (!place.geometry || !place.geometry.location) {
      // User entered the name of a Place that was not suggested and
      // pressed the Enter key, or the Place Details request failed.
      window.alert("No details available for input: '" + place.name + "'");
      return;
    }

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(17);
    }

    marker.setPosition(place.geometry.location);
    marker.setVisible(true);
    marker.setMap(map);
    document.getElementById("store_lattitude").value = place.geometry.location.lat();
    document.getElementById("store_longitude").value = place.geometry.location.lng();
            
  });
}

window.initMap = initMap;
// [END maps_event_click_latlng]
