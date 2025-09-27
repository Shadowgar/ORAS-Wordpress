jQuery(function($){
    $( document ).on( 'click', '#ep_booking_export', function() {
        $('.ep-spinner').addClass('ep-is-active');
        let dates = $('#ep-reports-datepicker').val();
        let event_id = $('#ep_event_id').val();
        if( dates ) {
            let data = { 
                action      : 'ep_download_report_bookings',
                ep_filter_date : dates,
                event_id    : event_id,
                ep_report_action_type : 'export'
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    var blob=new Blob([response]);
                    var link=document.createElement('a');
                    link.href=window.URL.createObjectURL(blob);
                    link.download="bookings.csv";
                    link.click();
                    $('.ep-spinner').removeClass('ep-is-active');
                }
            });
        }
    });
    $( document ).on( 'click', '#ep_payment_filter', function() {
        let dates = $('#ep-reports-datepicker').val();
        let event_id = $('#ep_event_id').val();
        let payment = $('#ep_payment_method').val();
        let status = $('#ep_booking_status').val();
        if( dates ) {
            let data = { 
                action      :       'ep_eventprime_payment_reports_filter',
                ep_filter_date :    dates,
                event_id    :       event_id,
                payment_method     :payment,
                status      :       status
                
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    $('#ep_payment_stat_container').html(response.data.stat_html);
                    $('.ep-report-booking-list').html(response.data.booking_html);
                    if(response.data.chart.length){
                        drawPaymentsChart(response.data.chart);
                    }
                }
            });
        }
    });
    $( document ).on( 'click', '#ep-loadmore-report-payments', function() {
        $('.ep-spinner').addClass('ep-is-active');
        let dates = $('#ep-reports-datepicker').val();
        let event_id = $('#ep_event_id').val();
        let payment = $('#ep_payment_method').val();
        let status = $('#ep_booking_status').val();
        let paged = $('#ep-report-payment-paged').val();
        var max_page = $( this ).attr('data-max');
        if( dates ) {
            let data = { 
                action      : 'ep_eventprime_payment_reports_filter',
                ep_filter_date : dates,
                event_id    : event_id,
                paged       : paged,
                payment_method     :payment,
                status      :       status,
                ep_report_action_type : 'load_more'
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    $('.ep-spinner').removeClass('ep-is-active');
                    $('.ep-report-booking-list table tbody').append(response.data.booking_html);
                    let new_page = parseInt(paged, 10) + parseInt(1, 10);
                    $('#ep-report-payment-paged').val(new_page);
                    if(new_page >= parseInt(max_page,10)){
                        $('.ep-reports-boooking-load-more').hide();
                    }
                }
            });
        }
    });
    $( document ).on( 'click', '#ep_payments_export', function() {
        $('.ep-spinner').addClass('ep-is-active');
        let dates = $('#ep-reports-datepicker').val();
        let event_id = $('#ep_event_id').val();
        let payment = $('#ep_payment_method').val();
        let status = $('#ep_booking_status').val();
        if( dates ) {
            let data = { 
                action      : 'ep_download_report_payments',
                ep_filter_date : dates,
                event_id    : event_id,
                payment_method     :payment,
                status      :       status,
                ep_report_action_type : 'export'
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    var blob=new Blob([response]);
                    var link=document.createElement('a');
                    link.href=window.URL.createObjectURL(blob);
                    link.download="payments.csv";
                    link.click();
                    $('.ep-spinner').removeClass('ep-is-active');
                }
            });
        }
    });
    $( document ).on( 'click', '#ep_attendee_filter', function() {
        let event_id = $('#ep_event_id').val();
        let data = { 
                action      :       'ep_eventprime_attendee_reports_filter',
                event_id    :       event_id
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    $('#ep_attendee_stat_container').html(response.data.stat_html);
                    $('.ep-report-booking-list').html(response.data.booking_html);
                    if(response.data.chart){
                        drawAttendeesChart(response.data.chart);
                    }
                }
            });
        
    });
    
    $( document ).on( 'click', '#ep-loadmore-report-attendees', function() {
        $('.ep-spinner').addClass('ep-is-active');
        let event_id = $('#ep_event_id').val();
        let paged = $('#ep-report-attendee-paged').val();
        var max_page = $( this ).attr('data-max');
        
            let data = { 
                action      : 'ep_eventprime_attendee_reports_filter',
                event_id    : event_id,
                paged       : paged,
                ep_report_action_type : 'load_more'
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    $('.ep-spinner').removeClass('ep-is-active');
                    $('.ep-report-booking-list table tbody').append(response.data.booking_html);
                    let new_page = parseInt(paged, 10) + parseInt(1, 10);
                    $('#ep-report-attendee-paged').val(new_page);
                    if(new_page >= parseInt(max_page,10)){
                        $('.ep-reports-boooking-load-more').hide();
                    }
                }
            });
            
    });
    $( document ).on( 'click', '#ep_attendees_export', function() {
        $('.ep-spinner').addClass('ep-is-active');
        let event_id = $('#ep_event_id').val();
        
            let data = { 
                action      : 'ep_download_report_attendee',
                event_id    : event_id,
                ep_report_action_type : 'export'
            };
            $.ajax({
                type        : "POST",
                url         : ajaxurl,
                data        : data,
                success     : function( response ) {
                    var blob=new Blob([response]);
                    var link=document.createElement('a');
                    link.href=window.URL.createObjectURL(blob);
                    link.download="attendees.csv";
                    link.click();
                    $('.ep-spinner').removeClass('ep-is-active');
                }
            });
        
    });
});

function drawPaymentsChart(arrData) {
    var data = new google.visualization.DataTable();
    data.addColumn('date',   'Time of Day');
    data.addColumn('number', 'Revenue');
  
    const arrDataMap = arrData.map((val, key) => {
        return [new Date(val.date), val.revenue];
    });
    data.addRows(arrDataMap);

    var options = {
        title: 'Revenue as per date',
        'height': 500,
        colors: ['#2271b1', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6'],
        legend: {
            position: 'bottom'
        },
        hAxis: {
            format: 'MMM d, yyyy',
            baselineColor: 'transparent',
            gridlines: {
                color: 'transparent',
                count: 25
            },
            title: 'Date'
        },
        vAxis: {
            baselineColor: '#DDD',
            gridlines: {
                color: '#DDD'
            },
            minValue: 0,
            title: 'Revenue'
        }
  };
  
    var chart = new google.visualization.LineChart(document.getElementById('ep_bookings_chart'));
    chart.draw(data, options);
}

function drawAttendeesChart(arrData) {
    var data = new google.visualization.DataTable();
    data.addColumn('date',   'Time of Day');
    data.addColumn('number', 'Booking');
    data.addColumn('number', 'Attendees');
  
    const arrDataMap = arrData.map((val, key) => {
        return [new Date(val.date), val.booking, val.attendees];
    });
    data.addRows(arrDataMap);

    var options = {
        title: 'Booking and Atteendes as per date',
        'height': 500,
        colors: ['#2271b1', '#78c3ff', '#ec8f6e', '#f3b49f', '#f6c7b6'],
        legend: {
            position: 'bottom'
        },
        hAxis: {
            format: 'MMM d, yyyy',
            baselineColor: 'transparent',
            gridlines: {
                color: 'transparent',
                count: 25
            },
            title: 'Date'
        },
        
        /* vAxis: {
            baselineColor: '#DDD',
            gridlines: {
                color: '#DDD'
            },
            minValue: 0,
            title: 'Bookings'
        }, */

        series: {
            0: {targetAxisIndex: 0},
            1: {targetAxisIndex: 1},
        },

        vAxes: {
            0: {
                textPosition: 'out',
                title: 'Bookings'
            },
            1: {
                textPosition: 'out',
                title: 'Attendees',
            },
        },
    };
  
    var chart = new google.visualization.LineChart(document.getElementById('ep_attendees_chart'));
  
    chart.draw(data, options);
}
