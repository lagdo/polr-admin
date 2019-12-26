                <h4>Welcome to your {{ $server['name'] }} dashboard!</h4>
                <p>Use the tabs to navigate your {{ $server['name'] }} dashboard.</p>

                <h4>Create a short URL</h4>
                <div class="" style="text-align: center;">
                    <form method="POST" id="shorten-form" role="form">
                        <input type="url" autocomplete="off" class="form-control long-link-input" placeholder="http://" name="url" />

                        <div class="row" id="options">
                            <p>Customize link</p>

                            <div class="btn-group btn-toggle visibility-toggler" data-toggle="buttons">
                                <label class="btn btn-primary btn-sm active">
                                    <input type="radio" name="options" value="p" checked /> Public
                                </label>
                                <label class="btn btn-sm btn-default">
                                    <input type="radio" name="options" value="s" /> Secret
                                </label>
                            </div>

                            <div>
                                <div class="custom-link-text">
                                    <h2 class="site-url-field">{{ $server['url'] }}/</h2>
                                    <input type="text" autocomplete="off" class="form-control custom-url-field" name="ending" />
                                </div>
                                <div>
                                    <button type="button" class="btn btn-success btn-xs check-btn" id="check-link-availability">Check Availability</button>
                                    <div id="link-availability-status"></div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-info" id="shorten-btn">Shorten</button>
                        <button type="button" class="btn btn-warning" id="show-link-options">Link Options</button>
                    </form>

                    <div id="tips" class="text-muted tips">
                        <i class="fa fa-spinner"></i> Loading Tips...
                    </div>
                </div>
