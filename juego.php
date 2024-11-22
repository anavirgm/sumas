<?php
session_start();
include("config/conexion.php");

// Suponemos que el nivel ha sido pasado como parámetro en la URL
$nivel_id = isset($_GET['nivel']) ? intval($_GET['nivel']) : 0;
$usuario_id = $_SESSION['usuario_id']; // El ID del usuario logueado

// Obtener la operación fija asociada al nivel
$sql_nivel = "SELECT operacion, respuesta FROM niveles WHERE id = ?";
$stmt_nivel = $conn->prepare($sql_nivel);
$stmt_nivel->bind_param("i", $nivel_id);
$stmt_nivel->execute();
$result_nivel = $stmt_nivel->get_result();

// Verificar si existe el nivel
if ($result_nivel->num_rows > 0) {
    $row_nivel = $result_nivel->fetch_assoc();
    $operacion = $row_nivel['operacion'];
    $respuesta = $row_nivel['respuesta'];
} else {
    die("Nivel no encontrado.");
}

// Obtener la respuesta guardada en progreso si existe
$sql_progreso = "SELECT respuesta FROM progreso WHERE usuario_id = ? AND nivel_id = ?";
$stmt_progreso = $conn->prepare($sql_progreso);
$stmt_progreso->bind_param("ii", $usuario_id, $nivel_id);
$stmt_progreso->execute();
$result_progreso = $stmt_progreso->get_result();

$respuesta_guardada = null; // Por defecto, no hay respuesta previa
if ($result_progreso->num_rows > 0) {
    $row_progreso = $result_progreso->fetch_assoc();
    $respuesta_guardada = $row_progreso['respuesta'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Juego de Sumas para niños donde pueden practicar.">
    <title>Juego de Sumas</title>
    <link href="https://fonts.googleapis.com/css2?family=Finger+Paint&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: "Finger Paint", sans-serif;
            background-image: url('img/suma.jpg');
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

        .contenedor {
            width: 100vw;
            height: 100vh;
            position: relative;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
        }


        #operacion {
            position: absolute;
            top: 30%; 
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            font-size: 40px;
            color: white;
            background-color: transparent;
            border-radius: 10px;
            text-align: center;
            width: 80%; 
            max-width: 300px;  
        }

        .operacion-columna {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 70px;
            font-weight: bold;
            color: black;
            background-color: transparent;
            border-radius: 10px;
        }

        .linea {
            width: 80%;
            border: none;
            border-top: 3px solid black;
            margin: 10px 0;
        }

        #teclado {
            position: absolute;
            top: 70%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: grid;
            grid-template-columns: repeat(6, 80px);
            grid-gap: 10px;
            z-index: 5;
        }


        .tecla {
            width: 80px;
            height: 80px;
            background-color: #0077B6;
            color: white;
            font-size: 30px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            cursor: pointer;
        }

        .tecla:hover {
            background-color: #005bb5; 
        }

        .modal {
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


        #redireccionar-btn {
            position: absolute;
            bottom: 3%; 
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 30px;
            background-color: #0073e6; 
            color: white; 
            border: none;
            border-radius: 10px;
            font-size: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: "Finger Paint", sans-serif;
        }

        #redireccionar-btn:hover {
            background-color: #005bb5; 
        }


        .hidden {
            display: none;
        }

        .top-bar {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 999;
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
            z-index: 1001;
        }


        .config-button:hover .config-menu {
            display: block;
        }

    </style>
</head>
<body>


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



    <div class="contenedor" id="game-container">
        <div id="operacion">
            <div class="operacion-columna">
                <span class="numero"><?php echo explode('+', $operacion)[0]; ?></span>
                <span class="numero"><?php echo explode('+', $operacion)[1]; ?></span>
                <hr class="linea">
                <span id="respuesta"><?php echo $respuesta_guardada !== null ? $respuesta_guardada : "0"; ?></span>
            </div>
        </div>
        <div id="teclado">
            <!-- Teclado -->
            <div class="tecla" onclick="agregarNumero(1)">1</div>
            <div class="tecla" onclick="agregarNumero(2)">2</div>
            <div class="tecla" onclick="agregarNumero(3)">3</div>
            <div class="tecla" onclick="agregarNumero(4)">4</div>
            <div class="tecla" onclick="agregarNumero(5)">5</div>
            <div class="tecla" onclick="borrar()">⌫</div>
            <div class="tecla" onclick="agregarNumero(6)">6</div>
            <div class="tecla" onclick="agregarNumero(7)">7</div>
            <div class="tecla" onclick="agregarNumero(8)">8</div>
            <div class="tecla" onclick="agregarNumero(9)">9</div>
            <div class="tecla" onclick="agregarNumero(0)">0</div>
            <div class="tecla" onclick="verificarRespuesta()">✔</div>
        </div>
        <button id="redireccionar-btn" onclick="history.back()">Regresar</button>
    </div>


    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close hidden">&times;</span> <!-- X de cierre -->
            <div class="modal-message" id="modal-message">
            </div>
        </div>
    </div>

    <!-- Audio para los botones -->
    <audio id="button-sound" src="assets/click.mp3"></audio>

    <!-- Audio de fondo -->
    <audio id="background-music" src="assets/Happy_Foods.mp3" autoplay loop></audio>

    <script>

        const buttonSound = document.getElementById("button-sound");

        // Función para reproducir el sonido de "click"
        function playClickSound() {
            buttonSound.currentTime = 0; // Reinicia el audio si ya se estaba reproduciendo
            buttonSound.play().catch((error) => {
                console.error("Error al reproducir el sonido:", error);
            });
        }

        // Asociar la función a los botones del teclado y otros elementos interactivos
        document.querySelectorAll(".tecla, #redireccionar-btn, .next-button, .config-button, .mute-button").forEach((element) => {
            element.addEventListener("click", playClickSound);
        });
        
        const bgMusic = document.getElementById('background-music');
        const muteButton = document.getElementById('mute-button');
        const muteIcon = document.getElementById('mute-icon');

        // Verificar si el audio estaba previamente desactivado
        let isMuted = localStorage.getItem('isMuted') === 'true';
        bgMusic.muted = isMuted;
        muteIcon.src = isMuted ? 'img/mute.png' : 'img/sound.png';

        // Obtener el tiempo guardado desde localStorage
        let savedTime = localStorage.getItem('audioTime');
        if (savedTime) {
            bgMusic.currentTime = parseFloat(savedTime);
        }

        // Guardar el tiempo del audio cuando se cambia de página
        bgMusic.ontimeupdate = function() {
            localStorage.setItem('audioTime', bgMusic.currentTime);
        };

        // Función para alternar el estado de sonido
        function toggleMute() {
            playClickSound();
            isMuted = !isMuted;
            bgMusic.muted = isMuted;
            muteIcon.src = isMuted ? 'img/mute.png' : 'img/sound.png';

            // Guardar el estado de mute en localStorage
            localStorage.setItem('isMuted', isMuted.toString());
        }

        const respuestaCorrecta = <?php echo $respuesta; ?>;
        const nivelId = <?php echo $nivel_id; ?>;
        const respuestaInicial = "<?php echo $respuesta_guardada !== null ? $respuesta_guardada : "0"; ?>";

        // Cargar respuesta inicial
        document.getElementById("respuesta").textContent = respuestaInicial;

        function agregarNumero(num) {
            let respuestaCampo = document.getElementById("respuesta");
            let respuestaActual = respuestaCampo.textContent;
            if (respuestaActual === "0") respuestaActual = "";
            respuestaCampo.textContent = respuestaActual + num;
        }

        function borrar() {
            let respuestaCampo = document.getElementById("respuesta");
            let respuestaActual = respuestaCampo.textContent;
            respuestaCampo.textContent = respuestaActual.slice(0, -1) || "0";
        }

        function verificarRespuesta() {
            let respuestaCampo = document.getElementById("respuesta").textContent;
            let mensaje, gif, sonido;

            if (parseInt(respuestaCampo) === respuestaCorrecta) {
                mensaje = "¡Correcto! ¡Bien hecho!";
                gif = "feliz.GIF"; 
                sonido = "correcto.mp3"; 
                guardarProgreso();

                // Ocultar la "X" de cierre del modal
                document.querySelector(".close").classList.add("hidden");

                // Redirigir al menú principal después de un pequeño retraso para que el modal sea visible
                setTimeout(function() {
                    history.back();  // Retrocede en el historial de páginas
                }, 4000); // 4 segundos para que el modal se cierre y luego redirigir
            } else {
                mensaje = "Incorrecto, intenta nuevamente.";
                gif = "triste.GIF";
                sonido = "incorrecto.mp3";

                // "X" visible cuando la respuesta sea incorrecta
                document.querySelector(".close").classList.remove("hidden");
            }

            mostrarModal(mensaje, gif, sonido);
        }


        function cerrarModal() {
            document.getElementById("modal").style.display = "none";
        }

        // Función para mostrar el modal con un mensaje
        function mostrarModal(mensaje, gif, sonido) {
            // Establece el mensaje
            document.getElementById("modal-message").textContent = mensaje;

            // Muestra el modal
            document.getElementById("modal").style.display = "flex"; 

            // Añade el GIF al modal
            let modalContent = document.getElementById("modal-message");
            let gifImage = document.createElement("img");
            gifImage.src = "assets/" + gif;
            gifImage.style.width = "300px";
            gifImage.style.marginTop = "20px";
            modalContent.appendChild(gifImage);

            // Reproduce el sonido correspondiente
            let audio = new Audio("assets/" + sonido);
            audio.play();
        }

        document.querySelector(".close").addEventListener("click", cerrarModal);




        function guardarProgreso() {
            fetch("config/guardar_progreso.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `usuario_id=<?php echo $usuario_id; ?>&nivel_id=${nivelId}&completado=1`
            });
        }


        
    </script>
</body>
</html>