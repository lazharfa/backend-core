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
        <!-- left column -->
        <div class="col-md-4">
            <!-- general form elements -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><strong>Input Donasi</strong></h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{ url('donation/create') }}" method="post">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <div class="form-group">
                            <label for="date_donation">Date Donation</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right" name="date_donation" id="date_donation" value="{{ date('m/d/Y') }}" placeholder="Date Donation">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="campaign_id">Campaign</label>
                            <input type="hidden" name="campaign_id" class="form-control" id="campaign_id" placeholder="Enter campaign">
                            <input type="text" class="form-control" id="campaign_name" placeholder="Enter campaign">
                        </div>
                        <div class="form-group">
                            <label for="date_donation">Total Donation</label>
                            <input type="text" class="form-control" id="total_donation" name="total_donation" placeholder="Total Donation">
                        </div>
                        <div class="form-group">
                            <label>Bank</label>
                            <select class="form-control select2" name="bank_id" style="width: 100%;">
                                <option selected value="" disabled>Pilih Bank</option>
                                @foreach($memberBanks as $memberBank)
                                    <option value="{{ $memberBank->id }}">{{ $memberBank->bank_info . ' | ' . $memberBank->bank_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="donor_name">Nama Donatur</label>
                            <input type="text" class="form-control" id="donor_name" name="donor_name" placeholder="Nama Donatur">
                        </div>
                        <div class="form-group">
                            <label for="donor_phone">Telpon Donatur</label>
                            <input type="text" class="form-control" id="donor_phone" name="donor_phone" placeholder="Telpon Donatur">
                        </div>
                        <div class="form-group">
                            <label for="donor_email">Email Donatur</label>
                            <input type="email" class="form-control" id="donor_email" name="donor_email" placeholder="Email Donatur">
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea class="form-control" rows="3" name="note" placeholder="Catatan"></textarea>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <button type="reset" class="btn btn-danger">Reset</button>
                        <button type="submit" class="btn btn-primary pull-right">Submit</button>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
        <!-- left column -->
        <div class="col-md-8">
            <!-- general form elements -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><strong>Donasi Terbaru</strong></h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <div class="box-body">

                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Donatur</th>
                            <th>Telpon Donatur</th>
                            <th>Email Donatur</th>
                            <th>Bank</th>
                            <th>Total Donasi</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($donations as $donation)
                            <tr>
                                <td>{{ $donation->date_donation }}</td>
                                <td>{{ $donation->donor_name }}</td>
                                <td>{{ $donation->donor_phone }}</td>
                                <td>{{ $donation->donor_email }}</td>
                                <td>{{ ($donation->bank) ? $donation->bank->bank_info : '' }}</td>
                                <td>{{ $donation->total_donation }}</td>
                                <td align="center">
                                    <a href="#modal-edit-donation"
                                       data-toggle="modal"
                                       data-date_donation="{{ date('m/d/Y', strtotime($donation->date_donation)) }}"
                                       data-campaign_title="{{ ($donation->campaign ? $donation->campaign->campaign_title : '')}}"
                                       data-campaign_id="{{ $donation->campaign_id }}"
                                       data-id="{{ $donation->id }}"
                                       data-donor_name="{{ $donation->donor_name }}"
                                       data-donor_phone="{{ $donation->donor_phone }}"
                                       data-donor_email="{{ $donation->donor_email }}"
                                       data-bank="{{ $donation->bank_id }}"
                                       data-total_donation="{{ $donation->total_donation }}"
                                       class="btn-edit-donation" title="Edit">
                                        <i class="fa fa-fw fa-pencil-square-o"></i>
                                    </a>
                                    <a href="#modal-delete-donation" data-toggle="modal" class="btn-delete-donation"
                                       data-id="{{ $donation->id }}"
                                       data-name="{{ $donation->donor_name }}"
                                       data-placement="right" title="Hapus">
                                        <i class="fa fa-fw fa-trash-o"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
                <!-- /.box-body -->

            </div>
            <!-- /.box -->
        </div>
    </div>

    <div class="modal fade" id="modal-edit-donation">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="form-edit-donation">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <input type="hidden" name="sale_id" id="sale-id">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Konfirmasi Pembatalan</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="date_donation">Date Donation</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right" name="date_donation" id="edit-date-donation" value="{{ date('m/d/Y') }}" placeholder="Date Donation">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="campaign_id">Campaign</label>
                            <input type="hidden" name="campaign_id" class="form-control" id="edit-campaign-id" placeholder="Enter campaign">
                            <input type="text" class="form-control" id="edit-campaign-title" placeholder="Enter campaign">
                        </div>
                        <div class="form-group">
                            <label for="date_donation">Total Donation</label>
                            <input type="text" class="form-control" id="edit-total-donation" name="total_donation" placeholder="Total Donation">
                        </div>
                        <div class="form-group">
                            <label>Bank</label>
                            <select class="form-control select2" id="edit-bank" name="bank_id" style="width: 100%;">
                                <option value="" disabled>Pilih Bank</option>
                                @foreach($memberBanks as $memberBank)
                                    <option value="{{ $memberBank->id }}">{{ $memberBank->bank_info . ' | ' . $memberBank->bank_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="donor_name">Nama Donatur</label>
                            <input type="text" class="form-control" id="edit-donor-name" name="donor_name" placeholder="Nama Donatur">
                        </div>
                        <div class="form-group">
                            <label for="donor_phone">Telpon Donatur</label>
                            <input type="text" class="form-control" id="edit-donor-phone" name="donor_phone" placeholder="Telpon Donatur">
                        </div>
                        <div class="form-group">
                            <label for="donor_email">Email Donatur</label>
                            <input type="email" class="form-control" id="edit-donor-email" name="donor_email" placeholder="Email Donatur">
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea class="form-control" rows="3" name="note" id="edit-donor-note" placeholder="Catatan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ya saya yakin</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <div class="modal fade" id="modal-delete-donation">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="form-delete-donation">
                    {{ csrf_field() }}
                    {{ method_field('delete') }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Konfirmasi Hapus</h4>
                    </div>
                    <div class="modal-body">
                        <p id="text-delete-sale">Anda yakin akan menghapus donasi ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya saya yakin</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

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

        $('#edit-campaign-name').autocomplete({
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
                $('#edit-campaign-name').val(ui.item.campaign_title);
                $('#edit-campaign-id').val(ui.item.id);
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

        $(document).on('click', '.btn-edit-donation', function () {

            const id = $(this).data('id');
            const campaignId = $(this).data('campaign_id');
            const campaignTitle = $(this).data('campaign_title');
            const dateDonation = $(this).data('date_donation');
            const donorName = $(this).data('donor_name');
            const donorPhone = $(this).data('donor_phone');
            const donorEmail = $(this).data('donor_email');
            const bank = $(this).data('bank');
            const totalDonation = $(this).data('total_donation');

            $('#edit-campaign-id').val(campaignId);
            $('#edit-campaign-title').val(campaignTitle);
            $('#edit-date-donation').val(dateDonation);
            $('#edit-donor-name').val(donorName);
            $('#edit-donor-phone').val(donorPhone);
            $('#edit-donor-email').val(donorEmail);
            $('#edit-total-donation').val(totalDonation);

            $('#edit-bank option[value="' + bank + '"]').prop('selected', true)

            // $('#text-cancel-sale').html('Anda yakin membatalkan pesanan atas nama <b>' + name + '</b> ?');
            $('#form-edit-donation').attr('action', "{{ url('donation') }}/" + id);
            // $('#sale-id').val(id);

        });

        $(document).on('click', '.btn-delete-donation', function () {

            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#text-delete-donation').html('Anda yakin menghapus donasi atas nama <b>' + name + '</b> ?');
            $('#form-delete-donation').attr('action', "{{ url('donation') }}/" + id);

        });

    </script>
@stop