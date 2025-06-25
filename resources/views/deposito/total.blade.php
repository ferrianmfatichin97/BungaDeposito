@extends('layouts.app')

@section('title', 'List Nasabah Deposito')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Total Nasabah Deposito</h1>

    <div class="card">
        <div class="card-body">
            @include('deposito.tabel')
        </div>
    </div>
</div>
</div>
@endsection
