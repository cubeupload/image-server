<html>

<body>
	<table>
		<tr>
			<td>Time</td>
			<td>IP</td>
			<td>Referrer</td>
		</tr>
		@foreach( $views as $view )
		<tr>
			<td>{{ Carbon\Carbon::createFromTimestamp($view->at)->diffForHumans() }}</td>
			<td>{{ $view->ip }}</td>
			<td><a _target="blank" href="{{ $view->from }}">{{ $view->from }}</a></td>
		</tr>
		@endforeach
	</table>
</body>

</html>