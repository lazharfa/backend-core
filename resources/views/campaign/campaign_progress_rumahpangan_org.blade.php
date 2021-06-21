<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style type="text/css">
        body {
            max-width: 411px;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.01em;
            color: rgba(0, 0, 0, 0.6);
            font-size: 16px;
        }

        strong {
            color: rgba(0, 0, 0, 0.85);
        }

        .logoibm {
            margin-left: 10px;
            margin-top: 10px;
        }

        .gambar {
            width: 100%;
        }

        .card-body {
            background-color: #025F9D;
            height: 75px;
            width: 100%;
            padding-top: 1px;
            padding-bottom: 5px;
            color: white;
            text-align: center;
            margin-top: 30px;
        }

        .text-link {
            color: #007DBE;
            font-weight: bold;
        }

        .subjudul {
            margin-top: 30px;
            font-size: 14px;
            line-height: 0px;
        }

        a {
            color: white;
            text-decoration-line: none;
        }

        @media (min-width: 280px) and (max-width: 425px) {
            body {
                max-width: 411px;
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.01em;
                color: rgba(0, 0, 0, 0.6);
                font-size: 16px;
                margin-left: 7px;
            }
        }

        @media (min-width: 540px) {
            body {
                max-width: 540px;
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.01em;
                color: rgba(0, 0, 0, 0.6);
                font-size: 16px;
                margin-left: 8px;
            }
        }

        @media (min-width: 768px) {
            body {
                max-width: 411px;
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.01em;
                color: rgba(0, 0, 0, 0.6);
                font-size: 16px;
                margin-left: 180px;
            }

            .card-body {
                background-color: #007DBE;
                height: 75px;
                width: 100%;
                padding-top: 1px;
                padding-bottom: 5px;
                color: white;
                text-align: center;
                margin-top: 93px;
            }
        }

        @media (min-width: 770px) and (max-width: 1024px) {
            body {
                max-width: 411px;
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.01em;
                color: rgba(0, 0, 0, 0.6);
                font-size: 16px;
                margin-left: 300px;
            }

            .card-body {
                background-color: #007DBE;
                height: 75px;
                width: 100%;
                padding-top: 1px;
                padding-bottom: 5px;
                color: white;
                text-align: center;
                margin-top: 290px;
            }
        }

        @media (min-width: 1025px) {
            body {
                max-width: 411px;
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.01em;
                color: rgba(0, 0, 0, 0.6);
                font-size: 16px;
                margin-left: 450px;
            }
        }
    </style>
</head>

<body>
    <img src="https://rumahpangan.org/assets/logo.png" width="70px" class="logoibm">
    <br><br>

    <div class="isi">
        <p>Halo <strong>{{ $donation->donor_name }}</strong>, <br>
            Berikut kabar terbaru dari program yang anda bantu</p>
            <a href="{{ 'https://rumahpangan.org/id/post/' . $campaignProgress->news_slug }}" style="color: #007DBE">
                <img src="{{ 'https://core.rumahpangan.org/api/image/' . $campaignProgress->news_image }}" class="gambar">
            </a>

        <p class="subjudul">Kabar Terbaru</p>
        <p class="text-link"> {{ date('d F Y', strtotime($campaignProgress->news_date)) }} : <a style="color: #007DBE" href="{{ 'https://rumahpangan.org/id/post/' . $campaignProgress->news_slug }}">{{ $campaignProgress->news_title }}</a></p>

        <p class="subjudul">Program</p>
        <a href="{{ 'https://rumahpangan.org/id/campaign/' . $campaignProgress->campaign->campaign_slug }}" style="color: #007DBE">{{ $campaignProgress->campaign->campaign_title }}</a>
        <p>Terima kasih sudah menjadi bagian dari Sahabat Pedalaman.</p> <br>
        <p>Salam Hangat,</p>

        <p> Customer Relation Rumah Pangan Bangsa</p>
    </div>

    <div class="card-body">
        <p> <a href="">Program lainnya </a> | <a href="">Blog </a> </p>
        <p>

            <a href="https://www.facebook.com/rumahpangan.org">
                <img src="https://core.insanbumimandiri.org/api/image/ibm-fb.png" width="22" height="20">
            </a>
            <a href="https://instagram.com/rumahpanganorg">
                <img src="https://core.insanbumimandiri.org/api/image/ibm-ig.png" width="20" height="20">
            </a>
        </p>

    </div>
</body>

</html>