<!DOCTYPE html>
<html class="no-js" lang="">
  <head>
    <meta charset="utf-8" />
    <title>LuxSpace</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <meta property="og:title" content="" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />

    <link rel="apple-touch-icon" href="{{ url('/frontend/images/content/favicon.png') }}" />
    <!-- Place favicon.ico in the root directory -->  
    <link rel="icon" href="{{ url('/frontend/images/content/favicon.png') }}" />
    <meta name="theme-color" content="#000" />
    <link rel="icon" href="{{ url('/frontend/favicon.ico') }}">
    <link href="{{ url('/frontend/css/app.minify.css') }}" rel="stylesheet">
  </head>

  <body>
    <!-- Add your site or application content here -->

    <!-- START: HEADER -->
    @include('component.frontend.navbar')
    <!-- END: HEADER -->

    {{-- content --}}
    @yield('content')

    <!-- START: ASIDE MENU -->
    @include('component.frontend.aside')
    <!-- END: ASIDE MENU -->

    <!-- START: FOOTER -->
    @include('component.frontend.footer')
    <!-- END: FOOTER -->

    @include('component.frontend.script')
  </body>
</html>
