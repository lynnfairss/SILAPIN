@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.min.js"></script>

    <script>
        $(document).ready(function () {

            $(document).pjax(
                'a:not([target="_blank"]):not([data-toggle="modal"]):not([href^="#"])',
                '#pjax-container',
                {
                    timeout: 5000,
                    scrollTo: false,
                    cache: false
                }
            );

            $(document).on('pjax:send', function () {
                $('#pjax-container').addClass('pjax-loading');
            });

            $(document).on('pjax:complete', function () {
                // Beri kesempatan browser render konten baru sebelum fade-in
                requestAnimationFrame(function () {
                    $('#pjax-container').removeClass('pjax-loading');
                });

                // Re-init layout AdminLTE
                if (typeof $.AdminLTE !== 'undefined' && $.AdminLTE.layout) {
                    $.AdminLTE.layout.fix();
                }

                // Perbarui status active di sidebar berdasarkan URL
                var currentPath = window.location.pathname.replace(/\/+$/, '');
                $('.nav-sidebar .nav-item').removeClass('menu-open');
                $('.nav-sidebar .nav-link').removeClass('active');

                $('.nav-sidebar .nav-link').each(function () {
                    var href = $(this).attr('href');
                    if (!href) return;

                    var linkPath = href.replace(/\/+$/, '');
                    if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                        $(this).addClass('active');
                        var parent = $(this).closest('.nav-item');
                        if (parent.hasClass('has-treeview')) {
                            parent.addClass('menu-open');
                        }
                    }
                });

                // Re-init Bootstrap komponen di konten baru
                $('[data-toggle="tooltip"]').tooltip('dispose').tooltip();
                $('[data-toggle="popover"]').popover('dispose').popover();
            });

        });
    </script>
@stop
