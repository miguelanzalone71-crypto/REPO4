<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM articulos WHERE id = ?');
            $stmt->execute([(int)$_GET['id']]);
            $item = $stmt->fetch();
            if (!$item) json_response(['error' => 'Artículo no encontrado'], 404);
            json_response($item);
        }
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $stmt = $pdo->prepare('SELECT * FROM articulos WHERE nombre LIKE ? OR marca LIKE ? OR ubicacion LIKE ? ORDER BY id DESC');
            $like = '%' . $q . '%';
            $stmt->execute([$like, $like, $like]);
            json_response($stmt->fetchAll());
        }
        $stmt = $pdo->query('SELECT * FROM articulos ORDER BY id DESC');
        json_response($stmt->fetchAll());
    }

    if ($method === 'POST' || $method === 'PUT') {
        $data = request_data();
        $nombre = trim((string)($data['nombre'] ?? ''));
        $marca = trim((string)($data['marca'] ?? ''));
        $ubicacion = trim((string)($data['ubicacion'] ?? ''));
        $unidadesRaw = $data['unidades'] ?? null;

        if ($nombre === '' || $marca === '' || $ubicacion === '' || $unidadesRaw === null ||
            filter_var($unidadesRaw, FILTER_VALIDATE_INT) === false || (int)$unidadesRaw < 0) {
            json_response(['error' => 'Completa nombre, marca, ubicación y unidades (entero igual o mayor que 0).'], 422);
        }
        $unidades = (int)$unidadesRaw;

        if ($method === 'POST') {
            $stmt = $pdo->prepare('INSERT INTO articulos (nombre, marca, unidades, ubicacion) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nombre, $marca, $unidades, $ubicacion]);
            json_response(['mensaje' => 'Artículo creado correctamente', 'id' => (int)$pdo->lastInsertId()], 201);
        }

        $id = (int)($_GET['id'] ?? $data['id'] ?? 0);
        if ($id <= 0) json_response(['error' => 'Indica un ID válido para actualizar.'], 400);
        $stmt = $pdo->prepare('UPDATE articulos SET nombre=?, marca=?, unidades=?, ubicacion=? WHERE id=?');
        $stmt->execute([$nombre, $marca, $unidades, $ubicacion, $id]);
        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM articulos WHERE id=?');
            $check->execute([$id]);
            if (!$check->fetch()) json_response(['error' => 'Artículo no encontrado'], 404);
        }
        json_response(['mensaje' => 'Artículo actualizado correctamente']);
    }

    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) json_response(['error' => 'Indica un ID válido.'], 400);
        $stmt = $pdo->prepare('DELETE FROM articulos WHERE id=?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) json_response(['error' => 'Artículo no encontrado'], 404);
        json_response(['mensaje' => 'Artículo eliminado correctamente']);
    }

    json_response(['error' => 'Método no permitido'], 405);
} catch (Throwable $e) {
    error_log((string)$e);
    json_response(['error' => 'Error del servidor o de conexión a la base de datos. Verifica la configuración MySQL y los permisos.'], 500);
}
