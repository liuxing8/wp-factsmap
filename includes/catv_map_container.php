<div class="row map-header text-center">
    <div class="col-md-12">
        <div class="col-sm-4 col-sm-offset-4">
            <button type="button" class="fm-btn map-modal-btn btn btn-blue" data-toggle="modal"
                    data-target="#filterModal">Map Filters
            </button>
            <?php catv_functions::set_edit_button(); ?>
        </div>
    </div>
</div>
<div class="row">
    <div id="response_area" class="col-md-12 text-center"></div>
</div>
<div class="" id="mapContainer"></div>
<div id="filters">
    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="fm_filters_form" method="post">
                        <input name="action" type="hidden" value="fm_ajax_hook"/>
                        <?php
                        echo '<input type="hidden" name="fm-ajax-filters-nonce" id="fm-ajax-filters-nonce" value="' . wp_create_nonce('fm-ajax-filters') . '" />';
                        ?>
                        <div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                        </div>
                        <div>
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#regions" aria-controls="regions"
                                                                          role="tab" data-toggle="tab">Regions</a></li>
                                <li role="presentation"><a href="#categories" aria-controls="categories" role="tab"
                                                           data-toggle="tab">Categories</a></li>
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="regions">
                                    <div class="well">
                                        <label class="mr-1" for="regions">Regions:</label><label><input type='checkbox'
                                                                                                        id='allRegions'
                                                                                                        checked
                                                                                                        value='all-regions'
                                                                                                        onclick="toggleAllRegions(this, 'region[]')"><strong
                                                    class="pl-05">Select all</strong></label>
                                        <div class="row">
                                            <?php
                                            catv_functions::set_filter_regions();
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="categories">
                                    <div class="well">
                                        <label class="mr-1" for="regions">Categories:</label><label><input
                                                    type='checkbox'
                                                    id="allCategories"
                                                    checked value='all-regions'
                                                    onclick="toggleAllRegions(this, 'category[]')"><strong
                                                    class="pl-05">Select all</strong></label>
                                        <div class="row">
                                            <?php
                                            catv_functions::set_filter_categories();
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-footer">
                            <div class='col-sm-6 filter-legend'><span class="glyphicon glyphicon-dot-selected pr-05"></span>Selected
                                <span class="glyphicon glyphicon-dot-not-selected pr-05"></span>Not selected</div>
                            <div class='col-sm-6 filter-save-btn'><button type="button" class="btn btn-default ml-05" data-dismiss="modal">Close</button><input type="button" id="submit-filters" class="btn btn-blue" value="Save Changes" onclick="submit_fm_filters()"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
