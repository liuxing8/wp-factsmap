function placeMarker(location) {
    marker.setPosition(location);
}

function setLatLngFields(location) {
    document.getElementById('fm_fact_lat').value = location.lat();
    document.getElementById('fm_fact_lng').value = location.lng();
}

$(document).ready(function () {
    jQuery('#fm_fact_lat').on('input propertychange paste', function () {
        var latlng = new google.maps.LatLng(jQuery('#fm_fact_lat').val(), jQuery('#fm_fact_lng').val());
        placeMarker(latlng);
    });

    jQuery('#fm_fact_lng').on('input propertychange paste', function () {
        var latlng = new google.maps.LatLng(jQuery('#fm_fact_lat').val(), jQuery('#fm_fact_lng').val());
        placeMarker(latlng);
    });
});

function codeAddress() {
    var address = document.getElementById('fact_address').value;
    geocoder.geocode({'address': address}, function (results, status) {
        if (status == 'OK') {
            map.setCenter(results[0].geometry.location);
            map.setZoom(13);
            marker.setPosition(results[0].geometry.location);
            setLatLngFields(results[0].geometry.location);
            jQuery('#search_result').html("Best match for your search: <strong>" + results[0].formatted_address + "</strong><br>If this is not the result you were searching for, try to be more specific.");
        } else {
            jQuery('#search_result').html('<span class="admin-fm-search-fail">No match for your search, try to be more specific.</span>');
        }
    });
}