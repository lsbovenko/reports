@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row page-header">
            <div class="col-sm-8">
                <h1>{{ trans('reports.edit_planned_hours', ['year' => $year]) }}</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                @include('partials.errors')
                <form method="POST" action="{{ route('planned-hours.update', ['year' => $year]) }}">
                    @include('planned_hours.fields')
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary pull-right">{{ trans('reports.save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>
    </div>
@endsection