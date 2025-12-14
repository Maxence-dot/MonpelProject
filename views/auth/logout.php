<?php
// Logout controller in views for organization.
session_start();
session_destroy();
header('Location: /MonpelProject/login.php');
exit;
