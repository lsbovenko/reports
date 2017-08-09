@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            @include('partials.errors')
            <div class="alert alert-success alert-dismissable">
                Спасибо. Идея успешно добавлена.
            </div>
            <p>Вы будете перенаправлены на главную страницу через <span id="time">5</span> ...</p>
        </div>
    </div>
    <hr>
</div>
@endsection
