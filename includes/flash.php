<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function flash($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

function show_flash()
{
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $msg) {

            $color = ($type === 'success') ? 'green' : 'red';

            echo "<div style='
                padding: 10px;
                margin: 10px 0;
                border-left: 5px solid $color;
                background: #f4f4f4;'>
                <b>$msg</b>
            </div>";
        }
        unset($_SESSION['flash']);
    }
}
