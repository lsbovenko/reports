@extends('layouts.email')

@section('content')
<p>{{ trans('reports.hello') }}, {{$user->name}}!</p>
<p>{{ trans('reports.forgot_send_report') }} <b>{{$date->format('Y-m-d')}}</b></p>
<p>{{ trans('reports.create_report_follow_link') }} <a href="{{url('/')}}">{{url('/')}}</a></p>
@endsection
