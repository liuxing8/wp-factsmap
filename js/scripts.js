var map, infoWindow;
var markers = [];
var activeFilters = [];

function initializeMap() {
    map = new google.maps.Map(document.getElementById('mapContainer'), {
        center: {lat: 49.7976857, lng: 15.5096585},
        zoom: 7,
        minZoom: 4,
        fullscreenControl: true
    });

    infoWindow = new google.maps.InfoWindow(
        {
            maxWidth: 300
        });

    setMarkers(marker_data);
}

function setInfoWindow(marker, content) {
    google.maps.event.addListener(marker, 'click', function () {
        infoWindow.setContent(content);
        infoWindow.open(map, this);
    });
}

function setMarkers(marker_data) {
    for (i in marker_data) {
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(marker_data[i].lat, marker_data[i].lng),
            map: map,
            postName: marker_data[i].post_name,
            postTitle: marker_data[i].post_title,
            postImg: marker_data[i].thumbnail
        });

        var content;
        content = '<a href="' + fact_path + marker.postName + '" target="_blank"><h4>' + marker.postTitle + '</h4></a>';
        if(marker.postImg != ''){
            content = content + '<div class="info-img-container"><a href="' + fact_path + marker.postName + '" target="_blank"><img src="' + marker.postImg + '" class="img-responsive info-img"></a></div>';
        }
        content = content + '<a class="btn btn-blue mt-05" href="' + fact_path + marker.postName + '" target="_blank">Read More...</a>';

        setInfoWindow(marker, content);

        markers.push(marker);
    }
}

function setMarkersMap(map, markerArray) {
    for (var i = 0; i < markerArray.length; i++) {
        markerArray[i].setMap(map);
    }
}

function clearMap() {
    setMarkersMap(null, markers);
    markers = [];
}

function setFilteredMarkers(markerArray) {
    clearMap();
    setMarkers(markerArray);
    setMarkersMap(map, markers);
}

function fitView() {
    var bounds = new google.maps.LatLngBounds();
    for(i=0;i<markers.length;i++) {
        bounds.extend(markers[i].getPosition());
    }
    map.setCenter(bounds.getCenter());
    google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
        this.setZoom(map.getZoom());

        if (this.getZoom() > 15) {
            this.setZoom(15);
        }
    });
    map.fitBounds(bounds);
}

function setDefaultView() {
    var defaultLatLng = new google.maps.LatLng(49.7976857, 15.5096585);
    map.setCenter(defaultLatLng);
    map.setZoom(7);
}

//Filter controls

function toggleAllRegions(source, type) {
    regions = document.getElementsByName(type);
    for (var i = 0; i < regions.length; i++) {
        regions[i].checked = source.checked;
    }
}

function uncheckSelectAll(source, type) {
    selectAll = document.getElementById(type);
    if (!source.checked) {
        selectAll.checked = false;
    }
    else if (allChecked(source.name)) {
        selectAll.checked = true;
    }
}

function allChecked(type) {
    regions = document.getElementsByName(type);
    for (var i = 0; i < regions.length; i++) {
        if (!regions[i].checked) {
            return false;
        }
    }
    return true;
}