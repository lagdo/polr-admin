(function($scope) {
    /* Initialize $scope variables */
    $scope.datatables = {};
    $scope.jaxon = null;

    // Initialise Datatables elements
    $scope.initTables = function() {
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
            $scope.datatables['admin_users_table'] = $('#admin_users_table').DataTable($.extend({
                "ajax": function(data, callback, settings) {
                    // Pass the Datatables callback and settings to the Jaxon call
                    $scope.jaxon = {callback: callback, settings: settings};
                    Lagdo.Polr.Admin.App.User.getUsers(data);
                    // Clear the Datatables data after the Jaxon call
                    $scope.jaxon = null;
                },
                // "ajax": BASE_API_PATH + 'get_admin_users',

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
            $scope.datatables['admin_links_table'] = $('#admin_links_table').DataTable($.extend({
                "ajax": function(data, callback, settings) {
                    // Pass the Datatables callback and settings to the Jaxon call
                    $scope.jaxon = {callback: callback, settings: settings};
                    Lagdo.Polr.Admin.App.Link.getAdminLinks(data);
                    // Clear the Datatables data after the Jaxon call
                    $scope.jaxon = null;
                },
                // "ajax": BASE_API_PATH + 'get_admin_links',

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

        $scope.datatables['user_links_table'] = $('#user_links_table').DataTable($.extend({
            "ajax": function(data, callback, settings) {
                // Pass the Datatables callback and settings to the Jaxon call
                $scope.jaxon = {callback: callback, settings: settings};
                Lagdo.Polr.Admin.App.Link.getUserLinks(data);
                // Clear the Datatables data after the Jaxon call
                $scope.jaxon = null;
            },
            // "ajax": BASE_API_PATH + 'get_user_links',

            "columns": [
                {className: 'wrap-text', data: 'short_url', name: 'short_url'},
                {className: 'wrap-text', data: 'long_url', name: 'long_url'},
                {data: 'clicks', name: 'clicks'},
                {data: 'created_at', name: 'created_at'}
            ]
        }, datatables_config));
    };

    $scope.reloadLinkTables = function () {
        // Reload DataTables for affected tables
        // without resetting page
        if ('admin_links_table' in $scope.datatables) {
            $scope.datatables['admin_links_table'].ajax.reload(null, false);
        }

        $scope.datatables['user_links_table'].ajax.reload(null, false);
    };

    $scope.reloadUserTables = function () {
        $scope.datatables['admin_users_table'].ajax.reload(null, false);
    };

    function setTip(tip) {
        $("#tips").html(tip);
    }

    function changeTips(tcase) {
        switch (tcase) {
            case 1:
                setTip('Create an account to keep track of your links');
                break;
            case 2:
                setTip('Did you know you can change the URL ending by clicking on "Link Options"?');
                i = 1;
                break;
        }
    }

    /*
        Initialisation
    */
    $scope.init = function() {
        var optionsButton = $('#show-link-options');
        $('#options').hide();
        var slide = 0;
        optionsButton.click(function() {
            if (slide === 0) {
                $("#options").slideDown();
                slide = 1;
            } else {
                $("#options").slideUp();
                slide = 0;
            }
        });
        min = 1;
        max = 2;
        var i = Math.floor(Math.random() * (max - min + 1)) + min;
        changeTips(i);
        var tipstimer = setInterval(function() {
            changeTips(i);
            i++;
        }, 8000);

        $('.admin-nav a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
        });
        $('.new-user-fields').hide();

        $scope.initTables();
    };

    // $scope.init();
})(polr.home);

$(document).ready(function() {
    // Init the datatables
    if(!polr.ajax)
        polr.home.init();
});
