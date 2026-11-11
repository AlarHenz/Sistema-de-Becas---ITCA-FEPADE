<?php
// public/logout.php
session_start();
session_unset();
session_destroy();
header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/login.php');
exit();
?>