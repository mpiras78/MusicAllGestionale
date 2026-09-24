<?php
$db=new PDO('sqlite:database/musicall.sqlite');
$res=$db->query("SELECT type,name,sql FROM sqlite_master WHERE type IN ('table','index','trigger','view') ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $r){
    echo $r['type'] . "\t" . $r['name'] . "\n";
}
?>