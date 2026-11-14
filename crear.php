<?php

session_start();

// Si guardar.php encontro errores aqui recuperamos los mensajes
// y lo que la persona habia escrito para no hacerla empezar de cero
$errores = $_SESSION['errores'] ?? [];
$datos   = $_SESSION['datos'] ?? [];
unset($_SESSION['errores'], $_SESSION['datos']);

// Escapa texto antes de mostrarlo en HTML
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

$titulo  = $datos['titulo'] ?? '';
$isbn    = $datos['isbn'] ?? '';
$anio    = $datos['ano_publicacion'] ?? '';
$idioma  = $datos['idioma'] ?? '';
$paginas = $datos['numero_paginas'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioExpress - Nuevo libro</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <h1>📚 BiblioExpress</h1>
        <p>Registrar un libro nuevo</p>
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
            <h2>Datos del libro</h2>

            <form action="guardar.php" method="post">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" maxlength="150" required
                       value="<?= e($titulo) ?>">

                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn" maxlength="50" required
                       placeholder="Ej: 978-0132350884" value="<?= e($isbn) ?>">

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
                    <button type="submit" class="btn btn-verde">Guardar libro</button>
                    <a href="index.php" class="btn btn-gris">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer>BiblioExpress · Taller 1 · CRUD con PHP y PDO</footer>
</body>
</html>
