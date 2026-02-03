<?php
require_once 'includes/bootstrap.php';

$auth->logout();
redirect(BASE_URL . '/login.php');