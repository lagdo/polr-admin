@extends('layouts.base')

@section('css')
<link rel='stylesheet' href='/css/admin.css'>
<link rel='stylesheet' href='/css/datatables.min.css'>
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
        </ul>
    </div>
    <div class='col-md-10'>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="home">
                <h2>Welcome to your {{env('APP_NAME')}} dashboard!</h2>
                <p>Use the links on the left hand side to navigate your {{env('APP_NAME')}} dashboard.</p>
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
                    Old Password: <input class="form-control password-box" type='password' name='current_password' />
                    New Password: <input class="form-control password-box" type='password' name='new_password' />
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
                <a ng-click="state.showNewUserWell = !state.showNewUserWell" class="btn btn-primary btn-sm status-display">New</a>

                <div ng-if="state.showNewUserWell" class="new-user-fields well">
                    <table class="table">
                        <tr>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                        <tr id="new-user-form">
                            <td><input type="text" class="form-control" ng-model="newUserParams.username"></td>
                            <td><input type="password" class="form-control" ng-model="newUserParams.userPassword"></td>
                            <td><input type="email" class="form-control" ng-model="newUserParams.userEmail"></td>
                            <td>
                                <select class="form-control new-user-role" ng-model="newUserParams.userRole">
                                    @foreach  ($user_roles as $role_text => $role_val)
                                        <option value="{{$role_val}}">{{$role_text}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <a ng-click="addNewUser($event)" class="btn btn-primary btn-sm status-display new-user-add">Add</a>
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
        </div>
    </div>
</div>


@endsection

@section('js')

{{-- Include extra JS --}}
{!! $jaxonCss !!}
{!! $jaxonJs !!}
{!! $jaxonScript !!}

<script type="text/javascript">
$(document).ready(function() {
    // Set click handlers on buttons
    $('#api-reset-key').click(function() {
        {!! $jaxonUser->generateNewAPIKey($user_id)->confirm('Generate a new API key?') !!}
    });
    $('#change-password-form .change-password-btn').click(function() {
        {!! $jaxonUser->changePassword(rq()->form('change-password-form'))->confirm('Save the new password?') !!}
    });
});
</script>
@endsection