<?php
session_start();
require_once 'conexion.php';

function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

try {
    $sql = 'SELECT id_libro, titulo, isbn, ano_publicacion, idioma, numero_paginas
            FROM libro
            ORDER BY id_libro';
    $libros = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    error_log('BiblioExpress - error al listar: ' . $e->getMessage());
    $libros = [];
    $mensaje = ['tipo' => 'error', 'texto' => 'No se pudieron cargar los libros.'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioExpress - Catálogo de libros</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <h1>📚 BiblioExpress</h1>
        <p>Catálogo de libros de la biblioteca</p>
    </header>

    <main>
        <?php if ($mensaje): ?>
            <div class="mensaje mensaje-<?= e($mensaje['tipo']) ?>">
                <?= e($mensaje['texto']) ?>
            </div>
        <?php endif; ?>

        <section class="tarjeta">
            <div class="barra">
                <h2>Libros registrados (<?= count($libros) ?>)</h2>
                <a class="btn btn-verde" href="crear.php">+ Nuevo libro</a>
            </div>

            <?php if (count($libros) === 0): ?>
                <p class="vacio">Todavía no hay libros registrados.</p>
            <?php else: ?>
                <div class="tabla-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>ISBN</th>
                                <th>Año</th>
                                <th>Idioma</th>
                                <th>Páginas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($libros as $libro): ?>
                                <tr>
                                    <td><?= e($libro['id_libro']) ?></td>
                                    <td><?= e($libro['titulo']) ?></td>
                                    <td><?= e($libro['isbn']) ?></td>
                                    <td><?= e($libro['ano_publicacion']) ?></td>
                                    <td><?= e($libro['idioma']) ?></td>
                                    <td><?= e($libro['numero_paginas']) ?></td>
                                    <td class="acciones">
                                        <a class="btn btn-amarillo btn-chico"
                                           href="editar.php?id_libro=<?= (int) $libro['id_libro'] ?>">Editar</a>
                                        <a class="btn btn-rojo btn-chico"
                                           href="eliminar.php?id_libro=<?= (int) $libro['id_libro'] ?>">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>BiblioExpress · Taller 1 · CRUD con PHP y PDO</footer>
</body>
</html>
