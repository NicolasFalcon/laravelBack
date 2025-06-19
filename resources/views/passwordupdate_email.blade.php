
<!DOCTYPE html>
     <html lang="en">    
    
     <head>
         <title>Bootstrap 5 Example</title>
         <meta charset="utf-8">       
          <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
       <style>
       .bdr{
        margin-top:100px !important;
       }
       . text-center{
        text-align:center !important;
       }
       </style>
        
    </head>
       <body>
        <div class="container p-5 bg text-white" style="text-align:center; font-size:20px">
        <div>
             <div class="container bdr mt-5 " style="border-left:40px solid #941000; border-right:40px solid #941000; border-top:20px solid #941000; border-bottom:20px solid #941000; border-radius:10px; text-align:center; background-color:white; margin-top:40px;">
                 <div class="row mt-3  text-center">
                     <h1 style="color:black">Reset Password</h1>
                 </div>
                 <div class="row mt-3  text-center" style="margin-top:40px; width:100%; text-align:center">
    
                     <div class="">
                     <img src="{{ config('app.url') }}/json/image/FitMe%20Current%20Logo%20(3).png" style="width:30%">
                        
                     </div>
    
                 </div>
    
                 <div class="row mt-5">
                     <div class="col-md-12 text-center " style="margin-left: 10px;">
                         <p style ="color:black;font-size:15px;font-waight:bolder;">Please Click below button to reset  your password.
                         </p>
                     </div>
    
    
                     <div class="row mt-5 text-center mb-5" style="margin-top:30px;">
                         <div class="col-md-12">
                         
                             <a href="{{ config('app.url') }}/json/user_passwordupdate.php?email={{ $data['email'] }}"><button class="btn bttn btn-info" style="background-color:skyblue;font-waight:bolder;margin-bottom:30px; padding:15px;">Change Password</button></a>
                         </div>
                     </div>
                     <div class="row">
                         <div class="col-md-12 text-center " style="margin-left: 10px;">
                            
                         </div>
                     </div>
                 </div>
             </div>
             </div>
         </div>
     </body>
     </html>