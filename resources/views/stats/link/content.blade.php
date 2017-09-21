                <div class="row bottom-padding">
                    <div class="col-md-8">
                        <h4>Traffic over Time</h4> (total: {{ $link->clicks }})
                        <canvas id="dayChart"></canvas>
                    </div>
                    <div class="col-md-4">
                        <h4>Traffic sources</h4>
                        <canvas id="refererChart"></canvas>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h4>Map</h4>
                        <div id="mapChart"></div>
                    </div>
                    <div class="col-md-6">
                        <h4>Referers</h4>
                        <table class="table table-hover" id="refererTable">
                            <thead>
                                <tr>
                                    <th>Host</th>
                                    <th>Clicks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referer_stats as $referer)
                                    <tr>
                                        <td>{{ $referer->label }}</td>
                                        <td>{{ $referer->clicks }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
            
                    </div>
                </div>
