<?php

require '../vendor/autoload.php';  // Include the composer autoload for the Endroid QR code library

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

function sanitizeLinks($links) {
    // Trim whitespace and filter out invalid URLs
    $sanitized_links = [];
    foreach ($links as $link) {
        $trimmed = trim($link);
        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $sanitized_links[] = $trimmed;
        }
    }
    return $sanitized_links;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['links'])) {
    $links = explode("\n", $_POST['links']);  // Split the text area by line
    $links = sanitizeLinks($links);           // Sanitize the links

    // Create the output directory if it doesn't exist
    $outputDir = '../qr_codes/';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);  // Create folder with write permissions
    }

    if (empty($links)) {
        header('Location: ../index.php?message=No valid URLs provided.');
        exit;
    }

    foreach ($links as $link) {
        try {
            // Generate QR code using the QrCode class from version 2.x
            $qrCode = new QrCode($link);
            $qrCode->setSize(300)                   // Set size to 300x300 px
                   ->setMargin(10)                  // Add margin
                   ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);  // Set error correction level to high

            // Create a file-safe name for the QR code
            $urlParts = parse_url($link);           // Parse the URL
            $path = trim($urlParts['path'], '/');   // Get the path and trim slashes
            $queryString = '';

            // Check if the query exists and parse it
            if (isset($urlParts['query'])) {
                parse_str($urlParts['query'], $queryParams);  // Parse the query parameters
                // Build a query string for the file name
                if (!empty($queryParams)) {
                    foreach ($queryParams as $key => $value) {
                        $queryString .= '_' . $key . '_' . $value;  // Append key-value pairs
                    }
                }
            }

            // Construct the final file name
            $fileName = preg_replace('/[^a-zA-Z0-9_]/', '_', $path) . $queryString . '.png';
            $fileName = trim($fileName, '_');  // Trim leading/trailing underscores
            $filePath = $outputDir . $fileName;

            // Save the QR code to a file
            file_put_contents($filePath, $qrCode->writeString());  // Write the QR code image directly to file

        } catch (Exception $e) {
            header('Location: ../index.php?message=Error generating QR code: ' . $e->getMessage());
            exit;
        }
    }

    // Redirect after successful generation
    header('Location: ../index.php?message=QR codes successfully generated!');
    exit;
} else {
    // Redirect if accessed without form submission
    header('Location: ../index.php');
    exit;
}
