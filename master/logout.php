<?php
// Start Generation Here
session_start();
session_unset(); // Menghapus semua variabel sesi
session_destroy(); // Menghancurkan sesi
header("Location: login.php"); // Arahkan pengguna ke halaman login
exit();
