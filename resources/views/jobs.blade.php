@extends('app')

@section('body')
<div class="mx-5">
    <div class="mt-10 container-fluid">
        <div class="m-5">
            <button type="button" class="btn btn-outline-primary">Pulisci LOG</button>
            <button type="button" class="btn btn-outline-primary">Refresh Access Token</button>
        </div>
        <div class="mt-5">
            <ul class="list-group">
                    @foreach($contentFile as $file)
                    <li class="list-group-item list-group-item-action">
                        {{!! $file !!}}
                    </li>
                    @endforeach
                </ul>
        </div>
    </div>
</div>
@endsection
