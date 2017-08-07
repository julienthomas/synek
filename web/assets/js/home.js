var map = null;
var geolocationZoom = 11;
var geolocMakerInit = false;

$(function(){
    map = new google.maps.Map(document.getElementById("home-map"), {
        center: {lat: 46.81676808590648, lng: 2.4233221091003587},
        zoom:   6
    });

    var mapLocate = $("[data-id='map-locate']");
    var geolocationMarker = new GeolocationMarker(map, null, {visible: false});
    geolocationMarker.setCircleOptions({});
    geolocationMarker.addListener('geolocation_error', function(){
        mapLocate.attr('disabled', true);
    });

    geolocationMarker.addListener('position_changed', function(){
        if (geolocMakerInit === false) {
            map.setCenter(geolocationMarker.getPosition());
            map.setZoom(geolocationZoom);
            mapLocate.click(function(){
                map.setCenter(geolocationMarker.getPosition());
                map.setZoom(geolocationZoom);
            });
            geolocMakerInit = true;
        }
    });

    $("[data-id='place-info-close']").click(function(){
        $("[data-id='place-info']").removeClass('show');
    });

    updateMarkers();
});

/**
 * Get place list and update map markers
 */
function updateMarkers()
{
    var beer = $("#beer-filter").val();
    if (beer.length === 0) {
        beer = null;
    }

    $.ajax({
        type: 'GET',
        url: $("#home-map").data('url'),
        dataType: 'json',
        data: {beer: beer},
        success: function(data){
            $.each(data, function(index, place){
                var marker = new google.maps.Marker({
                    icon:     place.marker,
                    position: {lat: parseFloat(place.latitude), lng: parseFloat(place.longitude)},
                    title:    place.name,
                    map:      map
                });
                marker.addListener('click', function(){
                    displayPlaceInfo(place);
                });
            });
        }
    });
}

/**
 * @param placeData
 */
function displayPlaceInfo(placeData)
{
    var placeInfo          = $("[data-id='place-info']");
    var placeName          = $("[data-id='place-name']", placeInfo);
    var placeAddress       = $("[data-id='place-address']", placeInfo);
    var placePhone         = $("[data-id='place-phone']", placeInfo);
    var placePhoneNumber   = $("[data-id='place-phone-number']", placeInfo);
    var placeEmail         = $("[data-id='place-email']", placeInfo);
    var placeEmailAddress  = $("[data-id='place-email-address']", placeInfo);
    var availableBeers     = $("[data-id='place-available-beers']", placeInfo);
    var availableBeersList = $("[data-id='place-available-beers-list']", placeInfo);
    var address            = placeData['address'];

    if (placeData.addressComplement && placeData.addressComplement.length > 0) {
        address += ' ' + placeData.addressComplement;
    }
    address += ', ' + placeData.zipCode + ' ' + placeData.city;

    placeName.text(placeData.name);
    placeAddress.text(address);

    if (placeData.phone && placeData.phone.length > 0) {
        placePhoneNumber.text(placeData['phone']);
        placePhone.show();
    } else {
        placePhone.hide();
        placePhoneNumber.text(null);
    }

    if (placeData.email && placeData.email.length > 0) {
        placeEmailAddress.text(placeData.email);
        placeEmail.show();
    } else {
        placeEmail.hide();
        placeEmailAddress.text(null);
    }

    availableBeersList.empty();
    if (placeData.beers && Object.keys(placeData.beers).length > 0) {
        $.each(placeData.beers, function(brewery, beers){
            var breweryDiv = $("<div class='place-available-brewery'>");
            breweryDiv.append("<h5 class='page-header'>" + brewery + "</h5>");
            $.each(beers, function(index, beerName){
                breweryDiv.append("<span class='label label-success beer-label'>" + beerName + "</span>");
            });
            availableBeersList.append(breweryDiv);
        });
        availableBeers.show();
    } else {
        availableBeers.hide();
    }

    placeInfo.addClass('show');
}