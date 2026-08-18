<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'] ?? '';
$subtitle = $_POST['subtitle'] ?? '';
$link_url = $_POST['link_url'] ?? '';
$link_text = $_POST['link_text'] ?? '';
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;

$image_path = '';
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT image_path FROM sliders WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    $image_path = $existing ? $existing['image_path'] : '';
}

// Handle file upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/sliders/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['image']['name']);
    $targetFilePath = $uploadDir . $fileName;
    
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $image_path = 'uploads/sliders/' . $fileName;
        } else {
            echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Gagal mengupload gambar.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Format file tidak didukung.']);
        exit;
    }
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE sliders SET title = ?, subtitle = ?, link_url = ?, link_text = ?, is_active = ?, urutan = ?" . ($image_path ? ", image_path = ?" : "") . " WHERE id = ?");
        $params = [$title, $subtitle, $link_url, $link_text, $is_active, $urutan];
        if ($image_path) $params[] = $image_path;
        $params[] = $id;
        $stmt->execute($params);
        $message = "Slider berhasil diupdate";
    } else {
        if (!$image_path) {
            echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Gambar wajib diupload untuk slider baru.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO sliders (image_path, title, subtitle, link_url, link_text, is_active, urutan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$image_path, $title, $subtitle, $link_url, $link_text, $is_active, $urutan]);
        $message = "Slider berhasil ditambahkan";
    }
    
    echo json_encode(['success' => true, 'status' => 'success', 'message' => $message]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'status' => 'error', 'message' => $e->getMessage()]);
}
?>
