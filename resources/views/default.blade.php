<div class="row">
	<div class="col-md-12">
		<div class="portlet light">
			<div class="portlet-title tabbable-line">
				<ul class="nav nav-tabs admin-nav">
@foreach( $tabs as $id => $tab )
					<li class="{{ $tab->active ? 'active' : '' }} {{ $tab->class }}">
						<a href="#{{ $id }}" data-toggle="tab">{{ $tab->title }}</a>
					</li>
@endforeach
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
@foreach( $tabs as $id => $tab )
					<div class="tab-pane {{ $tab->active ? 'active' : '' }} {{ $tab->class }}" id="{{ $id }}">
						{!! $tab->view !!}
					</div>
@endforeach
				</div>
			</div>
		</div>
	</div>
</div>
