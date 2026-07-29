<?php
session_start();
session_unset(); // Saare session variables khatam
session_destroy(); // Session destroy

// Wapas login page par bhej dena
header("Location: login.php");
exit();
?>