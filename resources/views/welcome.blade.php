<!DOCTYPE html>
<html>

<head>
    <title>Payments</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body>



    <div class="container mt-5" id="reload">
        {{-- <div id="pop_div"></div>
            @columnchart('PaymentChart', 'pop_div')
        <br> --}}

        <div id="pop1_div"></div>
            @columnchart('CombinedChart', 'pop1_div')

        <h2 class="mb-4">Customer's Payment</h2>
        <table class="table table-bordered" id="yajra-datatable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Reference</th>
                    <th>Amount(₦)</th>
                    <th>Customer ID</th>
                    <th>Status</th>
                    <th>Payment Gateway</th>
                    <th>TransactionDate</th>
                    {{-- <th>Action</th> --}}
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#yajra-datatable').DataTable({
            processing: false,
            serverSide: true,
            orderable: true,
            searchable: true,
            ajax: "{{ route('payments.list') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'reference', name: 'reference'},
                {data: 'amount', name: 'amount'},
                {data: 'customerID', name: 'customerID'},
                {data: 'status', name: 'status'},
                {data: 'paymentGateway', name: 'paymentGateway'},
                {data: 'transactionDate', name: 'transactionDate'},
                // {
                //     data: 'action',
                //     name: 'action',
                //     orderable: true,
                //     searchable: true
                // },
            ]
        })

        setInterval( function () {
            table.ajax.reload( null, false ); // user paging is not reset on reload
        }, 5000 );


        ////below code is to filter datatable data with daterange picker library
        // var today = moment().format('YYYY-MM-DD');
        //     var start = moment().startOf('month');
        //     var end = moment().endOf('month');
        //      $(".date").text("( "+start.format('MMMM, YYYY')  + " )");
        //     $('#reportrange').val(start.format('YYYY-MM-DD') + " : " + end.format('YYYY-MM-DD'));
        //     $('#reportrange').attr("data-from", start.format('YYYY-MM-DD'));
        //     $('#reportrange').attr('data-to', end.format('YYYY-MM-DD'));
        //     var cb = function(start, end) {
        //         $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        //     };

        //     cb(start, end);
        //     var optionSet = {
        //         startDate: start,
        //         endDate: end,
        //         opens: 'right',
        //         ranges: {
        //             'Today': [moment(), moment()],
        //             'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        //             'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        //             'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        //             'This Month': [moment().startOf('month'), moment().endOf('month')],
        //             'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
        //                 .endOf('month')
        //             ]
        //         }
        //     };


        //     $('#reportrange').daterangepicker(optionSet, cb);

        //     $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        //         $('#reportrange').attr("data-from", picker.startDate.format('YYYY-MM-DD'));
        //         $('#reportrange').attr('data-to', picker.endDate.format('YYYY-MM-DD'));
        //          $('#reportrange').val( picker.startDate.format('YYYY-MM-DD') + " : " + picker.endDate.format('YYYY-MM-DD'));
        //         $('#paymenthistory').DataTable().draw(true);
        //         tablehistory.ajax.reload(null, false);
        //     });
           


        //     var tablehistory = $("##yajra-datatable").DataTable({
        //         processing: true,
        //         serverSide: true,
        //         "ordering": false,
        //         "ajax": {
        //             "url": "{{ route('payments.transactions') }}",
        //             "data": function(d) {
        //                 d.start_date = $("#reportrange").attr("data-from");
        //                 d.end_date = $("#reportrange").attr("data-to");
        //             }
        //         },
        //         "pageLength": 50,
        //         dom: 'Blfrtip',
        //         buttons: [
        //             'excel', 'pdf', 'print'
        //         ],

        //         columns: [
                    // {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                    // {data: 'reference', name: 'reference'},
                    // {data: 'amount', name: 'amount'},
                    // {data: 'customerID', name: 'customerID'},
                    // {data: 'status', name: 'status'},
                    // {data: 'paymentGateway', name: 'paymentGateway'},
                    // {data: 'transactionDate', name: 'transactionDate'},
        //         ]
        //     });

        // });

    });

</script>

</html>
