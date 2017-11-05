                <h3>Polr endpoints</h3>

                <div class="">
                    <h4>Change endpoint</h4>
                    <form role='form'>
                        <div class="row">
                            <div class="col-md-9">
                                <select class="form-control" name="endpoint" id="select-endpoint">
                                @foreach( $endpoints as $id => $name )
                                    <option value="{{ $id }}"{{ ($id == $endpoint->id) ?
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
