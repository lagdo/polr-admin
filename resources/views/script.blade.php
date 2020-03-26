<script>
polr.home.setHandlers = function(){
    // URL shortening
    $('#check-link-availability').click(function() {
        {!! $link->checkAvailability(pr()->js('polr.home.server'), jq('.custom-url-field')->val()) !!};
    });
    $('#shorten-btn').click(function() {
        {!! $link->shorten(pr()->js('polr.home.server'), pr()->form('shorten-form')) !!};
    });
    // Events on datatables
    $('#admin_links_table').on('draw.dt', function() {
        // Edit long URL
        $('#admin_links_table .edit-long-link-btn').click(function(){
            {!! $link->editLongUrl(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Show link stats
        $('#admin_links_table .show-link-stats').click(function(){
            {!! $stats->showStats(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Enable/disable link
        $('#admin_links_table .btn-disable-link').click(function(){
            {!! $link->setLinkStatus(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending'), 'disable')
                ->confirm('Disable link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) !!};
        });
        $('#admin_links_table .btn-enable-link').click(function(){
            {!! $link->setLinkStatus(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending'), 'enable')
                ->confirm('Enable link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Delete link
        $('#admin_links_table .btn-delete-link').click(function(){
            {!! $link->deleteLink(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending'))
                ->confirm('Delete link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) !!};
        });
    });
    $('#user_links_table').on('draw.dt', function() {
        // Edit long URL
        $('#user_links_table .edit-long-link-btn').click(function(){
            {!! $link->editLongUrl(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Show link stats
        $('#user_links_table .show-link-stats').click(function(){
            {!! $stats->showStats(pr()->js('polr.home.server'), jq()->parent()->parent()->attr('data-ending')) !!};
        });
    });
    // Refresh the stats
    $('#stats-buttons .btn-refresh-stats').click(function(){
        {!! $stats->refreshStats(pr()->js('polr.home.server'), pr()->js('polr.stats.ending'), pr()->form('stats-dates')) !!};
    });
    // Clear stats filters
    $('#stats-buttons .btn-clear-stats').click(function(){
        polr.stats.ending = '';
        $('#stats-filter').html('');
        // Hide the Clear button in Stats tab
        $('#stats-buttons .clear-stats').hide();
    });
    // Hide the Clear button in Stats tab
    $('#stats-buttons .clear-stats').hide();
};

polr.home.getAdminLinks = function(data) {
    {!! $link->getAdminLinks(pr()->js('polr.home.server'), pr()->js('data')) !!}
};
polr.home.getUserLinks = function(data) {
    {!! $link->getUserLinks(pr()->js('polr.home.server'), pr()->js('data')) !!}
};
polr.init = function(leftBound, rightBound) {
    polr.home.server = {!! pr()->select('select-server') !!};
    polr.home.init();
    polr.stats.leftBound = leftBound;
    polr.stats.rightBound = rightBound;
    polr.stats.initDatePickers();
    polr.home.setHandlers();
};
</script>
