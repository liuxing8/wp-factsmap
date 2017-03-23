function submit_fm_filters() {
    filteredFacts = new Array();
    jQuery('#response_area').html("<div class='filter-apply'><strong>Applying changes, please wait.</strong></div>");
    jQuery('#filterModal').modal('hide');
    jQuery.post(
        fm_ajax_script.ajaxurl, //URL
        jQuery("#fm_filters_form").serialize(), //data
        function (filterData) { //success
            filteredFacts = filterData;
            if (isEmpty(filteredFacts)) {
                jQuery('#response_area').html("<div class='filter-empty'><strong>No facts found matching your filters.</strong></div>");
                setFilteredMarkers(filteredFacts);
                setDefaultView();
            }
            else {
                jQuery('#response_area').html("");
                setFilteredMarkers(filteredFacts);
                fitView();
            }
        },
        "json"
    );
}

function isEmpty(obj) {
    for (var key in obj) {
        if (obj.hasOwnProperty(key))
            return false;
    }
    return true;
}
