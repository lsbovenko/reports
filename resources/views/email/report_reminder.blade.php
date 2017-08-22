@extends('layouts.email')

@section('content')
<p>Здравствуйте, {{$user->name}}!</p>
<p>Возможно вы забыли отправить отчёт за <b>{{$date->format('Y-m-d')}}</b></p>
<p>Для создания отчёта перейдите по ссылке <a href="{{url('/')}}">{{url('/')}}</a></p>
@endsection
