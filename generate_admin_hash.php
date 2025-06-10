<?php
$password = 'password1';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password hash for 'admin1': $hash\n";
?>