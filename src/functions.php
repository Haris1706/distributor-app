<?php
// src/functions.php

require_once __DIR__ . '/config.php';

/**
 * Melakukan proses login berdasarkan email dan password.
 * Mengembalikan data pengguna jika berhasil, atau null jika gagal.
 */
function login(string $email, string $password): ?array
{
  $conn  = koneksi();
  $email = mysqli_real_escape_string($conn, $email);

  $query  = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
  $result = mysqli_query($conn, $query);
  $user   = mysqli_fetch_assoc($result);

  if ($user && password_verify($password, $user['password'])) {
    return $user;
  }

  return null;
}

/**
 * Mengelola koneksi database tunggal per request.
 * Menggunakan koneksi statis agar tidak membuka koneksi berulang.
 */
function db(): mysqli
{
  static $conn = null;

  if ($conn === null) {
    $conn = koneksi();
    register_shutdown_function(function () use (&$conn) {
      if ($conn) {
        mysqli_close($conn);
        $conn = null;
      }
    });
  }

  return $conn;
}

/**
 * Menentukan tipe parameter untuk bind_param() pada prepared statement.
 * 'i' untuk integer, 'd' untuk float, 's' untuk string.
 */
function infer_types(array $params): string
{
  $types = '';
  foreach ($params as $p) {
    if (is_int($p))       $types .= 'i';
    elseif (is_float($p)) $types .= 'd';
    else                  $types .= 's';
  }
  return $types;
}

/**
 * Menyiapkan dan mengeksekusi query SQL dengan prepared statement.
 * Mengembalikan array berisi status, hasil, jumlah baris terpengaruh, error, dan ID insert.
 */
function prepare_and_execute(string $sql, array $params = []): array
{
  $conn = db();
  try {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
      throw new Exception(mysqli_error($conn));
    }

    if (!empty($params)) {
      $types = infer_types($params);
      $refs  = [];
      foreach ($params as $k => $v) {
        $refs[$k] = &$params[$k];
      }
      array_unshift($refs, $types);
      call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    mysqli_stmt_execute($stmt);

    $result    = mysqli_stmt_get_result($stmt);
    $affected  = mysqli_stmt_affected_rows($stmt);
    $insert_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

    return [true, $result, $affected, null, $insert_id];
  } catch (Throwable $e) {
    error_log("SQL Error: {$e->getMessage()} | Query: $sql");
    return [false, null, 0, $e->getMessage(), 0];
  }
}

/**
 * Mengambil banyak baris data dari hasil query.
 * Cocok untuk SELECT yang mengembalikan banyak hasil.
 */
function queryAll(string $sql, array $params = []): array
{
  [$ok, $result,, $err] = prepare_and_execute($sql, $params);
  if (!$ok) {
    throw new Exception("Query failed: $err");
  }

  $rows = [];
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
    }
    mysqli_free_result($result);
  }

  return $rows;
}

/**
 * Mengambil satu baris data dari hasil query.
 * Cocok untuk SELECT dengan LIMIT 1 atau data tunggal.
 */
function queryOne(string $sql, array $params = []): ?array
{
  $rows = queryAll($sql, $params);
  return $rows[0] ?? null;
}

/**
 * Menjalankan operasi tulis ke database (INSERT, UPDATE, DELETE).
 * Mengembalikan status eksekusi, jumlah baris terpengaruh, ID insert, dan pesan error.
 */
function execute(string $sql, array $params = []): array
{
  [$ok,, $affected, $err, $insert_id] = prepare_and_execute($sql, $params);
  return [
    'ok'            => $ok,
    'affected_rows' => $affected,
    'insert_id'     => $insert_id,
    'error'         => $err
  ];
}

/**
 * Alias untuk queryAll() agar kompatibel dengan versi lama.
 */
function query(string $sql): array
{
  return queryAll($sql);
}
