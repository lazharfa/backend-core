<!DOCTYPE html>
<html>
<head>
    <title>Update Program</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <style type="text/css">
        body{
            background: #E5E5E5;
            line-height: 21px;
            font-family: 'Roboto';
            font-size: 15px;
            letter-spacing: 0.02em;
            color: #606060;
        }
        .title a{
            color: black;
            text-decoration: none;
        }
        .news a{
            color: black;
            text-decoration: none;
        }
        .section-content{
            background: white;
            padding:30px;
        }
        .container{
            margin:auto;
            width: 810px;
            height: 818px;
        }
        .content{
            margin-top:50px;
        }
        div{
            margin-top:2px;
            margin-bottom:2px;
        }

        .campaign-content{
            margin-top:30px;
        }

        .campaign-content > .left{
            float: left;
            width: 40%;
        }
        .campaign-content > .left > img{
            width: 90%;
        }

        .campaign-content > .right{
            float: left;
            width: 60%;
        }
        .campaign-content .title{
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            height: 65px;
            line-height: 26px;
        }
        .campaign-content .label{
            font-size: 12px;
        }
        .campaign-content .news{
            font-weight: bold;
            font-size: 16px;
            line-height: 21px;
        }
        .content-cr{
            margin-top:50px;
            margin-bottom: 109px;
        }
        .cr-name{
            font-weight: bold;
        }
        .cr-label{
            font-style: italic;
        }
        .section-footer{
            margin-top: 23px;
            height: 71px;
            background: #007DBE;
        }
        .footer{
            padding:23px;
            color:white;
            margin-bottom: 60px;
        }
        .footer .left{
            width: 50%;
            float: left;
        }
        .footer .right{
            width: 30%;
            float: right;
            text-align: right;
        }
        .clear{
            clear:both;
        }
        .footer svg{
            margin-left: 5px;
        }
        .footer a{
            color: white;
        }
        .footer > .right > a{
            text-decoration: none;
        }

        .thank-desc{
            margin-top:20px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="section-content">
        <div class="header">
            <div class="logo">
                <img src="https://core.insanbumimandiri.org/api/image/logo.png">
            </div>
        </div>

        <div class="content">
            <div class="intro">
                Halo <b>{{ $donation->donor_name }}</b>, <br>Berikut kabar terbaru dari program yang Anda bantu
            </div>

            <div class="campaign-content">
                <div class="left">
                    <img src="{{ 'https://core.insanbumimandiri.org/api/image/' . $campaignProgress->news_image }}">
                </div>
                <div class="right">
                    <div class="campaign-desc">
                        <div class="label">Program</div>
                        <div class="title">
                            <a href="{{ 'https://insanbumimandiri.org/id/campaign/' . $campaignProgress->campaign->campaign_slug }}">{{ $campaignProgress->campaign->campaign_title }}</a>
                        </div>
                        <div class="label">Kabar Terbaru</div>
                        <div class="news">{{ $campaignProgress->news_date }} : <a href="{{ 'https://insanbumimandiri.org/id/post/' . $campaignProgress->news_slug }}">{{ $campaignProgress->news_title }}</a></div>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="thank-desc">Terima kasih sudah menjadi bagian dari Sahabat Pedalaman.</div>
            </div>

            <div class="content-cr clear">
                Salam Hangat,
                <div class="cr-foto">
                    <img src="https://core.insanbumimandiri.org/api/image/cr.png">
                </div>
                <div class="cr-name">Tanti Isyka Rafatullah</div>
                <div class="cr-label">Customer Relation</div>
            </div>

        </div>
    </div>

    <div class="section-footer">
        <div class="footer">
            <div class="left"><a href="https://insanbumimandiri.org/id/campaign">Bantuan Program Lainnya</a> | <a href="https://insanbumimandiri.org/id/post/category/non-campaign">Blog</a></div>
            <div class="right">
                <a href="https://twitter.com/insan_bm">
                    <svg width="21" height="19" viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M19.1195 2.50827L20.5343 2.67349L19.8983 3.948C19.5882 4.56953 19.2778 5.19105 18.967 5.81258C18.9302 5.94422 18.8654 6.05962 18.7697 6.20987C18.7296 6.27265 18.5929 6.47236 18.5865 6.48194C18.5504 6.53608 18.5237 6.57878 18.5045 6.61299V9.00152C18.5045 15.1135 12.5895 18.9974 7.00354 18.9974C5.86051 18.9974 4.99207 18.9427 3.99765 18.7257C2.36115 18.3685 1.14327 17.6587 0.585973 16.418L0.0122107 15.1407L1.40659 15.0124C2.66801 14.8964 3.76169 14.6561 4.60159 14.3343C2.29577 13.9635 1.0036 12.9508 1.0036 11.0489V10.0489H2.0036C2.22331 10.0489 2.42143 10.0311 2.59854 9.99834C0.868001 8.96365 0.00122175 7.30379 0.00122175 5.00152C0.00103266 4.9034 0.00103266 4.90339 0.000442551 4.79847C-0.00605857 3.63803 0.0562722 3.01797 0.373948 2.22659C0.577543 1.71941 0.871831 1.24988 1.2679 0.819668L2.02251 0L2.75617 0.838468C5.17394 3.60161 7.56395 5.27795 10.0042 5.48072C10.0146 2.93105 11.9415 1.00152 14.5043 1.00152C15.6991 1.00152 16.7828 1.45501 17.6345 2.27273C18.1006 2.36851 18.5957 2.44709 19.1195 2.50827ZM16.9086 4.16202L16.6021 4.0926L16.3904 3.86028C15.8785 3.29855 15.2359 3.00152 14.5043 3.00152C13.0414 3.00152 12.0041 4.04391 12.0041 5.50152C12.0041 5.73974 11.998 5.88942 11.9683 6.08615C11.8499 6.87116 11.4096 7.50152 10.5041 7.50152C7.50607 7.50152 4.80136 5.89542 2.16389 3.15228C2.02792 3.56561 1.99595 3.99047 2.00041 4.78727C2.00101 4.89384 2.00101 4.89384 2.00122 5.00152C2.00122 7.04953 2.83093 8.16977 4.79547 8.79419L5.49255 9.01575V9.7472C5.49255 10.6342 4.65222 11.4691 3.42268 11.8431C3.98631 12.2708 5.139 12.5015 7.00389 12.5015H8.00389V13.5015C8.00389 14.9343 6.35762 16.0561 3.87075 16.6419C4.68178 16.8903 5.76166 16.9974 7.00354 16.9974C11.618 16.9974 16.5045 13.7888 16.5045 9.00152V6.50152C16.5045 6.20774 16.5897 5.95273 16.7311 5.68759C16.7865 5.58393 16.8474 5.48509 16.9225 5.37237C16.9367 5.35115 16.9892 5.27426 17.0309 5.21279L17.1101 5.05429C17.2386 4.79745 17.3669 4.54061 17.4952 4.28377C17.2958 4.24599 17.1003 4.20541 16.9086 4.16202Z" fill="white"/>
                    </svg>
                </a>

                <a href="https://id-id.facebook.com/insanbumimandiri">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2 0H18C19.1046 0 20 0.89543 20 2V18C20 19.1046 19.1046 20 18 20H2C0.89543 20 0 19.1046 0 18V2C0 0.89543 0.89543 0 2 0ZM2 2V18H18V2H2ZM9.13306 10.0044H11V16H13V10.0044H14.9824V8.00439H13V7C13 6.44772 13.4477 6 14 6H15V4H14C12.3431 4 11 5.34315 11 7V8.00439H9.13306V10.0044Z" fill="white"/>
                    </svg>
                </a>
                <a href="https://www.instagram.com/insanbumimandiri/?hl=en">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M14 0H6C2.68629 0 0 2.68629 0 6V14C0 17.3137 2.68629 20 6 20H14C17.3137 20 20 17.3137 20 14V6C20 2.68629 17.3137 0 14 0ZM2 6C2 3.79086 3.79086 2 6 2H14C16.2091 2 18 3.79086 18 6V14C18 16.2091 16.2091 18 14 18H6C3.79086 18 2 16.2091 2 14V6ZM10 15C7.23858 15 5 12.7614 5 10C5 7.23858 7.23858 5 10 5C12.7614 5 15 7.23858 15 10C15 12.7614 12.7614 15 10 15ZM10 13C11.6569 13 13 11.6569 13 10C13 8.34315 11.6569 7 10 7C8.34315 7 7 8.34315 7 10C7 11.6569 8.34315 13 10 13ZM16 5C16 5.55228 15.5523 6 15 6C14.4477 6 14 5.55228 14 5C14 4.44772 14.4477 4 15 4C15.5523 4 16 4.44772 16 5Z" fill="white"/>
                    </svg>
                </a>
                <a href="https://www.youtube.com/channel/UCwkJEMcdSu8u1HyseNQNkHg">
                    <svg width="22" height="21" viewBox="0 0 22 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M21.0247 14.1282C21.7081 12.9571 22 11.0689 22 7.9972C22 4.93778 21.7108 3.06048 21.0202 1.85803C20.3281 0.609363 19.5073 0.213032 17.8783 0.117901C16.7662 0.0425222 14.0023 0 11.0027 0C7.99661 0 5.23145 0.0425064 4.111 0.118492C2.49112 0.213258 1.67011 0.610465 0.971932 1.8626C0.291384 3.0609 0 4.94737 0 8.0084C0 11.052 0.292795 12.948 0.977912 14.1424C1.66701 15.3784 2.47493 15.7708 4.1086 15.8855C5.28276 15.9543 8.20442 16 11.0027 16C13.7946 16 16.7149 15.9542 17.8783 15.8863C19.527 15.7708 20.3348 15.3778 21.0247 14.1282ZM17.7524 2.11391C18.7714 2.17346 18.9603 2.26505 19.2833 2.84964C19.7535 3.664 20 5.25771 20 8.0084C20 10.7383 19.7522 12.3408 19.2855 13.141C18.9614 13.7274 18.7738 13.8187 17.7501 13.8904C16.6489 13.9547 13.757 14 11.0027 14C8.24213 14 5.34874 13.9547 4.23703 13.8897C3.22601 13.8186 3.03565 13.7262 2.71873 13.1578C2.24886 12.3385 2 10.727 2 7.9972C2 5.26899 2.24754 3.66639 2.7149 2.84342C3.03675 2.26627 3.22844 2.17353 4.23709 2.11449C5.30508 2.04211 8.04424 2 11.0027 2C13.9547 2 16.6926 2.04212 17.7524 2.11391ZM9 5L14 8L9 11V5Z" fill="white"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
</body>
</html>
