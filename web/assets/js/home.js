var map = null;
var geolocationZoom = 11;
var geolocMakerInit = false;

/**
 * @param places
 */
function initMap(places)
{
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

    $.each(places, function(index, value){
        var marker = new google.maps.Marker({
            icon:     value['marker'],
            position: {lat: parseFloat(value['latitude']), lng: parseFloat(value['longitude'])},
            title:    value['name'],
            map:      map
        });
        marker.addListener('click', function(){
            displayPlaceInfo(value);
        });
    });

    $("[data-id='place-info-close']").click(function(){
        $("[data-id='place-info']").removeClass('show');
    });
}

function updateMarkers()
{
    var beer =
}

/**
 * @param placeData
 */
function displayPlaceInfo(placeData)
{
    var placeInfo = $("[data-id='place-info']");
    var address   = placeData['address'];
    if (placeData['addressComplement'] && placeData['addressComplement'].length > 0) {
        address += ' ' + placeData['addressComplement'];
    }
    address += ', ' + placeData['zipCode'] + ' ' + placeData['city'];

    $("[data-id='place-name']", placeInfo).text(placeData['name']);
    $("[data-id='place-address']", placeInfo).text(address);

    if (placeData['phone'] && placeData['phone'].length > 0) {
        $("[data-id='place-phone-number']", placeInfo).text(placeData['phone']);
        $("[data-id='place-phone']", placeInfo).show();
    } else {
        $("[data-id='place-phone-number']", placeInfo).text(null);
        $("[data-id='place-phone']", placeInfo).hide();
    }
    placeInfo.addClass('show');
}