<?php
if (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
    $folderPath = 'qr_codes/' . $folder;

    if (is_dir($folderPath)) {
        $zip = new ZipArchive();
        $zipFile = 'qr_codes/' . $folder . '.zip'; // Output zip file path

        // Create a zip file
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add files to the zip archive
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            // Close zip file
            $zip->close();

            // Serve the zip file for download
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . basename($zipFile));
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);

            // Optionally, delete the zip file after download
            unlink($zipFile);
            exit;
        } else {
            echo 'Failed to create zip file';
        }
    } else {
        echo 'Invalid folder';
    }
} else {
    echo 'No folder specified';
}
