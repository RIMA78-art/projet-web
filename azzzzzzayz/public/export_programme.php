<?php
header('Location: index.php?route=programme/exportPdf&id=' . (int)($_GET['id'] ?? 0));
exit;
