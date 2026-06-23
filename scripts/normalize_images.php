<?php
declare(strict_types=1);

chdir(__DIR__ . '/../');
require_once __DIR__ . '/../includes/helpers.php';

// CLI-only script to normalize image filenames and update product records.
$updated = [];

function scan_dir_recursive(string $dir): array
{
    $files = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iter as $item) {
        if ($item->isFile()) {
            $files[] = $item->getPathname();
        }
    }
    return $files;
}

$baseDir = __DIR__ . '/../';
$folders = array_values(CATEGORY_IMAGE_PATHS);
$folders[] = UNSORTED_IMAGE_PATH;

// ensure absolute path and unique
$folders = array_map(fn($p) => realpath($baseDir . $p) ?: $baseDir . $p, $folders);

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        continue;
    }
    $files = scan_dir_recursive($folder);
    foreach ($files as $path) {
        $relPath = str_replace(realpath($baseDir) . '/', '', $path);
        $dir = dirname($path);
        $basename = basename($path);
        $safe = normalize_asset_filename($basename);
        if ($safe === $basename) {
            continue;
        }
        $target = $dir . '/' . $safe;
        // avoid overwrite
        $i = 1;
        $final = $target;
        while (is_file($final)) {
            $final = $dir . '/' . pathinfo($safe, PATHINFO_FILENAME) . '-' . $i . '.' . pathinfo($safe, PATHINFO_EXTENSION);
            $i++;
        }
        if (@rename($path, $final)) {
            $newRel = str_replace(realpath($baseDir) . '/', '', $final);
            $updated[$relPath] = $newRel;
            echo "Renamed: {$relPath} -> {$newRel}\n";
        } else {
            echo "Failed to rename: {$relPath}\n";
        }
    }
}

// Update DB or JSON products
if (class_exists('SQLite3') && is_file(__DIR__ . '/../db.php')) {
    require_once __DIR__ . '/../db.php';
    $db = get_db();
    $stmt = $db->prepare('SELECT id, image FROM products');
    $res = $stmt->execute();
    $updates = 0;
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $id = $row['id'];
        $img = $row['image'];
        if (!$img) continue;
        if (isset($updated[$img])) {
            $new = $updated[$img];
            $u = $db->prepare('UPDATE products SET image = :image WHERE id = :id');
            $u->bindValue(':image', $new, SQLITE3_TEXT);
            $u->bindValue(':id', $id, SQLITE3_INTEGER);
            $u->execute();
            $updates++;
            echo "DB updated product {$id}: {$img} -> {$new}\n";
        }
    }
    echo "Total DB updates: {$updates}\n";
} else {
    // JSON fallback
    $products = load_json('products', []);
    $changed = 0;
    foreach ($products as &$p) {
        if (empty($p['image'])) continue;
        $img = $p['image'];
        if (isset($updated[$img])) {
            $p['image'] = $updated[$img];
            $changed++;
            echo "JSON updated product {$p['id']}: {$img} -> {$p['image']}\n";
        }
    }
    if ($changed > 0) {
        save_json('products', $products);
    }
    echo "Total JSON updates: {$changed}\n";
}

if (empty($updated)) {
    echo "No files renamed.\n";
} else {
    file_put_contents(__DIR__ . '/../data/normalize-report.json', json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "Normalization complete. Report saved to data/normalize-report.json\n";
}
