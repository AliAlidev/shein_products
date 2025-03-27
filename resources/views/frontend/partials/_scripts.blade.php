<!-- Scripts
    ================================================== -->
<script type="text/javascript" src="{{ asset('themes/scripts/jquery-3.4.1.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/jquery-migrate-3.1.0.min.js') }}"></script>
<script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/mmenu.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/chosen.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/slick.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/rangeslider.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/magnific-popup.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/waypoints.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/counterup.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/scripts/tooltips.min.js') }}"></script>
<!-- slider -->

<script type="text/javascript" src="{{ asset('themes/library/custom-carousel/js/owl.carousel.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('themes/library/custom-carousel/js/main.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
</script>
<!-- end -->

<script type="text/javascript" src="{{ asset('themes/scripts/custom.js') }}"></script>

<!-- Maps -->

<!-- Style Switcher
    ================================================== -->
<script src="{{ asset('themes/scripts/switcher.js') }}"></script>

<script src="{{ asset('assets/modules/izitoast/dist/js/iziToast.min.js') }}"></script>
<script src="{{ asset('frontend/js/script.js') }}" type="text/javascript"></script>

<!-- custom javascript -->

<script type="text/javascript">
    @if (session('success'))
        iziToast.success({
            title: 'Success',
            message: '{{ session('success') }}',
            position: 'topRight'
        });
    @endif

    @if (session('error'))
        iziToast.error({
            title: 'Error',
            message: '{{ session('error') }}',
            position: 'topRight'
        });
    @endif
</script>

<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>

<script type="text/javascript">
</script>


@yield('extra-js')
@yield('livewire-scripts')
