@extends('app')

@section('body')
<div class="mx-5">
    <div class="container-fluid">
        <zani-table title="Failed Jobs" url="/api/failedJobs/" buttons="true" :columns="[
        {
            'label' : 'id', 'field' : 'id', 'orderby' : 'id', 'type' : 'text', 'dimension' : 'small'
        },
        {
            'label' : 'payload', 'field' : 'payload', 'orderby' : 'payload', 'type' : 'text', 'dimension' : 'large'
        },
        {
            'label' : 'exception', 'field' : 'exception', 'orderby' : 'exception', 'type' : 'text', 'dimension' : 'large'
        },
        {
            'label' : 'failed_at', 'field' : 'failed_at', 'orderby' : 'failed_at', 'type' : 'text', 'dimension' : 'medium'
        },
        {
            'type' : 'buttonRetry', 'dimension' : 'small'
        },
        {
            'type' : 'buttonDelete', 'dimension' : 'small'
        },
        ]" />
    </div>
</div>
@endsection
