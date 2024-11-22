<?php
session_start();
include('config/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id']; // Usuario autenticado

// Consulta para obtener los niveles completados
$sql = "SELECT nivel_id FROM progreso WHERE usuario_id = ? AND completado = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$completados = [];
while ($row = $result->fetch_assoc()) {
    $completados[] = $row['nivel_id'];
}

// Verificar si se completaron todos los niveles (48 niveles)
$completo = count($completados) == 48 ? true : false;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $titulo_pagina ?? 'Juego de Sumas'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Finger+Paint&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Finger Paint", sans-serif;
            background-image: url('img/fondo.JPG');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .modal-celebracion {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 24px;
            color: #0073e6;
            width: 80%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        .close {
            color: #333;
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #FF8787;
            text-decoration: none;
            cursor: pointer;
        }

        h1 {
    font-size: 3rem;
    color: #4373dbd0;
    text-shadow: 2px 2px 4px rgb(255, 255, 255);
    margin-top: 5px;
    padding: 10px 20px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 2px 2px 10px rgba(2, 40, 255, 0.877);
}


.top-bar {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.top-bar button {
    background: none;
    border: none;
    cursor: pointer;
}

.top-bar img {
    width: 60px;
    height: 60px;
}

.config-button {
    position: relative;
}

.config-menu {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
    padding: 20px;
    z-index: 10;
}

.config-button:hover .config-menu {
    display: block;
}

.botones-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: center;
    max-width: 600px;
    margin-bottom: 30px;
}

.boton {
    width: 125px;
    height: 125px;
    font-weight: bold;
    color: #0077B6;
    background-color: #B3DAF1;
    border: 3px solid #A6C1E1;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.3s ease, background-color 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-family: "Finger Paint", sans-serif;
}

.boton:hover {
    background-color: #A0C9E8; 
    transform: scale(1.1);
}

.boton.completado {
    background-color: #8dd190;
    color: #155724;
    border: 3px solid #72bd75;
}

.back-button,
.next-button {
    position: fixed;
    background: transparent;
    color: #0077B6;
    border: none;
    padding: 0;
    border-radius: 50%;
    cursor: pointer;
    text-align: center;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease;
    top: 50%;
    transform: translateY(-50%); /* Centrado verticalmente */
}

.back-button img,
.next-button img {
    width: 100px;
    height: 70px;
}

.back-button {
    left: 80px; /* Fijo a la izquierda */
}

.next-button {
    right: 80px; /* Fijo a la derecha */
}

.back-button:hover,
.next-button:hover {
    background: #D0E7FF; /* Azul claro al hacer hover */
}

/* Media Queries para hacer la página responsiva */
@media (max-width: 768px) {
    .botones-container {
        max-width: 100%;
        gap: 15px;
    }

    .boton {
        width: 80px;
        height: 80px;
        font-size: 14px;
    }

    .back-button,
    .next-button {
        width: 50px;
        height: 50px;
    }

    .back-button img,
    .next-button img {
        width: 40px;
        height: 40px;
    }

    .next-button {
        top: 200px;
    }

    .back-button {
        top: 200px;
    }
}

@media (max-width: 480px) {
    .botones-container {
        max-width: 100%;
        gap: 10px;
    }

    .boton {
        width: 70px;
        height: 70px;
        font-size: 12px;
    }

    .back-button,
    .next-button {
        width: 45px;
        height: 45px;
    }

    .back-button img,
    .next-button img {
        width: 35px;
        height: 35px;
    }

    .next-button {
        top: 150px;
    }

    .back-button {
        top: 150px;
    }
}

    </style>
</head>
<body>
    <h1><?php echo $titulo_h1 ?? 'Menú de Niveles'; ?></h1>

    <!-- Mostrar modal de celebración si el usuario completó todos los niveles -->
    <?php if ($completo): ?>
    <div id="modal-celebracion" class="modal-celebracion">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>¡Felicidades!</h2>
            <p>Has completado todos los niveles del programa.</p>
            <img src="assets/celebracion.GIF" alt="Celebración" width="200">
            <audio id="celebracion-audio" src="assets/win.mp3" autoplay></audio>
        </div>
    </div>
    <?php endif; ?>

    <!-- Barra superior con botones -->
    <div class="top-bar">
        <button id="mute-button" onclick="toggleMute()">
            <img id="mute-icon" src="img/sound.png" alt="Sound On">
        </button>
        <div class="config-button">
            <button>
                <img src="assets/config.png" alt="Config">
            </button>
            <div class="config-menu">
                <p><a href="config/reiniciar.php" style="text-decoration: none; color: inherit;">Reiniciar ejercicios</a></p>
                <p><a href="logout.php" style="text-decoration: none; color: inherit;">Salir</a></p>
            </div>
        </div>
    </div>

    <!-- Botones para navegar -->
    <div class="navigation-buttons">
        <?php if (isset($boton_atras) && $boton_atras): ?>
            <button class="back-button" onclick="location.href='<?php echo $boton_atras; ?>'">
                <img src="assets/izq.png" alt="Volver">
            </button>
        <?php endif; ?>
        <?php if (isset($boton_siguiente) && $boton_siguiente): ?>
            <button class="next-button" onclick="location.href='<?php echo $boton_siguiente; ?>'">
                <img src="assets/der.png" alt="Siguiente">
            </button>
        <?php endif; ?>
    </div>

    <!-- Contenedor de botones de niveles -->
    <div class="botones-container">
        <?php foreach ($niveles as $nivel): ?>
            <?php $class = in_array($nivel, $completados) ? 'completado' : ''; ?>
            <button class="boton <?php echo $class; ?>" onclick="location.href='juego.php?nivel=<?php echo $nivel; ?>'">
                <?php echo $nivel; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Audio -->
    <audio id="background-music" src="assets/Happy_Foods.mp3" autoplay loop></audio>
    <audio id="button-sound" src="assets/click.mp3"></audio>

    <script>
        const bgMusic = document.getElementById('background-music');
        const muteButton = document.getElementById('mute-button');
        const muteIcon = document.getElementById('mute-icon');

        let isMuted = localStorage.getItem('isMuted') === 'true';
        bgMusic.muted = isMuted;
        muteIcon.src = isMuted ? 'img/mute.png' : 'img/sound.png';

        let savedTime = localStorage.getItem('audioTime');
        if (savedTime) {
            bgMusic.currentTime = parseFloat(savedTime);
        }

        bgMusic.ontimeupdate = function() {
            localStorage.setItem('audioTime', bgMusic.currentTime);
        };

        function toggleMute() {
            isMuted = !isMuted;
            bgMusic.muted = isMuted;
            muteIcon.src = isMuted ? 'img/mute.png' : 'img/sound.png';
            localStorage.setItem('isMuted', isMuted.toString());
        }

        function cerrarModal() {
            document.getElementById('modal-celebracion').style.display = 'none';
        }

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

        // Mostrar el modal de celebración si el usuario completó todos los niveles
        <?php if ($completo): ?>
            document.getElementById('modal-celebracion').style.display = 'flex';
        <?php endif; ?>
    </script>
</body>
</html>