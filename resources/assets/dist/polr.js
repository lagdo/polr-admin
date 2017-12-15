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

// Output helpful console message
// console.log('%cPolr', 'font-size:5em;color:green');
// console.log('%cNeed help? Open a ticket: https://github.com/cydrobolt/polr', 'color:blue');
// console.log('%cDocs: https://docs.polr.me', 'color:blue');

//Set up the Polr object
var polr = {
    home: {},
    stats: {}
};


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


var parseInputDate = function (inputDate) {
    return moment(inputDate);
};

(function($scope) {
    // The short URL whose stats are displayed.
    $scope.short_url = '';

    // Callbacks for Jaxon requests
    $scope.requestCallbacks = {
        timers: [],
        onRequest: function() {
            $('#stats-buttons .btn-refresh-stats').prepend('<i class="fa fa-spinner fa-spin" />');
        },
        onFailure: function() {
            $('#stats-buttons .btn-refresh-stats i').remove();
        },
        onSuccess: function() {
            $('#stats-buttons .btn-refresh-stats i').remove();
        }
    };

    $scope.populateEmptyDayData = function () {
        // Populate empty days in $scope.dayData with zeroes

        // Number of days in range
        var numDays = moment(datePickerRightBound).diff(moment(datePickerLeftBound), 'days');
        var i = moment(datePickerLeftBound);

        var daysWithData = {};

        // Generate hash map to keep track of dates with data
        _.each($scope.dayData, function (point) {
            var dayDate = point.x;
            daysWithData[dayDate] = true;
        });

        // Push zeroes for days without data
        _.each(_.range(0, numDays), function () {
            var formattedDate = i.format('YYYY-MM-DD');

            if (!(formattedDate in daysWithData)) {
                // If day does not have data, fill in with 0
                $scope.dayData.push({
                    x: formattedDate,
                    y: 0
                })
            }

            i.add(1, 'day');
        });

        // Sort dayData from least to most recent
        // to ensure Chart.js displays the data correctly
        $scope.dayData = _.sortBy($scope.dayData, ['x'])
    }

    $scope.initDayChart = function () {
        var ctx = $("#dayChart");

        // Populate empty days in dayData
        $scope.populateEmptyDayData();

        $scope.dayChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Clicks',
                    data: $scope.dayData,
                    pointHoverBackgroundColor: "rgba(75,192,192,1)",
                    pointHoverBorderColor: "rgba(220,220,220,1)",
                    backgroundColor: "rgba(75,192,192,0.4)",
                    borderColor: "rgba(75,192,192,1)",
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            min: 0
                        }
                    }]
                }
            }
        });
    };

    $scope.initRefererChart = function () {
        // Traffic sources
        var ctx = $("#refererChart");

        var srcLabels = [];
        // var bgColors = [];
        var bgColors = [ '#003559', '#162955', '#2E4272', '#4F628E', '#7887AB', '#b9d6f2'];
        var srcData = [];

        _.each($scope.refererData, function (item) {
            if (srcLabels.length > 6) {
                // If more than 6 referers are listed, push the seventh and
                // beyond into "other"
                srcLabels[6] = 'Other';
                srcData[6] += item.clicks;
                bgColors[6] = 'brown';
                return;
            }

            srcLabels.push(item.label);
            srcData.push(item.clicks);
        });

        $scope.refererChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: srcLabels,
                datasets: [{
                    data: srcData,
                    backgroundColor: bgColors
                }]
            }
        });

        $('#refererTable').DataTable();
    };

    $scope.initCountryChart = function () {
        var parsedCountryData = {};

        _.each($scope.countryData, function(country) {
            parsedCountryData[country.label] = country.clicks;
        });

        $('#mapChart').vectorMap({
            map: 'world_mill',
            series: {
                regions: [{
                    values: parsedCountryData,
                    scale: ['#C8EEFF', '#0071A4'],
                    normalizeFunction: 'polynomial'
                }]
            },
            onRegionTipShow: function(e, el, code) {
                el.html(el.html()+' (' + (parsedCountryData[code] || 0) + ')');
            }
        });
    };

    $scope.initDatePickers = function () {
        var $leftPicker = $('#left-bound-picker');
        var $rightPicker = $('#right-bound-picker');

        var datePickerOptions = {
            showTodayButton: true,
            format: 'YYYY-MM-DD HH:mm'
        }

        $leftPicker.datetimepicker(datePickerOptions);
        $rightPicker.datetimepicker(datePickerOptions);

        $leftPicker.data("DateTimePicker").parseInputDate(parseInputDate);
        $rightPicker.data("DateTimePicker").parseInputDate(parseInputDate);

        $leftPicker.data("DateTimePicker").date(datePickerLeftBound, Date, moment, null);
        $rightPicker.data("DateTimePicker").date(datePickerRightBound, Date, moment, null);
    }

    $scope.initData = function (day, referer, country, leftBound, rightBound) {
        // Stats data
        dayData = day;
        refererData = referer;
        countryData = country;
        // Datepicker dates
        datePickerLeftBound = leftBound;
        datePickerRightBound = rightBound;
    };

    $scope.initCharts = function () {
        $scope.dayChart = null;
        $scope.refererChart = null;
        $scope.countryChart = null;

        $scope.dayData = dayData;
        $scope.refererData = refererData;
        $scope.countryData = countryData;

        $scope.initDayChart();
        $scope.initRefererChart();
        $scope.initCountryChart();
    };

    $scope.init = function () {
        $scope.initCharts();
        $scope.initDatePickers();
    };

    // $scope.init();

})(polr.stats);
