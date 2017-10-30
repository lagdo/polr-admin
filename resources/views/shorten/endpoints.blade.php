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
                            <input class='btn btn-info' id='btn-change-endpoint' value='Change' />
                        </div>
                    </div>
                </form>
