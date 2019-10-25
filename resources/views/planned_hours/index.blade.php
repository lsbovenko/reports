@extends('layouts.app')

@section('title', trans('reports.planned_hours'))

@section('content')
    <div class="container">
        <div class="row page-header">
            <div class="col-sm-8">
                <h1>{{ trans('reports.planned_hours') }}</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (Session::has('alert-success'))
                    <div class="alert alert-success alert-dismissable">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        {{ Session::get('alert-success') }}
                    </div>
                @endif
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{ trans('reports.year') }}</th>
                        <th>{{ trans('reports.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($years as $year)
                        <tr>
                            <td>{{ $year }}</td>
                            <td>
                                <a href="{{ route('planned-hours.edit', ['year' => $year]) }}">{{ trans('reports.edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
    </div>
@endsection