<?php

session_start();
require_once 'conexion.php';

// Escapa texto antes de mostrarlo en HTML
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// ----------  Validar el id que llega por la URL ----------
$id = filter_var($_GET['id_libro'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'El identificador del libro no es válido.'];
    header('Location: index.php');
    exit;
}

// ---------- Cargar el libro desde la base de datos ----------
try {
    $stmt = $pdo->prepare('SELECT id_libro, titulo, isbn, ano_publicacion, idioma, numero_paginas
                           FROM libro
                           WHERE id_libro = ?');
    $stmt->execute([$id]);
    $libro = $stmt->fetch();
} catch (PDOException $e) {
    error_log('BiblioExpress - error al cargar libro: ' . $e->getMessage());
    $libro = false;
}

if (!$libro) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se encontró el libro que intentas editar.'];
    header('Location: index.php');
    exit;
}

// ---------- Errores de una validación anterior si los hay ----------
$errores = $_SESSION['errores'] ?? [];
$datos   = $_SESSION['datos'] ?? [];
unset($_SESSION['errores'], $_SESSION['datos']);

// Si hubo error se muestra lo que la persona escribio, y no los datos de la base de datos
$titulo  = $datos['titulo']          ?? $libro['titulo'];
$isbn    = $datos['isbn']            ?? $libro['isbn'];
$anio    = $datos['ano_publicacion'] ?? $libro['ano_publicacion'];
$idioma  = $datos['idioma']          ?? $libro['idioma'];
$paginas = $datos['numero_paginas']  ?? $libro['numero_paginas'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioExpress - Editar libro</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <h1>📚 BiblioExpress</h1>
        <p>Editar libro</p>
    </header>

    <main>
        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error">
                Corrige lo siguiente:
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="tarjeta">
            <h2>Datos del libro #<?= (int) $libro['id_libro'] ?></h2>

            <form action="guardar.php" method="post">
                <input type="hidden" name="id_libro" value="<?= (int) $libro['id_libro'] ?>">

                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" maxlength="150" required
                       value="<?= e($titulo) ?>">

                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn" maxlength="50" required
                       value="<?= e($isbn) ?>">

                <label for="ano_publicacion">Año de publicación</label>
                <input type="number" id="ano_publicacion" name="ano_publicacion"
                       min="1501" max="<?= date('Y') ?>" step="1" required
                       value="<?= e($anio) ?>">

                <label for="idioma">Idioma</label>
                <input type="text" id="idioma" name="idioma" maxlength="50" required
                       list="lista-idiomas" value="<?= e($idioma) ?>">
                <datalist id="lista-idiomas">
                    <option value="Español">
                    <option value="Inglés">
                    <option value="Francés">
                    <option value="Alemán">
                    <option value="Italiano">
                    <option value="Portugués">
                </datalist>

                <label for="numero_paginas">Número de páginas</label>
                <input type="number" id="numero_paginas" name="numero_paginas"
                       min="1" step="1" required value="<?= e($paginas) ?>">

                <div class="botones">
                    <button type="submit" class="btn btn-verde">Actualizar libro</button>
                    <a href="index.php" class="btn btn-gris">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer>BiblioExpress · Taller 1 · CRUD con PHP y PDO</footer>
</body>
</html>
