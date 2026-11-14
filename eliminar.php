<?php

session_start();
require_once 'conexion.php';

// Escapa texto antes de mostrarlo en HTML
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

$esPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

// ----------  Validar el id viene por POST al borrar, por GET al confirmar ----------
$origen = $esPost ? ($_POST['id_libro'] ?? '') : ($_GET['id_libro'] ?? '');
$id = filter_var($origen, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'El identificador del libro no es válido.'];
    header('Location: index.php');
    exit;
}

// ----------  Si se confirmo POST: borrar ----------
if ($esPost) {
    try {
        $stmt = $pdo->prepare('DELETE FROM libro WHERE id_libro = ?');
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = ['tipo' => 'ok', 'texto' => 'Libro eliminado correctamente.'];
        } else {
            $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'El libro ya no existía.'];
        }
    } catch (PDOException $e) {
        error_log('BiblioExpress - error al eliminar: ' . $e->getMessage());
        $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se pudo eliminar el libro. Intenta de nuevo.'];
    }

    header('Location: index.php');
    exit;
}

// ---------- Si solo se abrio el enlace GET: cargar el libro y pedir confirmacion ----------
try {
    $stmt = $pdo->prepare('SELECT id_libro, titulo, isbn FROM libro WHERE id_libro = ?');
    $stmt->execute([$id]);
    $libro = $stmt->fetch();
} catch (PDOException $e) {
    error_log('BiblioExpress - error al cargar libro: ' . $e->getMessage());
    $libro = false;
}

if (!$libro) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'No se encontró el libro que intentas eliminar.'];
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioExpress - Eliminar libro</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <h1>📚 BiblioExpress</h1>
        <p>Eliminar libro</p>
    </header>

    <main>
        <section class="tarjeta confirmar">
            <h2>¿Eliminar este libro?</h2>
            <p>
                Vas a eliminar <strong><?= e($libro['titulo']) ?></strong>
                (ISBN <?= e($libro['isbn']) ?>).<br>
                Esta acción no se puede deshacer.
            </p>

            <form action="eliminar.php" method="post">
                <input type="hidden" name="id_libro" value="<?= (int) $libro['id_libro'] ?>">
                <div class="botones">
                    <button type="submit" class="btn btn-rojo">Sí, eliminar</button>
                    <a href="index.php" class="btn btn-gris">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer>BiblioExpress · Taller 1 · CRUD con PHP y PDO</footer>
</body>
</html>
