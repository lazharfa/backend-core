@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Donasi</h1>
@stop

@section('content')

    @if (Session::has('error'))
        <div class="alert alert-danger alert-style" role="alert">
            <strong>Error! </strong> {{ Session::get('error') }}
        </div>
    @endif @if (Session::has('success'))
        <div class="alert alert-success alert-style" role="alert">
            <strong>Berhasil! </strong> {{ Session::get('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <form role="form" method="get" action="{{ url()->current() }}">

                    <div class="box-header with-border">
                        <h3 class="box-title"><strong>Pencarian</strong></h3>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-4">
                                <input type="text" class="form-control" id="str" name="str" placeholder="Nama/Email/Phone Donatur">
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="form-control" id="min_donation" min="0" name="min_donation" placeholder="Donasi Minimum">
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="form-control" id="max_donation" name="max_donation" placeholder="Donasi Maksimum">
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><strong>Donasi Terbaru</strong></h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <div class="box-body">
                    <form action="{{ url()->full() }}" method="post">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-success">Download</button>
                    </form>

                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Nama Donatur</th>
                            <th>Telpon Donatur</th>
                            <th>Email Donatur</th>
                            <th>Bank</th>
                            <th>Total Donasi</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($donations as $donation)
                            <tr>
                                <td>{{ $donation->donor_name }}</td>
                                <td>{{ $donation->donor_phone }}</td>
                                <td>{{ $donation->donor_email }}</td>
                                <td>{{ ($donation->bank) ? $donation->bank->bank_info : '' }}</td>
                                <td>{{ $donation->total_donation }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{--{{ $donations->links() }}--}}
                    {{ $donations->appends(request()->query())->links() }}
                </div>
                <!-- /.box-body -->

            </div>
            <!-- /.box -->
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>
    <!-- bootstrap datepicker -->
    <link rel="stylesheet" href="/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
@stop

@section('js')
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <!-- bootstrap datepicker -->
    <script src="/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <!-- Select2 -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script>
        //Initialize Select2 Elements
        $('.select2').select2();

        $('#date_donation').datepicker({
            autoclose: true
        });

        $('#campaign_name').autocomplete({
            source: '{{ url('/campaign/search') }}',
            minlenght: 100,
            autoFocus: true,
            create: function () {
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    return $("<li class='ui-menu-item'>")
                        .append('<a>' + item.campaign_title + '</a>')
                        .appendTo(ul);
                };
            },
            select: function (event, ui) {
                event.preventDefault();
                $('#campaign_name').val(ui.item.campaign_title);
                $('#campaign_id').val(ui.item.id);
            },
            html: true
        });


        $('#donor_name').autocomplete({
            source: '{{ url('/donor/search') }}',
            minlenght: 2,
            autoFocus: true,
            create: function () {
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    return $("<li class='ui-menu-item'>")
                        .append('<a>' + item.donor_name + ' - ' + item.donor_email + ' - ' + item.donor_phone + '</a>')
                        .appendTo(ul);
                };
            },
            select: function (event, ui) {
                event.preventDefault();
                $('#donor_name').val(ui.item.donor_name);
                $('#donor_email').val(ui.item.donor_email);
                $('#donor_phone').val(ui.item.donor_phone);
            },
            html: true
        });

        const urlParams = new URLSearchParams(window.location.search);
        const str = urlParams.get('str');
        const minDonation = urlParams.get('min_donation');
        const maxDonation = urlParams.get('max_donation');

        if (str) $('#str').val(str);
        if (minDonation) $('#min_donation').val(minDonation);
        if (maxDonation) $('#max_donation').val(maxDonation);

        $("#max_donation").attr({
            "min" : $("#min_donation").val()
        });

    </script>
@stop