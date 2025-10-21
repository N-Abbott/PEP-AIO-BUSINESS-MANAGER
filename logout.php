<?php
session_start();
session_destroy();
header("Location: PEP_Main.php");
exit;
?>