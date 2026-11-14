<?php

session_start();
require_once 'conexion.php';

// ---------- Funciones auxiliares ----------

// Lee un campo de $_POST como texto sin espacios sobrantes
function campo($nombre)
{
    $valor = $_POST[$nombre] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

// Cuenta caracteres no bytes, para que las tildes no cuenten doble
function largo($texto)
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

// Guarda los errores y lo escrito en la sesión y regresa al formulario
function volver($destino, $errores, $datos)
{
    $_SESSION['errores'] = $errores;
    $_SESSION['datos']   = $datos;
    header('Location: ' . $destino);
    exit;
}

// ---------- Solo se acepta el envio del formulario por POST ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ----------  Recibir los datos ----------
$id      = campo('id_libro');          // vacio = libro nuevo, con valor = edicion
$titulo  = campo('titulo');
$isbn    = campo('isbn');
$anio    = campo('ano_publicacion');
$idioma  = campo('idioma');
$paginas = campo('numero_paginas');

$esEdicion = ($id !== '');

if ($esEdicion && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
    $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'El identificador del libro no es válido.'];
    header('Location: index.php');
    exit;
}

// Si algo falla, se regresa al formulario correspondiente
$destino = $esEdicion ? 'editar.php?id_libro=' . (int) $id : 'crear.php';

// Lo que la persona escribio, para volver a mostrarlo si hay errores
$datos = [
    'titulo'          => $titulo,
    'isbn'            => $isbn,
    'ano_publicacion' => $anio,
    'idioma'          => $idioma,
    'numero_paginas'  => $paginas,
];

// ----------  Validaciones basicas ----------
$errores = [];

if ($titulo === '') {
    $errores[] = 'El título es obligatorio.';
} elseif (largo($titulo) > 150) {
    $errores[] = 'El título no puede tener más de 150 caracteres.';
}

if ($isbn === '') {
    $errores[] = 'El ISBN es obligatorio.';
} elseif (largo($isbn) > 50) {
    $errores[] = 'El ISBN no puede tener más de 50 caracteres.';
}

$anioActual = (int) date('Y');
if ($anio === '') {
    $errores[] = 'El año de publicación es obligatorio.';
} elseif (filter_var($anio, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1501, 'max_range' => $anioActual]]) === false) {
    $errores[] = "El año de publicación debe ser un número entero entre 1501 y $anioActual.";
}

if ($idioma === '') {
    $errores[] = 'El idioma es obligatorio.';
} elseif (largo($idioma) > 50) {
    $errores[] = 'El idioma no puede tener más de 50 caracteres.';
}

if ($paginas === '') {
    $errores[] = 'El número de páginas es obligatorio.';
} elseif (filter_var($paginas, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]) === false) {
    $errores[] = 'El número de páginas debe ser un número entero mayor que 0.';
}

if (!empty($errores)) {
    volver($destino, $errores, $datos);
}

// ----------  Guardar con sentencias preparadas ----------
try {
    if ($esEdicion) {
        // UPDATE, viene de editar.php
        $sql = 'UPDATE libro
                SET titulo = :titulo, isbn = :isbn, ano_publicacion = :anio,
                    idioma = :idioma, numero_paginas = :paginas
                WHERE id_libro = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo'  => $titulo,
            ':isbn'    => $isbn,
            ':anio'    => (int) $anio,
            ':idioma'  => $idioma,
            ':paginas' => (int) $paginas,
            ':id'      => (int) $id,
        ]);
        $texto = 'Libro actualizado correctamente.';
    } else {
        // INSERT, viene de crear.php
        $sql = 'INSERT INTO libro (titulo, isbn, ano_publicacion, idioma, numero_paginas)
                VALUES (:titulo, :isbn, :anio, :idioma, :paginas)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo'  => $titulo,
            ':isbn'    => $isbn,
            ':anio'    => (int) $anio,
            ':idioma'  => $idioma,
            ':paginas' => (int) $paginas,
        ]);
        $texto = 'Libro registrado correctamente.';
    }

    $_SESSION['mensaje'] = ['tipo' => 'ok', 'texto' => $texto];
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    // 1062 = ISBN repetido en MYSQL/mariadb, 23505 = violacion de UNIQUE en postgreSQL
    $codigoMotor = $e->errorInfo[1] ?? null;
    if ($codigoMotor == 1062 || $e->getCode() == '23505') {
        $errores[] = 'Ya existe un libro con ese ISBN.';
    } else {
        error_log('BiblioExpress - error al guardar: ' . $e->getMessage());
        $errores[] = 'No se pudo guardar el libro. Intenta de nuevo.';
    }
    volver($destino, $errores, $datos);
}
