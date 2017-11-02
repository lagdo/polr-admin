                <h3 class="users-heading">Users</h3>
                <!-- <a class="btn btn-primary btn-sm status-display new-user-add">New</a> -->

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
                            <td id="user-roles">
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

                @include('polr_admin::snippets.user_table', [
                    'table_id' => 'admin_users_table'
                ])
