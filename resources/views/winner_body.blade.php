<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations!</title>
    <!-- Include Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Poppins, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }
        .top-div {
            background-color: #922F2F;
            padding: 20px 0;
            /*display: flex;*/
            /*justify-content: center;*/
            height: 40px;
        }
        .logo-top {
            max-width: 100px;
            margin-top: 10px;
        }
        .section-1,
        .section-3 {
            background-color: #A93737;
            color: #fff;
            padding: 50px 50px;
        }
        .section-2 {
            background-color: #fff;
            color: #000;
            font-weight: 600;
            padding: 50px 50px;
        }
        .logo {
            max-width: 200px;
            margin: 0 auto 20px;
        }
        .heading {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .subheading {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .button-container {
            margin-bottom: 20px;
        }
        .button {
            padding: 10px 20px;
            font-size: 18px;
            margin: 0 10px;
            background-color: #007bff;
            color: #fff; 
            border: none;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
        }
        .button::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            z-index: 1;
            transition: all 0.3s ease;
        }
        .button:hover::before {
            transform: translateY(-100%);
        }
        .button i {
            position: relative;
            z-index: 2;
        }
        .matrix_r{
            position: relative;
        }
        .image_rs{
            position:absolute;
        }
        img.image_r {
    margin-bottom: -115px;
}
.social-icons_r{
    width: 40px;
    height: 40px;

}

/*.social-icons_rs {*/
/*    display: flex !important;*/
/*   justify-content: center !important;*/
/*    gap: 20px;*/
/*}*/
.m_-2085618599498772933social-icons_rs{
        display: flex !important;
   justify-content: center !important;
    gap: 20px;
}
.face-icons_r{
    width:20px;
    height:20px;
}
.btn_r{
    background-color:#1877f2;
}
.btn_rs{
    background-color:#01a0f1;
    
}

        @media only screen and (max-width:425px){
            .heading {
    font-size: 24px !important;
}
            .image_r{
                  width: 100px;
                  height: 100px;
                   border-radius: 15px;
            }
            .section-1,
        .section-3 {
            padding: 50px 20px !important;
        }
        .section-2 {
            padding: 50px 20px !important;
        }
        .button-container {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    
    
}
}
    </style>
</head>
<body>
    <div class="container">
        <div class="matrix_r">
        <div class="top-div">
            <!-- Logo to be positioned in the middle -->
            <div class="image_rs">
                <img src="{{ config('app.url') }}/json/image/image1.png" alt="Top Logo" class="logo-top"></div>
        </div>
        <div class="section-1">
            <strong><h1 class="heading">CONGRATULATIONS!!</h1></strong>
            <strong><p class="subheading">You've won!</p></strong>
            <img src="https://res.cloudinary.com/drfp9prvm/image/upload/v1720811081/DALL_E_2024-07-13_00.34.06_-_A_vibrant_and_celebratory_image_featuring_a_golden_trophy_with_a_shiny_surface_placed_on_a_pedestal._The_background_is_filled_with_colorful_confetti_xwbius.webp" alt="Winner Image" class="image_r">
        </div>
    </div>
        <div class="section-2">
         
            <h1 class="name_r">{{ $data['name'] }},</h1>
                <p>We are excited to announce that you are the WINNER of this week’s fitness challenge! 🌟🎉To celebrate your fantastic win, we're excited to offer you a CASH REWARD....
                </p>
        </div>
        <div class="section-3">
            <h2>Please provide us with your UPI ID by replying to this email within the next 2 days to claim your reward.
            </h2>
            <p>Love the app? Tag us on:</p>
            <div class="social-icons_rs">
                <a href="https://www.facebook.com/fitnessandworkoutapp/"><img src="{{ config('app.url') }}/json/image/facebook.png" alt="Top Logo" class="social-icons_r"></a>
                <a href="https://www.instagram.com/fitnessandworkoutapp/"><img src="{{ config('app.url') }}/json/image/insta.png" alt="Top Logo" class="social-icons_r"></a>
                <a href="https://x.com/fitnessoworkout?prefetchTimestamp=1720811598570"><img src="{{ config('app.url') }}/json/image/twitter.png" alt="Top Logo" class="social-icons_r"></a>
            </div>
            <img src="{{ config('app.url') }}/json/image/image1.png" alt="Top Logo" class="logo-top">
            <h2>Stay Active, Stay Fit and Stay Awesome</h2>
            <p>You’re recieving this email because you have a account. If you don’t want to recieve these updates anymore, feel free to unsubscribe</p>
            <p>Team FitMe</p>
        </div>
    </div>


</body></html>