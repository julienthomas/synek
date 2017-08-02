var map              = null;
var mapDefaultZoom   = 6;
var mapZoom          = 15;
var mapDefaultCenter = {lat: 46.81676808590648, lng: 2.4233221091003587};
var marker           = null;
var isMapInit        = false;
var placeResults     = null;
var geocoder         = null;
var pictureIndex     = 0;

$(function() {

    $("[data-id='place-submit']").click(function(event, submit){
        focusFormError(event, submit);
    });

    if ($("#place-address").is(':visible')) {
        initMap();
    }

    $("[data-id='place-address-link']").click(function(){
        if (isMapInit === false) {
            setTimeout(function(){
                initMap();
            }, 300);
        }
    });

    $("[data-id='geocode']").click(function(){
        geocodeAddress();
    });

    $("[data-id='place-locations-list']").on('change', "[data-role='place-location-input']", function() {
        setMarkerPosition(placeResults[$(this).val()]);
    });

    var pictures  = $("[data-id='pictures']");
    $("[data-id='picture-add']").click(function(){
        addPicture();
    });

    pictures.on('click', "[data-role='picture-edit']", function(){
        $("[data-role='picture-input']", $(this).parents("[data-role='picture']")).first().click();
    }).on('change', "[data-role='picture-input']", function(){
        setPicturePreview($(this));
    }).on('click', "[data-role='picture-delete']", function(){
        removePicture($(this));
    }).on('click', "[data-role='picture-error-close']", function(){
        $(this).parents("[data-role='picture-error']").first().hide();
    });
    pictureIndex = $("[data-role='picture']", pictures).length;

    $("[data-id='place-form']").submit(function(event){
        uploadPictures(event);
    });

    $("[name^='beer[']").attr('required', false);

    $("[data-id='beer-choice']").change(function(){
        updateSelectedBeers();
    });
    updateSelectedBeers();

    $("[data-id='selected-beers']").on('click', "[data-role='beer-delete']", function(){
        removeSelectedBeer($(this).parent("[data-role='beer-label']"));
    });

    $("[data-id='beer-new-show']").click(function(){
        $("#beer-new").slideDown();
    });

    $("[data-id='beer-cancel']").click(function(){
        $("#beer-new").slideUp();
    });

    $("#brewery-filter").change(function(){
        filterBeers($(this).val());
    });

    $("[data-id='beer-submit']").click(function(){
        addBeer();
    });
});

/**
 * Focus hidden field with error
 * @param event
 * @param submit
 */
function focusFormError(event, submit)
{
    if (submit) {
        return;
    }
    var invalids     = $(':invalid', "[data-id='place-form']");
    var firstInvalid = invalids.first();

    if (invalids.length === 0) {
        return;
    }

    event.preventDefault();

    invalids.not(firstInvalid).prop('disabled', true);
    invalids.not(firstInvalid).addClass('validate-disabled');

    var panel       = firstInvalid.parents("[data-role='place-panel']");
    var showTimeout = 0;
    if (panel.is(':hidden')) {
        var link = $("a[href='#" + panel.attr('id') + "']", "[data-id='place-panels-links']");
        link.click();
        showTimeout = 150;
    }

    setTimeout(function(){
        $("[data-id='place-submit']").trigger('click', true);
        setTimeout(function(){
            invalids.not(firstInvalid).prop('disabled', false);
            invalids.not(firstInvalid).removeClass('validate-disabled');
        }, 1);
    }, showTimeout);
}

/**
 * Init the Google Map and add a marker if the place information is filled
 */
function initMap()
{
    var markerSrc = $("#place-map").data('marker-src');
    var latitude  = $("[data-id='place-latitude']").val();
    var longitude = $("[data-id='place-longitude']").val();
    var zoom      = mapDefaultZoom;
    var center    = mapDefaultCenter;
    var addMarker = false;
    if (latitude.length > 0 && longitude.length > 0) {
        center.lat = parseFloat(latitude);
        center.lng = parseFloat(longitude);
        zoom = mapZoom;
        addMarker = true;
    }

    console.log(center);

    map = new google.maps.Map(document.getElementById("place-map"), {
        center: center,
        zoom:   zoom
    });

    marker = new google.maps.Marker({
        icon: markerSrc,
        position: center,
        visible: addMarker,
        map: map
    });

    geocoder = new google.maps.Geocoder();

    isMapInit = true;
}

/**
 * Get the geolocation for filled address
 */
function geocodeAddress()
{
    var address             = $("[data-id='address']").val();
    var addressComplement   = $("[data-id='address-complement']").val();
    var zipCode             = $("[data-id='zip-code']").val();
    var city                = $("[data-id='city']").val();
    var countrySelect       = $("[data-id='country']");
    var placeLocationsNone  = $("[data-id='place-locations-none']");
    var placeLocationError  = $("[data-id='place-locations-error']");
    var country             = countrySelect.val() ? $('option:selected', countrySelect).text() : null;
    var parts               = [address, addressComplement, zipCode, city, country];
    var fullAddress         = '';

    jQuery.each(parts, function(index, value){
        if (value && value.length > 0) {
            if (fullAddress !== '') {
                fullAddress += ', ';
            }
            fullAddress += value;
        }
    });

    // Call the geocode service
    if (fullAddress.length > 0) {
        geocoder.geocode(
            {address: fullAddress},
            function (results, status) {
                if (status === 'OK') {
                    placeResults = results;
                    // Directly set the marker
                    if (placeResults.length == 1) {
                        showPlaceLocationsPanel(null);
                        setMarkerPosition(placeResults[0]);
                    } else {
                        // Display the addresses choices
                        setLocationInputsValues(null, null);
                        marker.setVisible(false);
                        setPlacesChoice();
                    }
                } else if (status === 'ZERO_RESULTS') {
                    setLocationInputsValues(null, null);
                    marker.setVisible(false);
                    showPlaceLocationsPanel(placeLocationsNone);
                } else {
                    setLocationInputsValues(null, null);
                    marker.setVisible(false);
                    showPlaceLocationsPanel(placeLocationError);
                }
            }
        );
    }
}

/**
 * Hide and display information panels
 *
 * @param panel
 */
function showPlaceLocationsPanel(panel)
{
    var panels = $("[data-id='place-locations'], [data-id='place-locations-none'], [data-id='place-locations-error']");

    panels.not(panel).hide();
    if (panel) {
        panel.show();
    }
}

/**
 * Set marker position
 *
 * @param result
 */
function setMarkerPosition(result)
{
    if (!result || !result.geometry.location) {
        placeLat.val(null);
        placeLng.val(null);
        marker.setVisible(false);
        return;
    }
    var location = result.geometry.location;
    marker.setPosition(location);
    marker.setVisible(true);
    map.setCenter(location);
    map.setZoom(mapZoom);
    setLocationInputsValues(location.lat().toFixed(7), location.lng().toFixed(7));
}

/**
 * Set the location input values
 *
 * @param lat
 * @param lng
 */
function setLocationInputsValues(lat, lng)
{
    $("[data-id='place-latitude']").val(lat);
    $("[data-id='place-longitude']").val(lng);
}

/**
 * Build the place location choices
 */
function setPlacesChoice()
{
    var placeLocations         = $("[data-id='place-locations']");
    var placeLocationTemplate  = $("[data-id='place-location-template']", "[data-id='place-locations-list-template']");
    var placeLocationsList     = $("[data-id='place-locations-list']");

    marker.setVisible(false);
    placeLocationsList.empty();
    jQuery.each(placeResults, function(index, data) {
        var placeLocation      = placeLocationTemplate.clone();
        var placeLocationInput = $("[data-role='place-location-input']", placeLocation);
        var placeLocationLabel = $("[data-role='place-location-label']", placeLocation);
        var placeLocationId    = 'place-location-' + index;

        placeLocationInput.attr('id', placeLocationId);
        placeLocationInput.attr('value', index);
        placeLocationLabel.text(data['formatted_address']);
        placeLocationLabel.attr('for', placeLocationId);
        placeLocationsList.append(placeLocation);
        placeLocation.show();
    });
    showPlaceLocationsPanel(placeLocations);
}

/**
 * Add a picture form
 */
function addPicture()
{
    var clone     = $("[data-role='picture']", "[data-id='pictures-template']").clone();
    var reg       = /__name__/g;
    var placeFile = $("[data-role='place-file']", clone);
    var pictures  = $("[data-id='pictures']");

    $("[data-role='picture-input']", clone).attr('disabled', false);

    if ($("[data-role='picture']", pictures).length === 3) {
        $("[data-id='picture-add']").attr('disabled', true);
    }

    placeFile.attr({
        disabled: false,
        name:     placeFile.attr('name').replace(reg, pictureIndex),
        id:       placeFile.attr('id').replace(reg, pictureIndex)
    });

    pictureIndex++;
    $("[data-role='picture-input']", clone).click();
    $("[data-role='picture-input']", clone).change(function(){
        setPicturePreview($(this));
    });
}

/**
 * Remove a picture form
 * @param input
 */
function removePicture(input)
{
    input.parents("[data-role='picture']").first().remove();
    if ($("[data-role='picture']", "[data-id='pictures']").length !== 3) {
        $("[data-id='picture-add']").attr('disabled', false);
    }
}

/**
 * Set the picture preview
 * @param input
 */
function setPicturePreview(input)
{
    var pictures     = $("[data-id='pictures']");
    var picture      = input.parents("[data-role='picture']").first();
    var preview      = picture.find("[data-role='preview']").first();
    var pictureError = $("[data-role='picture-error']", picture);
    var inputDOM     = input.get(0);

    if (!inputDOM.files || !inputDOM.files[0]) {
        return;
    }

    var file = inputDOM.files[0];
    var img  = new Image();

    img.src  = URL.createObjectURL(file);
    img.onload = function() {

        if (pictures.find(picture).length == 0) {
            pictures.append(picture);
        }
        if ((file.type !== 'image/jpeg' && file.type !== 'image/png') || file.size / 1000000 > 2) {
            input.val('');
            pictureError.show();
            setTimeout(function(){
                pictureError.fadeOut();
            }, 2000);
            return;
        }
        preview.css('background-image', 'url(' + img.src + ')');
        $("[data-role='picture-none']", picture).hide();
    };
}

/**
 * @param event
 */
function uploadPictures(event)
{
    var url   = $("[data-id='place-form']").data('upload-url');
    var calls = [];

    $("[data-role='picture-input']", "[data-id='pictures']").each(function(){
        var inputDOM = $(this).get(0);
        if (!inputDOM.files || !inputDOM.files[0]) {
            return;
        }
        var picture = $(this).parents("[data-role='picture']").first();
        var data    = new FormData();
        data.append('image', inputDOM.files[0]);
        calls.push(
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data){
                    if (data && data.file) {
                        $("[data-role='place-file']", picture).val(data.file)
                    }
                },
                error: function(){}
            })
        );
    });

    if (calls.length === 0) {
        return;
    }
    event.preventDefault();
    $.when.apply($, calls).then(function() {
        $("[data-id='place-form']").unbind('submit').submit();
    }, function(){
        $("[data-id='place-form']").unbind('submit').submit();
    });
}

/**
 * Add a beer label
 */
function updateSelectedBeers()
{
    var select           = $("[data-id='beer-choice']");
    var beersId          = select.val();
    var selected         = $("[data-id='selected-beers']");
    var beerTemplate     = $("[data-role='beer-label'][data-template='1']");
    var beerListTemplate =  $("[data-role='beer-list'][data-template='1']");
    var currentBeers     = {};

    $.each(beersId, function(index, id) {
        var brewery = $("option[value='" + id + "']", select).data('tokens');
        if (!currentBeers[brewery]) {
            currentBeers[brewery] = [];
        }
        currentBeers[brewery].push({'id': id, 'name': $("option[value='" + id + "']", select).text()});
    });

    $.each(currentBeers, function(key, obj) {
        obj.sort();
    });
    Object.keys(currentBeers).sort();

    $(selected).empty();
    $.each(currentBeers, function(brewery, list){
        var beerList = beerListTemplate.clone();
        beerList.attr('data-template', 0);
        $("[data-role='brewery-name']", beerList).text(brewery);
        $.each(list, function(index, obj) {
            var beer = beerTemplate.clone();
            beer.attr({
                'data-template': 0,
                'data-beer-id': obj.id
            });
            $("[data-role='beer-name']", beer).text(obj.name);
            beer.show();
            beerList.append(beer);
        });
        beerList.show();
        selected.append(beerList);
    });
}

/**
 * Remove deselected beer label
 * @param beerLabel
 */
function removeSelectedBeer(beerLabel)
{
    var select = $("[data-id='beer-choice']");
    var id     = beerLabel.data('beer-id');
    var list   = beerLabel.parent("[data-role='beer-list']");

    $("option[value='" + id + "']", select).prop('selected', false);

    beerLabel.remove();
    if ($("[data-role='beer-label']", list).length === 0) {
        list.remove();
    }
    select.selectpicker('refresh');
    filterBeers();
}

/**
 * Filter beers depending on brewery
 */
function filterBeers()
{
    var breweryName  = $("#brewery-filter").val();
    var selectPicker = $("[data-id='place_beers']").parent(".bootstrap-select");

    if (breweryName == null) {
        $("[data-optgroup]", selectPicker).show();
        return;
    }

    var optgroup      = selectPicker.find($("span:contains(" + breweryName + ")", 'li.dropdown-header'));
    var optindex      = optgroup.parent('li').data('optgroup');
    var selectedGroup = $("[data-optgroup='" + optindex + "']", selectPicker);

    selectedGroup.show();
    $("[data-optgroup]", selectPicker).not(selectedGroup).hide();
}

function addBeer()
{
    var brewerySelect = $("[data-id='brewery-choice']");
    var breweryName   = $("option:selected", brewerySelect).text();
    var fields        = $("[name^='beer[']");
    var formData       = new FormData();

    fields.each(function(){
        if ($(this).val() !== null) {
            var parent = $(this).parents('.form-group').first();
            formData.append($(this).attr('name'), $(this).val());
            parent.removeClass('has-error');
            $("[data-role='form-error']", parent).remove();
        }
    });

    $.ajax({
        type: 'POST',
        url: $("[data-id='beer-submit']").data('url'),
        dataType: 'json',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data, textStatus, xhr) {
            if (xhr.status === 200 || xhr.status === 201) {
                if (xhr.status === 201) {
                    refreshBreweryFilter(breweryName);
                    refreshBeers(breweryName, data.id, data.name);
                }
                updateBeerChoiceValues(data.id);
                $("#beer-new").slideUp();
            }
        },
        error: function(xhr){
            if (xhr.status === 400) {
                showBeerErrors(xhr.responseJSON);
            }
        }
    });
}

function refreshBreweryFilter(breweryName)
{
    var breweryFilter = $("#brewery-filter");
    var currentVal    = breweryFilter.val();
    if ($("option[value='" + breweryName + "']", breweryFilter).length != 0) {
        return;
    }
    var option = $("<option value='" + breweryName + "'>");
    option.text(breweryName);
    breweryFilter.append(option);
    var allOptions = $("option", breweryFilter);
    sortOptions(allOptions);
    breweryFilter.empty().append(allOptions);
    breweryFilter.val(currentVal);
    breweryFilter.selectpicker('refresh');
}

function refreshBeers(breweryName, beerId, beerName)
{
    var beersSelect  = $("[data-id='beer-choice']");
    var group        = $("optgroup[label='" + breweryName + "']", beersSelect);
    var option       = $("<option value='" + beerId + "' data-tokens='" + breweryName + "'>");

    option.text(beerName);
    if (group.length == 0) {
        group = $("<optgroup label='" + breweryName + "'>");
        beersSelect.append(group);
        var allGroups = $("optgroup", beersSelect);
        sortOptGroups(allGroups);
        beersSelect.empty().append(allGroups);
    }
    group.append(option);
    var allOptions = $("option", group);
    sortOptions(allOptions);
    group.empty().append(allOptions);
    updateBeerChoiceValues(beerId);
}

function updateBeerChoiceValues(beerId)
{
    var beersSelect  = $("[data-id='beer-choice']");
    var currentBeers = beersSelect.val();
    currentBeers.push(beerId);
    $.each(currentBeers, function(index, value){
        $("option[value='" + value + "']", beersSelect).attr('selected', true);
    });
    beersSelect.selectpicker('refresh');
    beersSelect.change();
}

function showBeerErrors(jsonErrors)
{
    $.each(jsonErrors, function(name, msg){
        var field  = $("[name='beer[" + name + "]'");
        var error  = $("[data-id='form-error-template']").clone();
        var parent = field.parents('.form-group').first();
        error.removeAttr('data-id');
        error.removeClass('form-error-template');
        $("[data-role='form-error-message']", error).html(msg);
        parent.addClass('has-error');
        parent.append(error);
    });
}