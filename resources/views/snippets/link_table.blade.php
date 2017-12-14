<table id="{{$table_id}}" class="table table-hover">
    <thead>
        <tr>
            {{-- Show action buttons only if admin view --}}
            @if ($table_id == "admin_links_table")
            <th class="col-sm-1">Ending</th>
            <th class="col-sm-4">Long Link</th>
            <th class="col-sm-1">Clicks</th>
            <th class="col-sm-2">Date</th>
            <th class="col-sm-2">Creator</th>
            <th class="col-sm-1">Disable</th>
            <th class="col-sm-1">Delete</th>
            @else
            <th class="col-sm-2">Ending</th>
            <th class="col-sm-5">Long Link</th>
            <th class="col-sm-2">Clicks</th>
            <th class="col-sm-3">Date</th>
            @endif
        </tr>
    </thead>
</table>
