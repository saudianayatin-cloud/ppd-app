<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// echo "<h2>Welcome, " . $_SESSION['user'] . " (" . $_SESSION['role'] . ")</h2>";

// Example role restrictions
if ($_SESSION['role'] == 'admin') {
    // echo "<a href='register.php'>Add New User</a><br>";
    // echo "<a href='viewdocs2.php'>Add Viewer2</a><br>";
    header("Location: admin.php");
    exit;

}

if ($_SESSION['role'] == 'incoming') {
    header("Location: indexdocs_cside.php");
    exit;
}
if ($_SESSION['role'] == 'outgoing') {
    header("Location: outgoingdocs.php");
    exit;
}
if ($_SESSION['role'] == 'planning') {
    header("Location: indexdocs_cside_funded2.php");
    exit;
}
if ($_SESSION['role'] == 'programming') {
    header("Location: indexdocs_cside_funded2.php");
    exit;
}
if ($_SESSION['role'] == 'environmental') {
    header("Location: environmentaldocs.php");
    exit;
}
if ($_SESSION['role'] == 'ebarmm') {
    header("Location: indexdocs_cside_fundedgis.php");
    exit;
}

if ($_SESSION['role'] == 'viewer') {
    header("location: viewdocs.php");
    exit;
}
if ($_SESSION['role'] == 'unfunded') {
    header("location: unfunded_cside_status.php");
    exit;
}


echo "<a href='logout.php'>Logout</a>";
