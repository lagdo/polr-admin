@extends('layouts.base')

@section('css')
<link rel='stylesheet' href='/css/admin.css'>
<link rel='stylesheet' href='/css/index.css' />
<link rel='stylesheet' href='/css/stats.css'>
<link rel='stylesheet' href='/css/datatables.min.css'>
<link rel='stylesheet' href='/css/jquery-jvectormap.css'>
<link rel='stylesheet' href='/css/bootstrap-datetimepicker.min.css'>
<style>
#mapChart {
    height: 24em;
}
</style>
@endsection

@section('content')
<div ng-controller="AdminCtrl" class="ng-root">
    <div class='col-md-2'>
        <ul class='nav nav-pills nav-stacked admin-nav' role='tablist'>
            <li role='presentation' aria-controls="home" class='admin-nav-item active'><a href='#home'>Home</a></li>
            <li role='presentation' aria-controls="settings" class='admin-nav-item'><a href='#settings'>Settings</a></li>
            <li role='presentation' aria-controls="links" class='admin-nav-item'><a href='#my-links'>My links</a></li>

            @if ($role == $admin_role)
            <li role='presentation' class='admin-nav-item'><a href='#all-links'>All links</a></li>
            <li role='presentation' class='admin-nav-item'><a href='#users'>Users</a></li>
            @endif

            @if ($api_active == 1)
            <li role='presentation' class='admin-nav-item'><a href='#developer'>Developer</a></li>
            @endif
            <li role='presentation' class='admin-nav-item invisible stats'><a href='#stats'>Stats</a></li>
        </ul>
    </div>
    <div class='col-md-10'>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="home">
                <h2>Welcome to your {{env('APP_NAME')}} dashboard!</h2>
                <p>Use the links on the left hand side to navigate your {{env('APP_NAME')}} dashboard.</p>

                @if (env('SETTING_SHORTEN_PERMISSION', false))
                <h3>Create a short URL</h3>
                <div class="" style="text-align: center;">
                    <form method='POST' id='shorten-form' role='form'>
                        <input type='url' autocomplete='off' class='form-control long-link-input'
                            placeholder='http://' name='link-url' />

                        <div class='row' id='options' ng-cloak>
                            <p>Customize link</p>

                            @if (!env('SETTING_PSEUDORANDOM_ENDING'))
                            {{-- Show secret toggle only if using counter-based ending --}}
                            <div class='btn-group btn-toggle visibility-toggler' data-toggle='buttons'>
                                <label class='btn btn-primary btn-sm active'>
                                    <input type='radio' name='options' value='p' checked /> Public
                                </label>
                                <label class='btn btn-sm btn-default'>
                                    <input type='radio' name='options' value='s' /> Secret
                                </label>
                            </div>
                            @endif

                            <div>
                                <div class='custom-link-text'>
                                    <h2 class='site-url-field'>{{env('APP_ADDRESS')}}/</h2>
                                    <input type='text' autocomplete="off" class='form-control custom-url-field' name='custom-ending' />
                                </div>
                                <div>
                                    <a href='javascript:void(0)' class='btn btn-success btn-xs check-btn' id='check-link-availability'>Check Availability</a>
                                    <div id='link-availability-status'></div>
                                </div>
                            </div>
                        </div>
                        <input class='btn btn-info' id='shorten-btn' value='Shorten' />
                        <a href='#' class='btn btn-warning' id='show-link-options'>Link Options</a>
                    </form>

                    <div id='tips' class='text-muted tips'>
                        <i class='fa fa-spinner'></i> Loading Tips...
                    </div>
                </div>
                @endif
            </div>

            <div role="tabpanel" class="tab-pane" id="my-links">
                <h3>My links</h3>
                @include('snippets.link_table', [
                    'table_id' => 'user_links_table'
                ])
            </div>

            <div role="tabpanel" class="tab-pane" id="settings">
                <h3>Change Password</h3>
                <form method='POST' id="change-password-form">
                    Old Password: <input class="form-control password-box" type='password' name='old_password' />
                    New Password: <input class="form-control password-box" type='password' name='new_password' />
                    Confirm Password: <input class="form-control password-box" type='password' name='new_password_confirmation' />
                    <input type="hidden" name='_token' value='{{csrf_token()}}' />
                    <input type='button' class='btn btn-success change-password-btn' value="Change" />
                </form>
            </div>

            @if ($role == $admin_role)
            <div role="tabpanel" class="tab-pane" id="all-links">
                <h3>All links</h3>
                @include('snippets.link_table', [
                    'table_id' => 'admin_links_table'
                ])
            </div>

            <div role="tabpanel" class="tab-pane" id="users">
                <h3 class="users-heading">Users</h3>
                <a class="btn btn-primary btn-sm status-display new-user-add">New</a>

                <div class="new-user-fields well">
                    <table class="table">
                        <tr>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                        <tr id="new-user-form">
                            <td><input type="text" class="form-control" name="username"></td>
                            <td><input type="password" class="form-control" name="user_password"></td>
                            <td><input type="email" class="form-control" name="user_email"></td>
                            <td>
                                <select class="form-control new-user-role" name="user_role">
                                    @foreach  ($user_roles as $role_text => $role_val)
                                        <option value="{{$role_val}}">{{$role_text}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm status-display new-user-save">Save</a>
                            </td>
                            <td>
                                <a class="btn btn-danger btn-sm status-display new-user-cancel">Cancel</a>
                            </td>
                        </tr>
                    </table>
                </div>

                @include('snippets.user_table', [
                    'table_id' => 'admin_users_table'
                ])

            </div>
            @endif

            @if ($api_active == 1)
            <div role="tabpanel" class="tab-pane" id="developer">
                <h3>Developer</h3>

                <p>API keys and documentation for developers.</p>
                <p>
                    Documentation:
                    <a href='http://docs.polr.me/en/latest/developer-guide/api/'>http://docs.polr.me/en/latest/developer-guide/api/</a>
                </p>

                <h4>API Key: </h4>
                <div class='row'>
                    <div class='col-md-8'>
                        <input class='form-control status-display' disabled type='text' id='api-key-value' value='{{$api_key}}'>
                    </div>
                    <div class='col-md-4'>
                        <a href="javascript:void(0);" id="api-reset-key" class="btn btn-danger">Reset</a>
                    </div>
                </div>


                <h4>API Quota: </h4>
                <h2 class='api-quota'>
                    @if ($api_quota == -1)
                        unlimited
                    @else
                        <code>{{$api_quota}}</code>
                    @endif
                </h2>
                <span> requests per minute</span>
            </div>
            @endif

            <div role="tabpanel" class="tab-pane" id="stats">
                <h3>Stats</h3>
                <div id="stats-header"></div>
                <div id="stats-content" class="bottom-padding"></div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('js')

{{-- Include extra JS --}}
<script src='/js/datatables.min.js'></script>
<script src='/js/lodash.min.js'></script>
<script src='/js/chart.bundle.min.js'></script>
<script src='/js/datatables.min.js'></script>
<script src='/js/jquery-jvectormap.min.js'></script>
<script src='/js/jquery-jvectormap-world-mill.js'></script>
<script src='/js/moment.min.js'></script>
<script src='/js/bootstrap-datetimepicker.min.js'></script>
<script src='/js/home.js'></script>
<script src='/js/index.js'></script>
<script src='/js/stats.js'></script>

{!! $jaxonCss !!}
{!! $jaxonJs !!}
{!! $jaxonScript !!}

<script type="text/javascript">
// Stats data
var dayData = {};
var refererData = {};
var countryData = {};

// Datepicker dates
var datePickerLeftBound = '';
var datePickerRightBound = '';

$(document).ready(function() {
    // Set click handlers on buttons
    $('#api-reset-key').click(function() {
        {!! $jaxonUser->generateNewAPIKey($user_id, true)->confirm('Generate a new API key?') !!};
    });
    $('#change-password-form .change-password-btn').click(function() {
        {!! $jaxonUser->changePassword(rq()->form('change-password-form'))->confirm('Save the new password?') !!};
    });
    // URL shortening
    $('#check-link-availability').click(function() {
        {!! $jaxonLink->checkAvailability(jq('.custom-url-field')->val()) !!};
    });
    $('#shorten-btn').click(function() {
        {!! $jaxonLink->shorten(rq()->form('shorten-form')) !!};
    });
    // New user
    $('#users .new-user-add').click(function() {
        $('#new-user-form input.form-control').val('');
        $('.new-user-fields').show();
    });
    $('#users .new-user-cancel').click(function() {
        $('#new-user-form input.form-control').val('');
        $('.new-user-fields').hide();
    });
    $('#users .new-user-save').click(function() {
        {!! $jaxonUser->addNewUser(rq()->form('new-user-form'))->confirm('Save the new user?') !!};
    });
    // Events on datatables
    // Theses handlers are called anytime a new page is printed in a datatable
    $('#admin_users_table').on('draw.dt', function() {
        // Activate/Deactivate user access
        {!! jq('#admin_users_table .btn-disable-user')->click(
            $jaxonUser->setUserStatus(jq()->parent()->parent()->attr('data-id'), 0)
                ->confirm('Disable access for user {1}?', jq()->parent()->parent()->attr('data-name')) ) !!};
        {!! jq('#admin_users_table .btn-enable-user')->click(
            $jaxonUser->setUserStatus(jq()->parent()->parent()->attr('data-id'), 1)
                ->confirm('Enable access for user {1}?', jq()->parent()->parent()->attr('data-name')) ) !!};
        // Change user role
        {!! jq('#admin_users_table select.change-user-role')->change(
            $jaxonUser->changeUserRole(jq()->parent()->parent()->attr('data-id'), jq()->val())
                ->confirm('Change role for user {1}?', jq()->parent()->parent()->attr('data-name')) ) !!};
        // Show API info dialog
        {!! jq('#admin_users_table .btn-show-api-info')->click(
            $jaxonUser->showAPIInfo(jq()->parent()->parent()->attr('data-id'), jq()->val()) ) !!};
        // Delete user
        {!! jq('#admin_users_table .btn-delete-user')->click(
            $jaxonUser->deleteUser(jq()->parent()->parent()->attr('data-id'))
                ->confirm('Delete user {1}?', jq()->parent()->parent()->attr('data-name')) ) !!};
    });
    $('#admin_links_table').on('draw.dt', function() {
        // Edit long URL
        {!! jq('#admin_links_table .edit-long-link-btn')->click(
            $jaxonLink->editLongUrl(jq()->parent()->parent()->attr('data-id')) ) !!};
        // Show link stats
        {!! jq('#admin_links_table .show-link-stats')->click(
            $jaxonStats->showLinkStats(jq()->parent()->parent()->attr('data-ending')) ) !!};
        // Enable/disable link
        {!! jq('#admin_links_table .btn-disable-link')->click(
            $jaxonLink->setLinkStatus(jq()->parent()->parent()->attr('data-id'), 0)
                ->confirm('Disable link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) ) !!};
        {!! jq('#admin_links_table .btn-enable-link')->click(
            $jaxonLink->setLinkStatus(jq()->parent()->parent()->attr('data-id'), 1)
                ->confirm('Enable link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) ) !!};
        // Delete link
        {!! jq('#admin_links_table .btn-delete-link')->click(
            $jaxonLink->deleteLink(jq()->parent()->parent()->attr('data-id'))
                ->confirm('Delete link with ending {1}?', jq()->parent()->parent()->attr('data-ending')) ) !!};
    });
    $('#user_links_table').on('draw.dt', function() {
        // Edit long URL
        {!! jq('#user_links_table .edit-long-link-btn')->click(
            $jaxonLink->editLongUrl(jq()->parent()->parent()->attr('data-id')) ) !!};
        // Show link stats
        {!! jq('#user_links_table .show-link-stats')->click(
            $jaxonStats->showLinkStats(jq()->parent()->parent()->attr('data-ending')) ) !!};
    });
});
</script>
@endsection
