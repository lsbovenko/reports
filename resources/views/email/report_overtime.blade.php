@extends('layouts.email')

@section('content')
<table>
    <thead>
        <tr>
            <th>{{ trans('reports.full_name') }}</th>
            <th>{{ trans('reports.overtime') }}</th>
            <th>{{ trans('reports.details') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $name => $item)
            <tr>
                <td>{{$name}}</td>
                <td>{{$item['time']}}</td>
                <td>
                    <ul>
                    @foreach ($item['details'] as $date => $time)
                        <li>
                            {{$date}} - {{$time}}
                        </li>
                    @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
