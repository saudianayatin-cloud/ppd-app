<?php
// maintenance.php

http_response_code(503);
header("Retry-After: 3600"); // Retry after 1 hour
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Under Maintenance</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:linear-gradient(135deg,#0d47a1,#1976d2,#42a5f5);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#fff;
}

.container{
    background:rgba(255,255,255,.1);
    backdrop-filter:blur(12px);
    border-radius:15px;
    padding:50px;
    width:90%;
    max-width:650px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,.3);
}

.icon{
    font-size:80px;
    margin-bottom:20px;
}

h1{
    font-size:36px;
    margin-bottom:15px;
}

p{
    font-size:18px;
    line-height:1.7;
    opacity:.95;
}

.notice{
    margin-top:30px;
    padding:15px;
    background:rgba(255,255,255,.15);
    border-radius:8px;
    font-size:15px;
}

.footer{
    margin-top:30px;
    font-size:14px;
    color:#ddd;
}

.loader{
    width:55px;
    height:55px;
    border:5px solid rgba(255,255,255,.3);
    border-top:5px solid #fff;
    border-radius:50%;
    margin:30px auto;
    animation:spin 1s linear infinite;
}

@keyframes spin{
    100%{
        transform:rotate(360deg);
    }
}
</style>

</head>
<body>

<div class="container">

    <div class="icon">🛠️</div>

    <h1>System Under Maintenance</h1>

    <p>
        We are currently performing scheduled maintenance to improve our services.
        Please check back again later.
    </p>

    <div class="loader"></div>

    <div class="notice">
        <strong>Estimated Downtime:</strong><br>
        Approximately 1 hour.
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> Your Organization<br>
        Thank you for your patience.
    </div>

</div>

</body>
</html>