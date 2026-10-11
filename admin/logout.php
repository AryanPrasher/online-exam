<?php
require __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
redirect('/admin/login.php');
