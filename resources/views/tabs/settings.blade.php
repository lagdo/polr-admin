                <h3>Polr endpoints</h3>

                <div class="">
                    <h4>Change endpoint</h4>
                    <form role='form'>
                        <div class="row">
                            <div class="col-md-9">
                                <select class="form-control" name="endpoint" id="select-endpoint">
                                @foreach( $endpoints['names'] as $id => $name )
                                    <option value="{{ $id }}"{{ ($id == $endpoints['current']['id']) ?
                                        ' selected' : '' }}>{{ $name }}</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input class='btn btn-info' id='btn-change-endpoint' value='Save' />
                            </div>
                        </div>
                    </form>
                </div>

                <!-- <h3>Change Password</h3>
                <form method='POST' id="change-password-form">
                    Old Password: <input class="form-control password-box" type='password' name='old_password' />
                    New Password: <input class="form-control password-box" type='password' name='new_password' />
                    Confirm Password: <input class="form-control password-box" type='password' name='new_password_confirmation' />
                    <input type="hidden" name='_token' value='{{csrf_token()}}' />
                    <input type='button' class='btn btn-success change-password-btn' value="Change" />
                </form> -->
