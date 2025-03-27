$(document).ready(function () {
    'use strict';
    $(document).on('change', '#date-picker', function () {
        let date = $(this).val();
        let capacity = $('#qtyInput').val();
        let restaurant = $('#restaurant_id').val();
        $.ajax({
            type: 'POST',
            url: reservationUrl,
            data: {date: date, capacity: capacity, restaurant: restaurant},
            success: function (response) {
                $("#showTimeSlot").html(response);
                $(".time-slot").each(function () {
                    let timeSlot = $(this);
                    $(this).find('input').on('change', function () {
                        let timeSlotVal = timeSlot.find('strong').text();
                        let timeSlotValID = timeSlot.find('p').text();
                        $('#TimeSlotId').val(timeSlotValID);
                        $("input:radio").removeAttr("checked");
                        $('#time-slot-' + timeSlotValID).prop("checked", true);
                        $('.panel-dropdown.time-slots-dropdown a').html(timeSlotVal);
                        $('.panel-dropdown').removeClass('active');
                    });
                });
            }
        });
    });

    $(document).on("click", '.qtyDec, .qtyInc', function () {
        'use strict';
        let date = $('#date-picker').val();
        let capacity = $('#qtyInput').val();
        let restaurant = $('#restaurant_id').val();

        $.ajax({
            type: 'POST',
            url: reservationUrl,
            data: {date: date, capacity: capacity, restaurant: restaurant},
            success: function (response) {
                $("#showTimeSlot").html(response);
                $(".time-slot").each(function () {
                    let timeSlot = $(this);
                    $(this).find('input').on('change', function () {
                        let timeSlotVal = timeSlot.find('strong').text();
                        let timeSlotValID = timeSlot.find('p').text();
                        $('#TimeSlotId').val(timeSlotValID);
                        $("input:radio").removeAttr("checked");
                        $('#time-slot-' + timeSlotValID).prop("checked", true);
                        $('.panel-dropdown.time-slots-dropdown a').html(timeSlotVal);
                        $('.panel-dropdown').removeClass('active');
                    });
                });
            }
        });
    });
});

$(function () {
    'use strict';
    $('#date-picker').daterangepicker({
        "opens": "left",
        singleDatePicker: true,
        minDate: new Date(),
        locale: {
            format: 'DD-MMM-YYYY'
        }
    });
});

$('#date-picker').on('showCalendar.daterangepicker', function (ev, picker) {
    $('.daterangepicker').addClass('calendar-animated');
});
$('#date-picker').on('show.daterangepicker', function (ev, picker) {
    $('.daterangepicker').addClass('calendar-visible');
    $('.daterangepicker').removeClass('calendar-hidden');
});
$('#date-picker').on('hide.daterangepicker', function (ev, picker) {
    $('.daterangepicker').removeClass('calendar-visible');
    $('.daterangepicker').addClass('calendar-hidden');
});
