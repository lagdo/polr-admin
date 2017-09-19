                <p id="edit-user-api-active">
                    <span>API Active</span>:
                    <code class='status-display'>{{ $user->api_active ? 'True' : 'False' }}</code>
                    <a class='btn btn-xs btn-success'>toggle</a>
                </p>

                <p id="edit-user-api-key">
                    <span>API Key: </span>
                    <code class='status-display'>{{ $user->api_key }}</code>
                    <a class='btn btn-xs btn-danger'>reset</a>
                </p>

                <p id="edit-user-api-quota">
                    <span>API Quota (req/min, -1 for unlimited):</span>
                    <input type='number' class='form-control api-quota' value="{{ $user->api_quota }}" />
                    <a class='btn btn-xs btn-warning'>change</a>
                </p>
