function initMap() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            if (lastAddress) {
                initAutocomplete();
                getLatLongPosition(lastAddress_latitude, lastAddress_longitude);
            } else {
                initAutocomplete();
                getLatLongPosition(position.coords.latitude, position.coords.longitude);
            }

        });
    } else {
        alert("Sorry, your browser does not support HTML5 geolocation.");
    }
}

function getLatLongPosition(latitude, longitude) {
    const myLatlng = {
        lat: parseFloat(latitude),
        lng: parseFloat(longitude)
    };
    const map = new google.maps.Map(document.getElementById("googleMap"), {
        zoom: 15,
        center: myLatlng,
    });

    // Create the initial InfoWindow.
    let infoWindow = new google.maps.InfoWindow({
        content: "Click the map to get latitude & longitude!",
        position: myLatlng,
    });

    infoWindow.open(map);
    // Configure the click listener.
    var marker;
    let total = 0;
    map.addListener("click", (mapsMouseEvent) => {

        // Close the current InfoWindow.
        infoWindow.close();
        // Create a new InfoWindow.
        infoWindow = new google.maps.InfoWindow({
            position: mapsMouseEvent.latLng,
        });

        var latLng = mapsMouseEvent.latLng.toJSON();

        var latlng = new google.maps.LatLng(latLng.lat, latLng.lng);
        var geocoder = geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'latLng': latlng
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    $('#autocomplete-input').val(results[1].formatted_address);
                }
            }
        });

        let dis = distance(parseFloat(locationLat), parseFloat(locationLong), latLng.lat, latLng.lng)
        let delivery_charge = basicCharge;

        if (dis > freeZone) {
            dis = dis - parseFloat(freeZone);
            delivery_charge = dis * chragePerKilo + parseFloat(basicCharge);
        }

        if(orderType){
             total = parseInt(subtotal) - parseInt(couponAmount);
        }else {
             total = (parseInt(subtotal)- parseInt(couponAmount) ) + parseInt(delivery_charge);
        }


        $('#delivery_chearge').text(parseInt(delivery_charge));
        $('#total').text(total);
        $('#total_delivery_charge').val(parseInt(delivery_charge));

        if (marker)
            marker.setMap(null);
        marker = new google.maps.Marker({
            position: myLatlng,
            map,
            draggable: true,
            title: "Your current location.",
        });

        changeMarkerPosition(latLng, marker)

    });

    let dis = distance(locationLat, locationLong, latitude, longitude)
    var delivery_charge = basicCharge;

    if (dis > freeZone) {
        dis = dis - parseFloat(freeZone);
        delivery_charge = dis * chragePerKilo + parseFloat(basicCharge);
    }

    total = 0;
    if(orderType){
        total = parseInt(subtotal) - parseInt(couponAmount);
    }else {
        total = (parseInt(subtotal)- parseInt(couponAmount) ) + parseInt(delivery_charge);
    }

    let vat = parseFloat(totalVAT);
    total = total+vat;
    $('#delivery_chearge').text(parseFloat(delivery_charge).toFixed(2));
    $('#vat').text(parseFloat(vat).toFixed(2));
    $('#total').text(parseFloat(total).toFixed(2));    
    $('#total_delivery_charge').val(parseInt(delivery_charge));
    $('#total_vat').val(vat);

    marker = new google.maps.Marker({
        position: myLatlng,
        map,
        draggable: true,
        title: "Your current location.",
    });
}

function changeMarkerPosition(latLng, marker) {
    var latlng = new google.maps.LatLng(latLng.lat, latLng.lng);
    marker.setPosition(latlng);
}

var mapLat = mapLat;
var mapLong = mapLong;

function initAutocomplete() {
    if (lastAddress) {
        getLocation(lastAddress_latitude, lastAddress_longitude);
    } else {
        if (mapLat != '' && mapLong != '') {
            getLocation(mapLat, mapLong);
        } else {
            getLocation(null, null);
        }
    }

    var input = document.getElementById('autocomplete-input');
    var autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener('place_changed', function () {
        var place = autocomplete.getPlace();
        getLatLongPosition(place.geometry.location.lat(), place.geometry.location.lng());
        $('#lat').val(place.geometry.location.lat());
        $('#long').val(place.geometry.location.lng());

        if (!place.geometry) {
            return;
        }
    });

    if ($('.main-search-input-item')[0]) {
        setTimeout(function () {
            $(".pac-container").prependTo("#autocomplete-container");
        }, 300);
    }
}
var geocoder;

function getLocation(lat, long) {

    geocoder = new google.maps.Geocoder();
    if (navigator.geolocation) {
        if (lat && long) {
            showGetPosition(lat, long)
        } else {
            navigator.geolocation.getCurrentPosition(showPosition);
        }
    } else {
        var msg = "Geolocation is not supported by this browser.";
        alert(msg);
    }
}

function showPosition(position) {
    var Latitude = position.coords.latitude;
    var Longitude = position.coords.longitude;
    $('#lat').val(Latitude);
    $('#long').val(Longitude);
    getLatLongPosition(Latitude, Longitude);

    var latlng = new google.maps.LatLng(Latitude, Longitude);
    geocoder.geocode({
        'latLng': latlng
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                $('#autocomplete-input').val(results[0].formatted_address);
            }
        }
    })

}

function showGetPosition(lat, long) {
    var Latitude = lat;
    var Longitude = long;
    $('#lat').val(Latitude);
    $('#long').val(Longitude);


    var latlng = new google.maps.LatLng(Latitude, Longitude);
    geocoder.geocode({
        'latLng': latlng
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                $('#autocomplete-input').val(results[0].formatted_address);
            }
        }
    })

}



//modal


function editBtn(id) {

    let editurl = $('#edit' + id).attr('data-url');
    let updateurl = $('#edit' + id).attr('data-attr');
    localStorage.setItem("total_delivery_charge", $('#total_delivery_charge').val());

    $.ajax({
        type: 'GET',
        url: editurl,
        dataType: "html",
        success: function (data) {
            let address = JSON.parse(data);
            $('#addressModal').modal('show');
            $("#addressForm").attr('action', updateurl);
            $("#formMethod").val('PUT');
            $("#autocomplete-input").val(address.address);
            $("#lat").val(address.latitude);
            $("#long").val(address.longitude);
            $("#id").val(address.id);
            $("#apartment").val(address.apartment);
            $("#label").val(address.label);
            $("#label_name").val(address.label_name);
            if (address.label == 15) {
                $('.label-name').show();
            }

        }
    });

}




$(document).on('click', '#add-new', function (event) {
    let href = $('#add-new').attr('data-attr');
    modalshow(href);
});
$(document).on('click', '#address-btn', function (event) {
    window.localStorage.removeItem("total_delivery_charge");
    $("#addressForm").submit()
});

if ($('.check-errors1').text() != "" || $('.check-errors2').text() != "") {

    let href = $('#edit-url').attr('data-attr');
    if ($("#formMethod").val() == 'PUT') {
        href = $('#edit-url').attr('data-attr');
    } else {
        href = $('#add-new').attr('data-attr');
    }
    modalshow(href);
}

function modalshow(href) {
    localStorage.setItem("total_delivery_charge", $('#total_delivery_charge').val());
    $('#addressModal').modal('show');
    $("#addressForm").attr('action', href);
}

$(document).on('click', '#modalClose', function (event) {
    const prevDeliveryCharge = localStorage.getItem("total_delivery_charge");
    let total = 0;
    if(orderType){
        total = parseInt(subtotal) - parseInt(couponAmount);
    }else {
        total = (parseInt(subtotal)- parseInt(couponAmount) ) + parseInt(prevDeliveryCharge);
    }

    $('#delivery_chearge').text(parseInt(prevDeliveryCharge));
    $('#total').text(total);
    $('#total_delivery_charge').val(parseInt(prevDeliveryCharge));

    window.localStorage.removeItem("total_delivery_charge");

});

if ($('#label').val() == 15) {
    $('.label-name').show();
} else {
    $('.label-name').hide();
}

$('#label').on('change', function () {
    if ($('#label').val() == 15) {
        $('.label-name').show();
    } else {
        $('.label-name').hide();
    }
});

function distance(lat1, lon1, lat2, lon2) {
    var radlat1 = Math.PI * lat1 / 180
    var radlat2 = Math.PI * lat2 / 180
    var theta = lon1 - lon2
    var radtheta = Math.PI * theta / 180
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist)
    dist = dist * 180 / Math.PI
    dist = dist * 60 * 1.1515
    dist = dist * 1.609344
    return dist
}


function deliveryAddress(latitude, longitude) {
    let dis = distance(locationLat, locationLong, latitude, longitude);
    var delivery_charge = basicCharge;

    if (dis > freeZone) {
        dis = dis - parseFloat(freeZone);
        delivery_charge = dis * chragePerKilo + parseFloat(basicCharge);
    }

    let total = 0;
    if(orderType){
        total = parseInt(subtotal) - parseInt(couponAmount);
    }else {
        total = (parseInt(subtotal)- parseInt(couponAmount) ) + parseInt(delivery_charge);
    }


    $('#delivery_chearge').text(parseInt(delivery_charge));
    $('#total').text(total);
    $('#total_delivery_charge').val(parseInt(delivery_charge));
}
