<div class="row" id="polr-dashboard-container">
	<div class="col-md-12">
		<div class="portlet light">
			<div class="portlet-title tabbable-line">
				<div class="col-md-3">
					<select class="form-control" name="server" id="select-server">
@foreach( $servers as $id => $server )
						<option value="{{ $id }}"{{ ($id == $selected) ? ' selected' : '' }}>{{ $server['name'] }}</option>
@endforeach
					</select>
				</div>
				<div class="col-md-1">
					<button type="button" class="btn btn-info" id="btn-change-server">Change</button>
				</div>
				<ul class="nav nav-tabs admin-nav">
@foreach( $tabs as $id => $tab )
					<li class="{{ $tab['active'] ? 'active' : '' }} {{ $tab['class'] }}">
						<a href="#{{ $id }}" data-toggle="tab">{{ $tab['title'] }}</a>
					</li>
@endforeach
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
@foreach( $tabs as $id => $tab )
					<div class="tab-pane {{ $tab['active'] ? 'active' : '' }} {{ $tab['class'] }}" id="{{ $id }}">
@include('polr_admin::tabs.' . $id, ['server' => $servers[$selected]])
					</div>
@endforeach
				</div>
			</div>
		</div>
	</div>
</div>
