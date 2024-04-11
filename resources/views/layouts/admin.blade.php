<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin._partials._head')
</head>

<body>
    @if(session('success'))
    <script>
        displaySuccessAlert('{{ session('success') }}');
    </script>
    @endif

    @if(session('error'))
    <script>
        displayErrorAlert('{{ session('error') }}');
    </script>
    @endif


    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <div id="main-wrapper">
        @include('admin._partials._navHeader')
        @include('admin._partials._header')
        @include('admin._partials._sidebar')

        <div class="content-body">
            <div class="container-fluid">
                @yield('body')
            </div>
        </div>
    </div>

    @include('admin._partials._footer')
    @include('admin._partials._script')
</body>

</html>
