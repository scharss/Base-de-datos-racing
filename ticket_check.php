<?php

// Mostrar todos los errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Conexi¨®n a la base de datos
$servername = $_ENV['DB_SERVER'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];

    if ($type === 'ticket') {
        $ticket = $conn->real_escape_string($_POST['ticket']);

        // Consulta para obtener los detalles del pedido basado en el n¨²mero de ticket y mostrar todos los estados
        $sql = "SELECT p.ID AS order_id, pm.meta_value AS customer_name, oim.meta_value AS email, oim2.meta_value AS phone, p.post_status, p.post_date
                FROM wpmd_posts p
                JOIN wpmd_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_billing_first_name'
                JOIN wpmd_postmeta oim ON p.ID = oim.post_id AND oim.meta_key = '_billing_email'
                JOIN wpmd_postmeta oim2 ON p.ID = oim2.post_id AND oim2.meta_key = '_billing_phone'
                JOIN wpmd_woocommerce_order_items oi ON p.ID = oi.order_id
                JOIN wpmd_woocommerce_order_itemmeta oim3 ON oi.order_item_id = oim3.order_item_id
                WHERE oim3.meta_key IN ('_lty_lottery_tickets', 'Ticket Number( s )')
                AND oim3.meta_value LIKE '%$ticket%'";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Mapeo de estados de pedidos a descripciones legibles
            $order_status = array(
                'wc-pending' => 'Pendiente de pago',
                'wc-processing' => 'En proceso',
                'wc-on-hold' => 'En espera',
                'wc-completed' => 'Completado',
                'wc-cancelled' => 'Cancelado',
                'wc-refunded' => 'Reembolsado',
                'wc-failed' => 'Fallido'
            );

            $status = isset($order_status[$row['post_status']]) ? $order_status[$row['post_status']] : 'Estado desconocido';
            $order_date = date('d-m-Y H:i:s', strtotime($row['post_date']));

            echo "ID de Pedido: " . $row['order_id'] . "<br>";
            echo "Estado del Pedido: " . $status . "<br>";
            echo "Fecha y Hora del Pedido: " . $order_date . "<br>";
            echo "Nombre del Cliente: " . $row['customer_name'] . "<br>";
            echo "Correo: " . $row['email'] . "<br>";
            echo "Celular: " . $row['phone'] . "<br>";
            
        } else {
            echo "<div class='alert alert-warning' role='alert'>El ticket no existe.</div>";
        }
    }
}

$conn->close();

?>
