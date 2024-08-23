<?php
header('Content-Type: application/json');

$uploadDirectory = 'uploads/';
$response = [];

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files'])) {
        $files = $_FILES['files'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = basename($files['name'][$i]);
            $fileTmpName = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

            // Handle duplicate filenames
            $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $counter = 1;
            while (file_exists($uploadDirectory . $fileName)) {
                $fileName = $fileNameWithoutExt . "($counter)." . $fileExt;
                $counter++;
            }

            if (move_uploaded_file($fileTmpName, $uploadDirectory . $fileName)) {
                $response[] = [
                    'fileName' => $fileName,
                    'fileSize' => $fileSize,
                    'uploadDate' => date('Y-m-d H:i:s'),
                    'fileUrl' => $uploadDirectory . $fileName
                ];
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to upload file.']);
                exit;
            }
        }

        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'No files uploaded.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
