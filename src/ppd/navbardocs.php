 <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MPW-BARMM PIMS Navbar</title>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        <style>
            body {
                margin: 0;
                font-family: Arial, Helvetica, sans-serif;
            }

            /* Navbar container */
            .topnav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                background-color: #f1f1f1;
                padding: 0 10px;
                flex-wrap: wrap;
            }

            /* Left and right sections */
            .topnav .left-section,
            .topnav .right-section {
                display: flex;
                align-items: center;
            }

            /* Navbar brand/logo */
            .navbar-logo {
                width: 45px;
                height: 45px;
                margin-right: 10px;
            }

            .navbar-brand {
                font-size: 18px;
                font-weight: 500;
                color: #9d9d9d;
                text-decoration: none;
            }

            /* Dropdown */
            .dropdown {
                position: relative;
                margin-left: 10px;
            }

            .dropbtn {
                background: none;
                border: none;
                font-size: 16px;
                cursor: pointer;
                color: #9d9d9d;
                padding: 14px 10px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .dropbtn i {
                margin-right: 5px;
            }

            .dropdown-content {
                display: none;
                position: absolute;
                background-color: #f9f9f9;
                min-width: 160px;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                z-index: 1000;
            }

            .dropdown-content a {
                color: black;
                padding: 10px 16px;
                text-decoration: none;
                display: block;
            }

            .dropdown-content a:hover {
                background-color: #ddd;
            }

            .dropdown:hover .dropdown-content {
                display: block;
            }

            /* Hamburger menu */
            .icon {
                display: none;
                font-size: 24px;
                cursor: pointer;
                color: #9d9d9d;
            }

            @media screen and (max-width: 768px) {

                .topnav .left-section,
                .topnav .right-section {
                    display: none;
                    flex-direction: column;
                    width: 100%;
                }

                .topnav.responsive .left-section,
                .topnav.responsive .right-section {
                    display: flex;
                }

                .dropdown-content {
                    position: relative;
                }

                .topnav.responsive .dropbtn {
                    width: 100%;
                    text-align: left;
                }

                .icon {
                    display: block;
                }
            }
        </style>
    </head>

    <body>

        <div class="topnav" id="myTopnav">
            <!-- Left Section -->
            <div class="left-section">
                <a href="#" class="navbar-brand">
                    <img src="images/mpw-icon.png" alt="MPW Logo" class="navbar-logo">
                    MINISTRY OF PUBLIC WORKS
                </a>
            </div>

            <!-- Right Section -->
            <div class="right-section">
                <!-- Home -->
                <div class="dropdown">
                    <button class="dropbtn" hidden><i class="fas fa-home"></i> Home</button>
                    <div class="dropdown-content">
                        <!-- <a href="home.php">Main Home</a> -->
                    </div>
                </div>

                <!-- Project List -->
                <!-- <div class="dropdown">
                    <button class="dropbtn"><i class="far fa-list-alt"></i> Project List <i class="fa fa-caret-down"></i></button>
                    <div class="dropdown-content">
                        <a href="funded.php">Funded</a>
                        <a href="unfunded.php">Unfunded</a>
                        <a href="admin_dashboard.php">Dashboard</a>
                        <a href="/admin/row/index.html" target="_blank">Row 1</a>
                        <a href="/admin/row2/index.html" target="_blank">Row 2</a>
                        <a href="/admin/row3/index.html" target="_blank">Row 3</a>
                        <a href="/admin/row4/index.html" target="_blank">Row 4</a>
                    </div>
                </div> -->

                <!-- Communication -->
                <!-- <div class="dropdown">
                    <button class="dropbtn"><i class="fas fa-share"></i> Communication <i class="fa fa-caret-down"></i></button>
                    <div class="dropdown-content">
                        <a href="incoming.php">Incoming</a>
                        <a href="outgoing.php">Outgoing</a>
                        <a href="planningsection.php">Planning</a>
                        <a href="test.php">Test</a>
                        <a href="documents.php">Documents</a>
                        <a href="/admin/indexdocs_cside.php" target="_blank">Documents 2.0</a>
                    </div>
                </div> -->

                <!-- User -->
                <div class="dropdown">
                    <button class="dropbtn"><i class="fas fa-cog"></i>Settings <i class="fa fa-caret-down"></i></button>
                    <div class="dropdown-content">
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>

            <!-- Hamburger icon -->
            <a href="javascript:void(0);" class="icon" onclick="toggleNavbar()">
                <i class="fa fa-bars"></i>
            </a>
        </div>

        <script>
            function toggleNavbar() {
                const navbar = document.getElementById("myTopnav");
                navbar.classList.toggle("responsive");
            }
        </script>

    </body>

    </html>

