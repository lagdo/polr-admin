<div class="row">
    <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked admin-nav" role="tablist">
@foreach( $tabs as $id => $tab )
            <li role="presentation" class="admin-nav-item {{ $tab->active ? 'active' : '' }} {{ $tab->class }}">
                <a href="#{{ $id }}">{{ $tab->title }}</a>
            </li>
@endforeach
        </ul>
    </div>
    <div class="col-md-10">
        <div class="tab-content">
            @foreach( $tabs as $id => $tab )
            <div role="tabpanel" class="tab-pane {{ $tab->active ? 'active' : '' }} {{ $tab->class }}" id="{{ $id }}">
            	{!! $tab->view !!}
            </div>
            @endforeach
        </div>
    </div>
</div>
