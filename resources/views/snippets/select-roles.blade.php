                                <select class="form-control new-user-role" name="user_role">
                                    @foreach  ($roles as $role_text => $role_val)
                                        <option value="{{ ($role_val) ?: 'default' }}">{{$role_text}}</option>
                                    @endforeach
                                </select>
