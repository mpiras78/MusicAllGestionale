<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/../database/musicall.sqlite');
$print = function($rows){
    foreach($rows as $r){
        echo "- {$r['name']} ({$r['type']})\n";
    }
};

echo "soci columns:\n";
$stmt = $pdo->query('PRAGMA table_info(soci)');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
$print($cols);

echo "\nlezioni columns:\n";
$stmt2 = $pdo->query('PRAGMA table_info(lezioni)');
$cols2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$print($cols2);

echo "\nviews: \n";
$views = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='view'")->fetchAll(PDO::FETCH_ASSOC);
foreach($views as $v){
    echo "- {$v['name']}\n";
}
