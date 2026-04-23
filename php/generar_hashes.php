<?php
$usuarios = [
    'James'   => 'james',
    'Rodrigo' => 'Rodrigo',
    'Andre'   => 'Andre',
    'Angel'   => 'Angel',
];

foreach ($usuarios as $username => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    echo "UPDATE USUARIO SET PASSWORD_HASH = '$hash' WHERE USERNAME = '$username';<br>";
}
?>
