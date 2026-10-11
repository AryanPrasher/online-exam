<?php
require __DIR__ . '/includes/functions.php';
unset($_SESSION['student_id'], $_SESSION['student_name']);
redirect('/login.php');
