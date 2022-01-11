<!DOCTYPE html>
<html>

<head>
    <title>Payments</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.1.0/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>



    <div class="container mt-5" id="reload">
        {{-- <div id="pop_div"></div>
            @columnchart('PaymentChart', 'pop_div')
        <br> --}}

        <div id="pop1_div"></div>
            @columnchart('CombinedChart', 'pop1_div')

        <h2 class="mb-4">Customer's Payment</h2>
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-6">
                <h3 class="box-title">Recent Sales</h3>
            </div>

            <div class="col-md-6 col-sm-6 col-xs-6 pull-right">
                <div id="reportrange" style="cursor: pointer; padding: 5px 10px; solid #ccc; width: 100%">
                    <i class="fa fa-calendar bg-primary text-white"></i>&nbsp;
                    <span></span>
                    <i class="fa fa-caret-down bg-light text-primary"></i>
                </div>
            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="yajra-datatable">
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
    </div>

</body>

<!-- -->
<!-- jquery-->
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
{{-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<!--datatable javascript -->
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<!--boostrap core javascript -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<!-- boostrap datatable javascript -->
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- moments-->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!--date picker-->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- -->
<script src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.print.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        lava.ready(function() {
            setInterval(function(){
                $.getJSON('/payment/list', function (dataTableJson) {
                    lava.loadData('CombinedChart', dataTableJson);
                });
            }, 5000);
        });

        //below code is to filter datatable data with daterange picker library
        var today = moment().format('YYYY-MM-DD');
        var start = moment().startOf('month');
        var end = moment().endOf('month');

        $(".date").text("("+start.format('MMMM, YYYY')  + " )");
        $('#reportrange').val(start.format('YYYY-MM-DD') + " : " + end.format('YYYY-MM-DD'));
        $('#reportrange').attr("data-from", start.format('YYYY-MM-DD'));
        $('#reportrange').attr('data-to', end.format('YYYY-MM-DD'));

        var cb = function(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        };

        cb(start, end);
        var optionSet = {
            startDate: start,
            endDate: end,
            opens: 'right',
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        };


        $('#reportrange').daterangepicker(optionSet, cb);

        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            $('#reportrange').attr("data-from", picker.startDate.format('YYYY-MM-DD'));
            $('#reportrange').attr('data-to', picker.endDate.format('YYYY-MM-DD'));
            $('#reportrange').val( picker.startDate.format('YYYY-MM-DD') + " : " + picker.endDate.format('YYYY-MM-DD'));
            $('#yajra-datatable').DataTable().draw();

            table.ajax.reload(null, false);
        });

        var table = $('#yajra-datatable').DataTable({
            processing: false,
            serverSide: true,
            orderable: true,
            searchable: true,
            "pageLength": 25,
            dom: 'Blfrtip',
            buttons: ['excel', 'pdf', 'print'],
            "paging": true,
            // ajax: "{{ route('payments.list') }}",

            "ajax": {
                "url": "{{ route('payments.transactions') }}",
                "data": function(d) {
                    d.start_date = $("#reportrange").attr("data-from");
                    d.end_date = $("#reportrange").attr("data-to");
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'reference', name: 'reference'},
                {data: 'amount', name: 'amount'},
                {data: 'customerID', name: 'customerID'},
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row){
                        if (data == 'success'){
                            return '<strong style="color:green;">'+"Success"+'</strong>';
                        }
                    }
                },
                {
                    data: 'paymentGateway',
                    name: 'paymentGateway',
                    render: function(data, type, row){
                        if (data == 'Flutterwave'){
                            return '<strong style="color:blue;">'+data+'</strong>';
                        }

                        else{
                            return '<strong style="color:red;">'+data+'</strong>';
                        }
                    }
                },
                {data: 'transactionDate', name: 'transactionDate'},
            ]
        })

        setInterval( function () {
            table.ajax.reload( null, false ); // user paging is not reset on reload
        }, 5000 );

    });

</script>

</html>
