<?php

if(!isset($_SESSION))
{
    session_start();
}
require 'connect.php';

function tt($value){
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

function tte($value){
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    exit();
}

// Перевірка виконання запиту до БД
function dbCheckError($query){
    $errInfo = $query->errorInfo();
    if ($errInfo[0] !== PDO::ERR_NONE){
        echo $errInfo[2];
        exit();
    }
    return true;
}

// Запит на отримання даних з однієї таблиці
function selectAll($table, $params = []){
    global $pdo;
    $sql = "SELECT * FROM $table";

    if(!empty($params)){
        $i = 0;
        foreach ($params as $key => $value){
            if (!is_numeric($value)){
                $value = "'".$value."'";
            }
            if ($i === 0){
                $sql = $sql . " WHERE $key=$value";
            }else{
                $sql = $sql . " AND $key=$value";
            }
            $i++;
        }
    }

    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Запит на отримання одного рядка з обраної таблиці
function selectOne($table, $params = []){
    global $pdo;
    $sql = "SELECT * FROM $table";

    if(!empty($params)){
        $i = 0;
        foreach ($params as $key => $value){
            if (!is_numeric($value)){
                $value = "'".$value."'";
            }
            if ($i === 0){
                $sql = $sql . " WHERE $key=$value";
            }else{
                $sql = $sql . " AND $key=$value";
            }
            $i++;
        }
    }

    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetch();
}

// Запис у таблицю БД
function insert($table, $params){
    global $pdo;
    $coll = implode(', ', array_keys($params));
    $mask = implode(', :', array_keys($params));
    $sql = "INSERT INTO $table ($coll) VALUES (:$mask)";
    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $pdo->lastInsertId();
}

// Оновлення рядка в таблиці
function update($table, $id, $params){
    global $pdo;
    $str = '';
    foreach ($params as $key => $value) {
        $str .= "$key = :$key, ";
    }
    $str = rtrim($str, ', ');
    $sql = "UPDATE $table SET $str WHERE id = :id";
    $params['id'] = $id;
    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
}

// Видалення рядка з таблиці
function delete($table, $id){
    global $pdo;
    $sql = "DELETE FROM $table WHERE id = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id' => $id]);
    dbCheckError($query);
}

// Вибірка записів (posts) з автором в адмінці
function selectAllFromPostsWithUsers($table1, $table2){
    global $pdo;
    $sql = "SELECT 
        t1.id,
        t1.title,
        t1.img,
        t1.content,
        t1.status,
        t1.id_topic,
        t1.created_date,
        t2.username
        FROM $table1 AS t1 JOIN $table2 AS t2 ON t1.id_user = t2.id";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Вибірка записів (posts) з автором на головну
function selectAllFromPostsWithUsersOnIndex($table1, $table2, $limit, $offset){
    global $pdo;
    $sql = "SELECT p.*, u.username FROM $table1 AS p JOIN $table2 AS u ON p.id_user = u.id WHERE p.status=1 LIMIT $limit OFFSET $offset";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Вибірка записів (posts) з автором на головну
function selectTopTopicFromPostsOnIndex($table1){
    global $pdo;
    $sql = "SELECT * FROM $table1 WHERE id_topic = 18";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Пошук за заголовками та вмістом (простий)
function seacrhInTitileAndContent($text, $table1, $table2){
    $text = trim(strip_tags(stripcslashes(htmlspecialchars($text))));
    global $pdo;
    $sql = "SELECT 
        p.*, u.username 
        FROM $table1 AS p 
        JOIN $table2 AS u 
        ON p.id_user = u.id 
        WHERE p.status=1
        AND (p.title LIKE '%$text%' OR p.content LIKE '%$text%')";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Вибірка запису (posts) з автором для сингів
function selectPostFromPostsWithUsersOnSingle($table1, $table2, $id){
    global $pdo;
    $sql = "SELECT p.*, u.username FROM $table1 AS p JOIN $table2 AS u ON p.id_user = u.id WHERE p.id=:id";
    $query = $pdo->prepare($sql);
    $query->execute(['id' => $id]);
    dbCheckError($query);
    return $query->fetch();
}

// Рахуємо кількість рядків у таблиці
function countRow($table){
    global $pdo;
    $sql = "SELECT Count(*) FROM $table";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchColumn();
}
