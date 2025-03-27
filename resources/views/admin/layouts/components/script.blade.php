<!-- General JS Scripts -->
<script src="{{ asset('assets/modules/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/modules/popper.js/dist/popper.min.js') }}"></script>
<script src="{{ asset('assets/modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/modules/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
<script src="{{ asset('assets/modules/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/dropzone.min.js') }}"></script>
<script src="{{ asset('assets/js/stisla.js') }}"></script>

<!-- JS Libraries -->
<script src="{{ asset('assets/modules/izitoast/dist/js/iziToast.min.js') }}"></script>
@yield('scripts')

<!-- Template JS File -->
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    @if(session('success'))
    iziToast.success({
        title: 'Success',
        message: '{{ session('success') }}',
        position: 'topRight'
    });
    @endif

    @if(session('error'))
    iziToast.error({
        title: 'Error',
        message: '{{ session('error') }}',
        position: 'topRight'
    });
    @endif



</script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<script>
    function showMessageNotification(message){
        if(message.status){
            iziToast.success({
                title: 'Transaction Result',
                message: message.message,
                position: 'topRight'
            });
        } else {
            iziToast.info({
                title: 'Transaction Result',
                message: message.message,
                position: 'topRight'
            });
        }
    }

    function updateOrdersList(){
        $('#main-table').DataTable().draw();
    }

    function updateOrderDetailsPage(){
        location.reload();
    }

    function getOrderInfo(message){
        var url = $('#orderInfoUrl').data('url');

        $.ajax({
            url: url,
            data: {
                order_id: message.order_id,
            },
            success: function(data){
                if(data === false) return;
                showMessageNotification(message);
                @if(isset($ordersListPage) && $ordersListPage == true)
                updateOrdersList();
                @elseif(isset($orderId) && $orderId > 0)
                    if(message.order_id == "{{$orderId}}"){
                        updateOrderDetailsPage();
                    }
                @endif
            },
            error: function(_, __, ___){

            }
        });
    }

    var pusher = new Pusher("{{env('MIX_PUSHER_APP_KEY')}}", {
        cluster: 'ap2',
        encrypted: true
    });

    // Subscribe to the channel we specified in our Laravel Event
    var userId = "{{ auth()->user()->id }}";
    
    var channel = pusher.subscribe('user-' + userId);

    // var channel = pusher.subscribe('mqtt-connection');

    channel.bind('transaction-response', function(message) {
        getOrderInfo(message);
    });
</script>

<script type="text/javascript">

{{--    $(document).ready(function() {--}}
{{--        const beep = document.getElementById("myAudio1");--}}
{{--        const voice = document.getElementById("myAudio2");--}}

{{--        function sound() {--}}
{{--            beep.play();--}}
{{--            // voice.play();--}}
{{--        }--}}
{{--        // web_token--}}
{{--        const firebaseConfig = {--}}
{{--            apiKey: "AIzaSyB4RuN2lZmdwTsPQYH-0aoiSobr7KTct4c",--}}
{{--            authDomain: "ghusn-al-zayton.firebaseapp.com",--}}
{{--            projectId: "ghusn-al-zayton",--}}
{{--            storageBucket: "ghusn-al-zayton.appspot.com",--}}
{{--            messagingSenderId: "398551411866",--}}
{{--            appId: "1:398551411866:web:3d4591cb309c098bbdc323",--}}
{{--            measurementId: "G-WM762CHPVX"--}}
{{--        };--}}
{{--        console.log('out');--}}
{{--        firebase.initializeApp(firebaseConfig);--}}
{{--        const messaging = firebase.messaging();--}}
{{--        @if(!blank(auth()->user()))--}}
{{--        startFCM();--}}
{{--        @endif--}}
{{--console.log(messaging.requestPermission())--}}
{{--        function startFCM() {--}}
{{--            messaging.requestPermission()--}}
{{--                .then(function() {--}}
{{--                    return messaging.getToken()--}}
{{--                })--}}
{{--                .then(function(response) {--}}
{{--                    console.log(response);--}}
{{--                    $.ajax({--}}
{{--                        url: '{{ route("store.token") }}',--}}
{{--                        type: 'POST',--}}
{{--                        data: {--}}
{{--                            token: response--}}
{{--                        },--}}
{{--                        dataType: 'JSON',--}}
{{--                        success: function(response) {--}}

{{--                        },--}}
{{--                        error: function(error) {--}}
{{--                            //--}}
{{--                        },--}}
{{--                    });--}}
{{--                }).catch(function(error) {--}}
{{--                //--}}
{{--            });--}}
{{--        }--}}
{{--        messaging.onMessage(function({data:{body,title}}){--}}
{{--            console.log("inside");--}}
{{--            sound();--}}
{{--            $('#custom-width-modal').modal('show');--}}
{{--            $('#notificationTitle').text(title);--}}
{{--            $('#notificationBody').text(body);--}}
{{--            new Notification(title, {body});--}}
{{--        });--}}

{{--    });--}}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>

<script>
    // Detect user's time zone
    var userTimezone = moment.tz.guess();

    // Store the time zone in a cookie
    Cookies.set('userTimezone', userTimezone, { expires: 7, path: '/' });
</script>
