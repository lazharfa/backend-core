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

        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            color: rgba(49, 53, 59, 0.96);
            background: rgb(172, 230, 221);
            font-size: 14px;
            font-weight: 500;
            max-width: 480px;
            margin: auto;
            max-height: auto;
        }

        table {
            border-spacing: 0px;
            border-collapse: separate;
        }

        .page {
            max-width: 480px;
            margin: auto;
            padding-top: 12px;
        }

        .section {
            background: white;
            margin-bottom: 8px;
            padding: 16px 20px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            margin-top: 0px;
        }

        .header {
            background: white;
            margin-bottom: 8px;
            padding: 16px 20px;
        }

        .footer {
            background: white;
            padding: 16px 20px;
        }

        .title {
            font-size: 20px;
            font-weight: 600;
            line-height: 28px;
        }

        .date {
            color: rgba(49, 53, 59, 0.54);
            font-size: 12px;
        }

        .campaign-img {
            border-radius: 4px;
            width: 80px;
        }

        .campaign-title {
            font-weight: 600;
            font-size: 16px;
        }

        .border-b {
            border-bottom: 1px solid rgba(49, 53, 59, 0.12);
        }

        .wrapper {
            padding-bottom: 10px;
        }

        .wrapper div {
            line-height: 19.6px;
        }

        .wrapper .label {
            color: rgba(49, 53, 59, 0.75);
            font-size: 14px;
        }

        .wrapper table {
            width: 100%;
        }

        .text-right {
            text-align: right;
        }

        .mb-4 {
            margin-bottom: 4px;
        }

        .mb-16 {
            margin-bottom: 16px;
        }

        .ml-16 {
            margin-left: 16px;
        }

        .down {
            height: 247px;
            max-width: 480px;
            background: rgb(255, 255, 255);
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="section">
            <div class="row">
                <div class="col-2">
                    <img align="left" style="width: 80px; height: 60px;" src="{{ $full_color_logo }}" />
                </div>
                <div class="col-10" align="center">
                    <p style="font-size: 15px"> LAZ Harapan Dhuafa </p>
                    <p style="font-size: 10px">Jl. Ciwaru Raya Kompek Pondok Citra 1 No. 1B Serang - Banten</p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="title mb-4">Donasi Berhasil</div>
            <div class="date">
                <span>{{ date('d M Y H.m', strtotime("$donation->date_donation - 7 hours")) }} WIB</span>
                <span>{{ $donation->donation_number }}</span>
            </div>
        </div>

        <div class="section">
            <div class="title mb-10">Program</div>
            <table>
                <tr>
                    <td width="70">
                        @if($donation->campaign_id)
                        <img class="campaign-img" src="{{ $donation->campaign->image_url }}" />
                        @endif
                    </td>
                    <td>
                        <div class="campaign-title">{{ $donation->campaign_id ? $donation->campaign->campaign_title : $donation->total_donation }}</div>
                    </td>
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
            <div class="wrapper mb-10">
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
                        <td>
                            <div class="label">Metode Pembayaran</div>
                        </td>
                        <td>
                            <div class="text-right">{{ $donation->bank->bank_info }}</div>
                        </td>
                    </tr>
                </table>
            </div>
            @endif
            <div class="wrapper border-b mb-16">
                <table>
                    <tr>
                        <td>
                            <div class="label">Nominal Donasi</div>
                        </td>
                        <td>
                            <div class="text-right">Rp {{ number_format($donation->donation, 2, ',', '.') }}</div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="wrapper border-b mb-16">
                <table>
                    <tr>
                        <td>
                            <div class="label">Kode Unik</div>
                        </td>
                        <td>
                            <div class="text-right">Rp {{ $donation->unique_value }}</div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="wrapper mb-10">
                <table>
                    <tr>
                        <td>
                            <div class="label">Total Donasi</div>
                        </td>
                        <td>
                            <div class="text-right">Rp {{ number_format($donation->donation + $donation->unique_value, 2, ',', '.') }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="footer">
            <div class="wrapper" align="center">
                <p style="font-size: 14px"> Terimakasih atas kepercayaannya berdonasi melalui
                    LAZ Harapan Dhuafa. Semoga menjadi amal jariyah dan pembersih harta Bapak/Ibu sekeluarga. </p>
            </div>
        </div>
        <div class="down"></div>
    </div>
</body>

</html>