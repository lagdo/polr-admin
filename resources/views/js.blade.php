<script type="text/javascript">
// Stats data
var dayData = {};
var refererData = {};
var countryData = {};

// Datepicker dates
var datePickerLeftBound = '{{ $datePickerLeftBound }}';
var datePickerRightBound = '{{ $datePickerRightBound }}';
</script>

<script src="{{ url('lagdo/polr/admin') }}/js/base.js"></script>

<script src="{{ url('lagdo/polr/admin') }}/js/datatables.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/lodash.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/chart.bundle.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/datatables.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/jquery-jvectormap.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/jquery-jvectormap-world-mill.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/moment.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/home.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/index.js"></script>
<script src="{{ url('lagdo/polr/admin') }}/js/stats.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Set click handlers on buttons
    /*$('#change-password-form .change-password-btn').click(function() {
        {!! $jaxonUser->changePassword(rq()->form('change-password-form'))->confirm('Save the new password?') !!};
    });*/
    // URL shortening
    $('#check-link-availability').click(function() {
        {!! $jaxonLink->checkAvailability(jq('.custom-url-field')->val()) !!};
    });
    $('#shorten-btn').click(function() {
        {!! $jaxonLink->shorten(rq()->form('shorten-form')) !!};
    });
    // New user
    /*$('#users .new-user-add').click(function() {
        $('#new-user-form input.form-control').val('');
        $('.new-user-fields').show();
    });
    $('#users .new-user-cancel').click(function() {
        $('#new-user-form input.form-control').val('');
        $('.new-user-fields').hide();
    });
    $('#users .new-user-save').click(function() {
        {!! $jaxonUser->addNewUser(rq()->form('new-user-form'))->confirm('Save the new user?') !!};
    });*/
    // Events on datatables
    // Theses handlers are called anytime a new page is printed in a datatable
    $('#admin_users_table').on('draw.dt', function() {
        // Activate/Deactivate user access
        $('#admin_users_table .btn-disable-user').click(function(){
            {!! $jaxonUser->setUserStatus(jq()->parent()->parent()->attr('data-id'), 0)
                ->confirm('Disable access for user {1}?', jq()->parent()->parent()->attr('data-name')) !!};
        });
        $('#admin_users_table .btn-enable-user').click(function(){
            {!! $jaxonUser->setUserStatus(jq()->parent()->parent()->attr('data-id'), 1)
                ->confirm('Enable access for user {1}?', jq()->parent()->parent()->attr('data-name')) !!};
        });
        // Change user role
        $('#admin_users_table select.change-user-role').change(function(){
            {!! $jaxonUser->changeUserRole(jq()->parent()->parent()->attr('data-id'), jq()->val())
                ->confirm('Change role for user {1}?', jq()->parent()->parent()->attr('data-name')) !!};
        });
        // Show API info dialog
        $('#admin_users_table .btn-show-api-info').click(function(){
            {!! $jaxonUser->showAPIInfo(jq()->parent()->parent()->attr('data-id'), jq()->val()) !!};
        });
        // Delete user
        /*$('#admin_users_table .btn-delete-user').click(function(){
            {!! $jaxonUser->deleteUser(jq()->parent()->parent()->attr('data-id'))
                ->confirm('Delete user {1}?', jq()->parent()->parent()->attr('data-name')) !!};
        });*/
    });
    $('#admin_links_table').on('draw.dt', function() {
        // Edit long URL
        $('#admin_links_table .edit-long-link-btn').click(function(){
            {!! $jaxonLink->editLongUrl(jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Show link stats
        $('#admin_links_table .show-link-stats').click(function(){
            {!! $jaxonStats->showStats(jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Enable/disable link
        $('#admin_links_table .btn-disable-link').click(function(){
            {!! $jaxonLink->setLinkStatus(jq()->parent()->parent()->attr('data-ending'), 0)
                ->confirm('Disable link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) !!};
        });
        $('#admin_links_table .btn-enable-link').click(function(){
            {!! $jaxonLink->setLinkStatus(jq()->parent()->parent()->attr('data-ending'), 1)
                ->confirm('Enable link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Delete link
        $('#admin_links_table .btn-delete-link').click(function(){
            {!! $jaxonLink->deleteLink(jq()->parent()->parent()->attr('data-ending'))
                ->confirm('Delete link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) !!};
        });
    });
    $('#user_links_table').on('draw.dt', function() {
        // Edit long URL
        $('#user_links_table .edit-long-link-btn').click(function(){
            {!! $jaxonLink->editLongUrl(jq()->parent()->parent()->attr('data-ending')) !!};
        });
        // Show link stats
        $('#user_links_table .show-link-stats').click(function(){
            {!! $jaxonStats->showStats(jq()->parent()->parent()->attr('data-ending')) !!};
        });
    });
    // Refresh the stats
    $('#stats-buttons .btn-refresh-stats').click(function(){
        {!! $jaxonStats->refreshStats(rq()->form('stats-dates'), rq()->js('polr.stats.short_url')) !!};
    });
    // Clear stats filters
    $('#stats-buttons .btn-clear-stats').click(function(){
        polr.stats.short_url = '';
        $('#stats-filter').html('');
    });
    // Polr Endpoint selection button
    $('#btn-change-endpoint').click(function(){
        {!! $jaxonUser->selectEndpoint(rq()->select('select-endpoint')) !!};
    });
});
</script>
