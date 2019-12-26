                        <p>
                            <b>Short Link: </b>
                            <a target="_blank" href="{{ $server->url }}/{{ $link->short_url }}">
                                {{ $server->url }}/{{ $link->short_url }}
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
