
<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title> CropS - Smart Greenhouse Products Marketplace </title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel ="stylesheet" href="static/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <header>
        
        <a href = "index.php" class = "logo">
            <img src = "static/images/logo.png" alt = "CropS" class = "logo-icon">
            
        </a>
        <nav class = "nav-links">
            <a href = "index.php"> Home </a>
            <a href = "#"> Stores </a>
            <a href = "#"> Products </a>
            <a href = "#"> Tips </a>
            <a href = "#"> About Us </a>
            <a href = "#"><i class = "fas fa-shopping-cart"></i></a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href = "#" class = "btn btn-outline" style = "padding: 5px 15px;">
                    <i class = "fas fa-user-circle"></i> Profile
                </a> <!-- fontAwesomeUserIcon -->
               <a href = "logout.php" style = "color:red; font-weight:600;"> Logout </a>
    
            <?php else: ?>
               <a href = "login.php" class = "btn btn-primary" style = "padding: 5px 15px; color: white;">Login </a>
            <?php endif; ?>
        </nav>
       
    </header>



