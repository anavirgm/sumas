<?php
session_start();
include("config/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = trim($_POST['nombre']);

    if (!empty($nombre_usuario)) {
        // Convertir el nombre a mayúsculas
        $nombre_usuario = strtoupper($nombre_usuario);

        // Verificar si el usuario ya existe
        $sql = "SELECT id FROM usuarios WHERE nombre_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si el usuario existe, obtener su ID
            $usuario = $result->fetch_assoc();
            $usuario_id = $usuario['id'];
        } else {
            // Si el usuario no existe, crearlo
            $sql = "INSERT INTO usuarios (nombre_usuario) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $nombre_usuario);
            $stmt->execute();
            $usuario_id = $stmt->insert_id;
        }

        // Guardar el ID del usuario en la sesión y redirigir
        $_SESSION['usuario_id'] = $usuario_id;
        header("Location: pagina1.php");
        exit();
    } else {
        echo "Por favor, ingresa un nombre válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Juego de Sumas para niños donde pueden practicar.">
    <title>Juego de Sumas - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Finger+Paint&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Finger Paint", sans-serif;
            background-image: url('img/fondo.JPG');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .login-container {
            background-color: #B3DAF1;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px; /* Hacerlo más grande */
        }
        .login-container h1 {
            color: #0077B6;
            font-size: 50px; /* Aumentar el tamaño del título */
            margin-bottom: 20px;
        }
        .input-field {
            width: 90%;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #A6C1E1;
            border-radius: 10px;
            outline: none;
            margin-bottom: 20px;
            background-color: #E0F2FF;
            color: #0077B6;
            text-align: center;
            font-family: "Finger Paint", sans-serif;
        }
        .boton {
            width: 100%;
            padding: 15px;
            font-size: 20px; /* Aumentar el tamaño del botón */
            font-weight: bold;
            color: #0077B6;
            background-color: #B3DAF1;
            border: 3px solid #A6C1E1;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-family: "Finger Paint", sans-serif;
        }
        .boton:hover {
            background-color: #A0C9E8;
            transform: scale(1.05);
        }

        /* Media Queries para hacerlo responsive */
        @media (max-width: 768px) {
            .login-container {
                width: 80%; /* Ajusta el tamaño del contenedor para pantallas medianas */
                padding: 30px;
            }
            .login-container h1 {
                font-size: 24px; /* Reducir tamaño del título en pantallas pequeñas */
            }
            .input-field {
                font-size: 16px; /* Reducir tamaño de la fuente del campo de entrada */
                padding: 12px;
            }
            .boton {
                font-size: 18px; /* Reducir tamaño del botón */
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 90%; /* Contenedor más pequeño en pantallas muy pequeñas */
                padding: 25px;
            }
            .login-container h1 {
                font-size: 20px; /* Título más pequeño */
            }
            .input-field {
                font-size: 14px; /* Reducir más el tamaño del campo de entrada */
                padding: 10px;
            }
            .boton {
                font-size: 16px; /* Botón más pequeño */
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Bienvenido</h1>
        <form action="index.php" method="POST">
            <input type="text" name="nombre" placeholder="¡Hola Pequeño, Ingresa tu nombre!" class="input-field" required>
            <button type="submit" class="boton">Entrar</button>
        </form>
    </div>

    <audio id="button-sound" src="assets/click.mp3"></audio>

    <script>
        // Configuración de sonido de click
        const buttonSound = document.getElementById('button-sound');
        function playClickSound() {
            buttonSound.currentTime = 0; // Reinicia el audio si ya se estaba reproduciendo
            buttonSound.play().catch((error) => {
                console.error("Error al reproducir el sonido:", error);
            });
        }

        // Agregar evento de click a todos los botones interactivos
        document.querySelectorAll("button, a").forEach((element) => {
            element.addEventListener("click", playClickSound);
        });
    </script>

</body>
</html>