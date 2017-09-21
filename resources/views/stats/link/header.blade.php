                <div class="row">
                    <div class="col-md-6 link-meta">
                        <p>
                            <b>Short Link: </b>
                            <a target="_blank" href="{{ env('APP_PROTOCOL') }}/{{ env('APP_ADDRESS') }}/{{ $link->short_url }}">
                                {{ env('APP_ADDRESS') }}/{{ $link->short_url }}
                            </a>
                        </p>
                        <p>
                            <b>Long Link: </b>
                            <a target="_blank" href="{{ $link->long_url }}">{{ str_limit($link->long_url, 50) }}</a>
                        </p>
                        {{-- <p>
                            <em>Tip: Clear the right date bound (bottom box) to set it to the current date and time. New
                            clicks will not show unless the right date bound is set to the current time.</em>
                        </p> --}}
                    </div>
                    <div class="col-md-3">
                        <form id="stats-dates">
                            <div class="form-group">
                                <div class='input-group date' id='left-bound-picker'>
                                    <input type="text" class="form-control" name="left_bound">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class='input-group date' id='right-bound-picker'>
                                    <input type="text" class="form-control" name="right_bound">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
        
                            <input type="button" class="form-control btn-refresh-stats" value="Refresh" class="form-control" />
                        </form>
                    </div>
                </div>
