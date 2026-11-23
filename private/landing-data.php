<?php
declare(strict_types=1);
function landingCurrent(): ?array {
 $slug=(string)($_GET['slug']??'');
 if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D',$slug))return null;
 $db=orderDatabase();
 $db->exec("CREATE TABLE IF NOT EXISTS landing_pages (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(200) NOT NULL, status ENUM('draft','published') NOT NULL DEFAULT 'draft', data_json LONGTEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 $q=$db->prepare('SELECT * FROM landing_pages WHERE slug=? AND status=?');
 $q->execute([$slug,'published']);return $q->fetch(PDO::FETCH_ASSOC)?:null;
}
