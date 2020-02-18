<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Ikantam</title>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css?v=' . Config::get('app.version')) }}" rel="stylesheet">
    @yield('page_css')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', '') }}
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            {!! Menu::main() !!}
            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    <li><a href="{{ config('app.auth_url') }}">Login</a></li>
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{ config('app.auth_url') }}/profile">{{ trans('reports.profile') }}</a>
                            </li>
                            <li>
                                <a href="{{ config('app.auth_url') }}/logout">{{ trans('reports.logout') }}</a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->

<div id="overlay">
    <div id="loading-bar-spinner" class="spinner"><div class="spinner-icon"></div></div>
</div>
<div class="container" id="content">

    @yield('content')

    <!-- Footer -->
    <footer>
        <div class="row">
            <div class="col-lg-12">

            </div>
        </div>
    </footer>
</div>
<!-- /.container -->

@if (isset($js))
    <script>window._globals = {!! json_encode($js) !!}</script>
@endif

<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>

@yield('page_js')

</body>
</html>