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

    });

</script>

</html>
