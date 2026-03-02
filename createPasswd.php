<?php
    // Archivo temporal para crear contraseñas y usuarios
    $passwd = "cli123";
    echo password_hash($passwd, PASSWORD_DEFAULT);

?>