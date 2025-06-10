<?php
$new_password = 'admin123'; // Choose your desired password
echo password_hash($new_password, PASSWORD_BCRYPT);
?>  