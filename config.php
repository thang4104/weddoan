<?php
$conn = new mysqli('localhost', 'root', '', 'video_db');
if ($conn->connect_error) die('Lỗi kết nối CSDL: ' . $conn->connect_error);