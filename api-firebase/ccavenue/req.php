<html>
<head>
<title> Non-Seamless-kit</title>
</head>
<body>
<center>
    <?php 
    
    include('Crypto.php');
        $order_id=rand(100,1000000);
        $plainText='merchant_id=272592354&order_id='.$order_id.'&redirect_url=https://spiderekart.in/india_demo_new/api-firebase/ccavenue/ccavResponseHandler.php&cancel_url=https://spiderekart.in/india_demo_new/api-firebase/ccavenue/ccavResponseHandler.php&amount=1&currency=INR&billing_name=Ali&billing_address=test Address&billing_zip=680015&billing_city=chennai';
        
        $encval=encrypt($plainText,'A037EBDD6A4BAB2D04453A6CE0FB162B');
        
    
?>
<form method="get" name="redirect" action="https://secure.ccavenue.com/transaction.do?command=initiateTransaction"> 
<?php
//echo '<input type=hidden name="merchant_id" value="2432357>"';
//echo '<input type=hidden name="order_id" value="'.$order_id.'">';
//echo '<input type=hidden name="redirect_url" value="'.$url.'">';
//echo '<input type=hidden name="cancel_url" value="'.$url.'">';
echo '<input type=hidden name="encRequest" value="'.$encval.'">';
echo '<input type=hidden name="access_code" value="AVHE95KH87AI85EHIAGDDR">';
//echo '<input type=hidden name="language" value="EN">';
?>
</form>
</center>
<script language='javascript'>document.redirect.submit();</script>
</body>
</html>

