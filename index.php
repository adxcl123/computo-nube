<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Registro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            background-color: #fff;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: rgb(175, 76, 167);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: rgb(160, 69, 160);
        }
        .success-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        .error-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #ffebee;
            border-left: 4px solid #f44336;
        }
        .db-status {
            margin-top: 20px;
            padding: 15px;
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        .db-status.success {
            background-color: #e8f5e9;
            border-left-color: #4caf50;
        }
        .db-status.error {
            background-color: #ffebee;
            border-left-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Registro de Usuario</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="primer_apellido">Primer Apellido:</label>
                <input type="text" id="primer_apellido" name="primer_apellido" required>
            </div>
            
            <div class="form-group">
                <label for="segundo_apellido">Segundo Apellido:</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido">
            </div>
            
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            
            <input type="submit" name="enviar" value="Enviar">
        </form>

        <?php
        // Configuración de conexión para Azure SQL Database
        $serverName = getenv('SQLSRV_SERVER') ?: "bdserversql.database.windows.net";
        $database = getenv('SQLSRV_DATABASE') ?: "bdsql01";
        $username = getenv('SQLSRV_USERNAME') ?: "adminsql";
        $password = getenv('SQLSRV_PASSWORD') ?: "Servid0r1";

        $dbConnected = false;
        $dbError = "";
        $lastInsertId = null;

        try {
            $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
            $dbConnected = true;
            
            // Crear tabla si no existe
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='usuarios' AND xtype='U')
                    CREATE TABLE usuarios (
                        id INT IDENTITY(1,1) PRIMARY KEY,
                        nombre NVARCHAR(50) NOT NULL,
                        primer_apellido NVARCHAR(50) NOT NULL,
                        segundo_apellido NVARCHAR(50),
                        correo NVARCHAR(100) NOT NULL,
                        telefono NVARCHAR(20) NOT NULL,
                        fecha_registro DATETIME DEFAULT GETDATE()
                    )";
            $conn->exec($sql);
            
        } catch (PDOException $e) {
            $dbError = $e->getMessage();
            echo "<div class='error-message'>";
            echo "<h3>Error de conexión a la base de datos:</h3>";
            echo "<p>" . htmlspecialchars($dbError) . "</p>";
            echo "</div>";
        }

        // Procesamiento del formulario
        if (isset($_POST['enviar'])) {
            if ($dbConnected) {
                try {
                    // Insertar datos en la base de datos
                    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, primer_apellido, segundo_apellido, correo, telefono) 
                                           VALUES (:nombre, :primer_apellido, :segundo_apellido, :correo, :telefono)");
                    
                    $stmt->bindParam(':nombre', $_POST['nombre']);
                    $stmt->bindParam(':primer_apellido', $_POST['primer_apellido']);
                    $stmt->bindParam(':segundo_apellido', $_POST['segundo_apellido']);
                    $stmt->bindParam(':correo', $_POST['correo']);
                    $stmt->bindParam(':telefono', $_POST['telefono']);
                    
                    $stmt->execute();
                    $lastInsertId = $conn->lastInsertId();
                    
                    // Mostrar mensaje de éxito
                    echo "<div class='success-message'>";
                    echo "<h3>Datos Guardados Correctamente:</h3>";
                    echo "<p><strong>Nombre:</strong> " . htmlspecialchars($_POST['nombre']) . "</p>";
                    echo "<p><strong>Primer Apellido:</strong> " . htmlspecialchars($_POST['primer_apellido']) . "</p>";
                    echo "<p><strong>Segundo Apellido:</strong> " . htmlspecialchars($_POST['segundo_apellido']) . "</p>";
                    echo "<p><strong>Correo:</strong> " . htmlspecialchars($_POST['correo']) . "</p>";
                    echo "<p><strong>Teléfono:</strong> " . htmlspecialchars($_POST['telefono']) . "</p>";
                    echo "</div>";
                    
                    // Estado de la base de datos
                    echo "<div class='db-status success'>";
                    echo "<h3>Estado de la Base de Datos:</h3>";
                    echo "<p>✅ Los datos se guardaron correctamente en la base de datos.</p>";
                    echo "<p><strong>ID del registro:</strong> " . htmlspecialchars($lastInsertId) . "</p>";
                    echo "</div>";
                    
                } catch(PDOException $e) {
                    echo "<div class='error-message'>";
                    echo "<h3>Error al guardar los datos:</h3>";
                    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                    
                    echo "<div class='db-status error'>";
                    echo "<h3>Estado de la Base de Datos:</h3>";
                    echo "<p>❌ Error al guardar en la base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='db-status error'>";
                echo "<h3>Estado de la Base de Datos:</h3>";
                echo "<p>❌ No se pudo conectar a la base de datos: " . htmlspecialchars($dbError) . "</p>";
                echo "</div>";
            }
        }
        
        // Mostrar estado de conexión si no se ha enviado el formulario
        if (!isset($_POST['enviar']) && $dbConnected) {
            echo "<div class='db-status'>";
            echo "<h3>Estado de la Base de Datos:</h3>";
            echo "<p>🟢 Conectado correctamente a la base de datos</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>