<p>
	The job for user <strong>{{ $rackbeat_company_name }} ({{ $rackbeat_user_account_id }})</strong> - (Connection:
	<strong>{{ $connection_id }}, {{ $plugin_name }}</strong>) has failed.

	<br>
	It has failed with error: <strong>{{ $error_message }}</strong>.

	@if($file !== null)
		<br>
		File: {{ $exception->getFile() }}
	@endif

	@if($line !== null)
		<br>
		Line: {{ $exception->getLine() }}
	@endif

	<br>
	Job ID: <strong>{{ $job_id }}</strong>

	<br>
	Failed at: <strong>{{ $failed_at }}</strong>
</p>