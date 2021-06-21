<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Program</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
    <style type="text/css">
        body{
            background: #E5E5E5;
            line-height: 21px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            letter-spacing: 0.02em;
            color: #606060;
        }
        .section-content{
            background: white;
            padding:30px;
            padding-top:0px;
        }
        .header{
            text-align: center;
        }
        .logo > img{
            background: #14c3d4;
            padding: 10px 36px;
            border-bottom: 10px solid #0c8c98;
            width: 80px;
        }
        .container{
            margin:auto;
            width: 670px;
            height: 818px;
        }
        .content{
            margin-top:40px;
            padding-left:50px;
            padding-right:50px;
        }
        div{
            margin-top:2px;
            margin-bottom:2px;
        }
        .content-cr{
            margin-top:50px;
        }
        .content-cr > .col{
            float: left;
            width: 20%;
        }
        .content-cr > .col2{
            float: left;
            width: 50%;
            margin-top: 25px;
        }
        .campaign-title{
            text-align: center;
            margin-top: 24px;
            font-size: 24px;
            font-weight: 600;
        }

        .campaign-doa{
            text-align: center;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .cr-name{
            font-weight: bold;
        }
        .cr-label{
            font-style: italic;
        }
        .section-footer{
            background: #14c3d4;
            border-top: 10px solid #0c8c98;
        }
        .footer{
            padding-top: 35px;
            padding-left: 45px;
            padding-right: 45px;
            padding-bottom: 150px;
            color:white;
        }
        .footer .left{
            width: 50%;
            float: left;
        }
        .footer .right{
            width: 32%;
            float: right;
        }
        .clear{
            clear:both;
        }
        .footer svg{
            margin-top: 5px;
            margin-left: 5px;
        }
        .footer a{
            color: white;
        }
        .footer > .right > a{
            text-decoration: none;
        }
        .cr-foto > img{
            border-radius: 50%;
            width: 90px;
            height: 90px;
            float:left;
        }
        .img-campaign{
            width: 100%;
            border-radius: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .label-head{
            font-weight: bold;
        }
        .label-address{
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="section-content">
        <div class="header">
            <div class="logo">
                <img src="https://core.rumahasuh.org/api/image/logo_rumahasuh.png">
            </div>
        </div>

        <div class="content">
            <div>
                <div class="thank-desc">
                    Halo kak <b>{{ $donation->donor_name }}</b>,
                    <br>

                    Berikut update dari Campaign <b><a href="{{ 'https://rumahasuh.org/id/campaign/' . $campaignProgress->campaign->campaign_slug }}">{{ $campaignProgress->campaign->campaign_title }}</a></b>
                </div>
            </div>

            <div class="email-text">
                <img src="{{ 'https://core.rumahasuh.org/api/image/' . $campaignProgress->news_image }}" class="img-campaign">
                <p>
                    Story lengkapnya bisa dilihat di <b>update.rumahasuh.org</b><br>
                    Terimakasih telah menjadi bagian dari kami<br><br>

                    Kak, yuk bantu campaign lainya : <b>campaign.rumahasuh.org</b>
                </p>
            </div>

            <div class="content-cr">
                <div class="col">
                    <div class="cr-foto">
                        <img src="https://core.rumahasuh.org/api/image/cr_rumahasuh.jpg">
                    </div>
                </div>
                <div class="col2">
                    <div class="cr-name">Muhammad Ibda</div>
                    <div class="cr-label">Direktur Rumah Asuh</div>
                </div>
                <div class="clear"></div>
            </div>

        </div>
    </div>

    <div class="section-footer">
        <div class="footer">
            <div class="left">
                <div class="label-head">Rumah Asuh</div>
                <div class="label-address">
                    Head Office<br>
                    Address : Jl. Setra Dago Barat No 27 Bandung<br>
                    <a href="https://rumahasuh.org/id">www.rumahasuh.org</a>
                </div>

            </div>
            <div class="right">
                <div class="label-head">Stay Connected with us :</div>
                <a href="https://id-id.facebook.com/rumahasuhorg">
                    <img src="https://core.rumahasuh.org/api/image/rumah-asuh-facebook.png" width="30" height="30">
                </a>
                <a href="https://www.instagram.com/rumahasuhorg">
                    <img src="https://core.rumahasuh.org/api/image/rumah-asuh-instagram.png" width="30" height="30">
                </a>
                <a href="https://api.whatsapp.com/send?phone=628112220118">
                    <img src="https://core.rumahasuh.org/api/image/rumah-asuh-whatsapp.png" width="30" height="30">
                </a>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
</body>
</html>
