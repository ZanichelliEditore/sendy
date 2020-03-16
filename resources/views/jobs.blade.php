@extends('app')

@section('body')
<div class="mx-5">
    <div class="container-fluid">
        <handle-jobs url="/api/jobs"></handle-jobs>
    </div>
</div>
@endsection
