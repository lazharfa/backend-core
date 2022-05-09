<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Donasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page {
          margin: 0;
        }
        body{
            margin: 0;
            font-family: 'Roboto', sans-serif;
            color:rgba(49, 53, 59, 0.96);
            background: rgba(242, 242, 242, 1);
            font-size:14px;
            font-weight: 500
        }
        table{
            border-spacing: 0px;
            border-collapse: separate;
        }
        .page{
            max-width: 480px;
            margin:auto;
            padding-top:12px;
        }
        .section{
            background: white;
            margin-bottom:8px;
            padding:16px 20px;
        }
        .title{
            font-size:20px;
            font-weight: 600;
            line-height: 28px;
        }
        .date{
            color:rgba(49, 53, 59, 0.54);
            font-size:12px;
        }
        .campaign-img{
            border-radius:4px;
            width:80px;
        }
        .campaign-title{
            font-weight: 600;
            font-size: 16px;
        }
        .border-b{
            border-bottom: 1px solid rgba(49, 53, 59, 0.12);
        }
        .wrapper{
            padding-bottom: 16px;
        }
        .wrapper div{
            line-height: 19.6px;
        }
        .wrapper .label{
            color:rgba(49, 53, 59, 0.75);
            font-size:14px;
        }
        .wrapper table{
            width:100%;
        }
        .text-right{
            text-align: right;
        }
        .mb-4{
            margin-bottom:4px;
        }
        .mb-16{
            margin-bottom:16px;
        }
        .ml-16{
            margin-left:16px;
        }
        </style>
      </head>
      <body>
        <div class="page">
            <div class="section">
                <img class="mb-16" src="{{ $full_color_logo }}"/>
                <div class="title mb-4">Donasi Berhasil</div>
                <div class="date">
                    <span>{{ date('d M Y H.m', strtotime("$donation->date_donation - 7 hours")) }} WIB</span>
                    
                    <span>{{ $donation->donation_number }}</span>
                </div>
            </div>

            <div class="section">
                <div class="title mb-16">Program</div>
                <table>
                    <tr>
                        <td width="70">
                          @if($donation->campaign_id)
                            <img class="campaign-img" src="{{ $donation->campaign->image_url }}"/>
                          @endif
                        </td>
                        <td><div class="campaign-title">{{ $donation->campaign_id ? $donation->campaign->campaign_title : $donation->total_donation }}</div></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="title mb-16">Donatur</div>

                <div class="wrapper border-b mb-16">
                    <div class="label">Nama Lengkap</div>
                    <div>{{ $donor_name }}</div>
                </div>

                @if($donation->donor_phone)
                    <div class="wrapper border-b mb-16">
                        <div class="label">Nomor WhatsApp</div>
                        <div>{{ $donor_phone }}</div>
                    </div>
                @endif

                @if($donation->donor_email)
                    <div class="wrapper mb-16">
                        <div class="label">Email</div>
                        <div>{{ $donor_email }}</div>
                    </div>
                @endif
            </div>

            <div class="section">
                <div class="title mb-16">Rincian Donasi</div>

                @if($donation->bank)
                    <div class="wrapper border-b mb-16">
                        <table>
                            <tr>
                                <td><div class="label">Metode Pembayaran</div></td>
                                <td><div class="text-right">{{ $donation->bank->bank_info }}</div></td>
                            </tr>
                        </table>
                    </div>
                @endif
                <div class="wrapper border-b mb-16">
                    <table>
                        <tr>
                            <td><div class="label">Nominal Donasi</div></td>
                            <td><div class="text-right">Rp {{ number_format($donation->donation, 2, ',', '.') }}</div></td>
                        </tr>
                    </table>
                </div>
                <div class="wrapper border-b mb-16">
                    <table>
                        <tr>
                            <td><div class="label">Kode Unik</div></td>
                            <td><div class="text-right">Rp {{ $donation->unique_value }}</div></td>
                        </tr>
                    </table>
                </div>
                <div class="wrapper mb-16">
                    <table>
                        <tr>
                            <td><div class="label">Total Donasi</div></td>
                            <td><div class="text-right">Rp {{ number_format($donation->donation + $donation->unique_value, 2, ',', '.') }}</div></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
      </body>
</html>