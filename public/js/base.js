// Escape jQuery selectors
function esc_selector(selector) {
    return selector.replace( /(:|\.|\[|\]|,)/g, "\\$1" );
}

jQuery.fn.clearForm = function() {
    // http://stackoverflow.com/questions/6364289/clear-form-fields-with-jquery
    $(this).find('input').not(':button, :submit, :reset, :hidden')
        .val('')
        .removeAttr('checked')
        .removeAttr('selected');

    return this;
};

$(document).ready(function() {
    // AJAX settings
    if((csrfToken = $('meta[name="csrf-token"]').attr('content')))
    {
        // Add the CSRF token to all Ajax and Jaxon requests
        $.ajaxSetup({headers: {'X-CSRF-TOKEN': csrfToken}});
        jaxon.config.postHeaders = {'X-CSRF-TOKEN': csrfToken};
    }

    $('.admin-nav a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
    $('.new-user-fields').hide();

    // Datatables
    var datatables = {};
    var datatables_config = {
        'autoWidth': false,
        'processing': true,
        'serverSide': true,

        'drawCallback': function () {
            // Compile Angular bindings on each draw
            // $compile($(this))($scope);
        }
    };

    if ($('#admin_users_table').length) {
        datatables['admin_users_table'] = $('#admin_users_table').DataTable($.extend({
            "ajax": BASE_API_PATH + 'admin/get_admin_users',

            "columns": [
                {className: 'wrap-text', data: 'username', name: 'username'},
                {className: 'wrap-text', data: 'email', name: 'email'},
                {data: 'created_at', name: 'created_at'},

                {data: 'toggle_active', name: 'toggle_active', orderable: false, searchable: false},
                {data: 'api_action', name: 'api_action', orderable: false, searchable: false},
                {data: 'change_role', name: 'change_role', orderable: false, searchable: false},
                {data: 'delete', name: 'delete', orderable: false, searchable: false}
            ]
        }, datatables_config));
    }
    if ($('#admin_links_table').length) {
        datatables['admin_links_table'] = $('#admin_links_table').DataTable($.extend({
            "ajax": BASE_API_PATH + 'admin/get_admin_links',

            "columns": [
                {className: 'wrap-text', data: 'short_url', name: 'short_url'},
                {className: 'wrap-text', data: 'long_url', name: 'long_url'},
                {data: 'clicks', name: 'clicks'},
                {data: 'created_at', name: 'created_at'},
                {data: 'creator', name: 'creator'},

                {data: 'disable', name: 'disable', orderable: false, searchable: false},
                {data: 'delete', name: 'delete', orderable: false, searchable: false}

            ]
        }, datatables_config));
    }

    datatables['user_links_table'] = $('#user_links_table').DataTable($.extend({
        "ajax": BASE_API_PATH + 'admin/get_user_links',

        "columns": [
            {className: 'wrap-text', data: 'short_url', name: 'short_url'},
            {className: 'wrap-text', data: 'long_url', name: 'long_url'},
            {data: 'clicks', name: 'clicks'},
            {data: 'created_at', name: 'created_at'}
        ]
    }, datatables_config));
});

// Output helpful console message
console.log('%cPolr', 'font-size:5em;color:green');
console.log('%cNeed help? Open a ticket: https://github.com/cydrobolt/polr', 'color:blue');
console.log('%cDocs: https://docs.polr.me', 'color:blue');

// Set up Angular module
// var polr = angular.module('polr',[]);
