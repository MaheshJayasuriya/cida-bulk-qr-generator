<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk QR Code Generator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css"> <!-- Include Bootstrap Icons -->
    <style>
    body {
        margin: 20px;
        transition: background-color 0.3s, color 0.3s;
    }
    .light-theme {
        background-color: #f8f9fa;
        color: #212529;
    }
    .dark-theme {
        background-color: #343a40;
        color: white;
    }
    .dark-theme .card {
            background-color: #495057; /* Darker background color for the card */
            color: #f8f9fa; /* Light text color for better contrast */
            border: 1px solid #6c757d; /* Optional: add a border for better visibility */
        }
    h3 {
        margin-bottom: 20px;
    }
    .message {
        color: green;
    }
    .error {
        color: red;
    }
    .collapse {
        display: none;
        margin-bottom: 10px; /* Add space between the collapsed sections */        
    }
    .expandable {
        cursor: pointer;
        padding: 10px;
        border: 1px solid #007bff;
        border-radius: 5px;
        background-color: #e9ecef;
        transition: background-color 0.3s;
        margin-bottom: 5px; /* Add space between date headers */
    }
    .dark-theme .expandable {
            background-color: #495057; /* Darker background color for the card */
            color: #f8f9fa; /* Light text color for better contrast */
            border: 1px solid #6c757d; /* Optional: add a border for better visibility */
        }
    .expandable:hover {
        background-color: #d5e9ff;
    }
    .dark-theme .expandable:hover {
            background-color: #343a40;            
        }
    .qr-code-list {
        list-style-type: none;
        padding-left: 0;
    }
    .qr-code-list li {
        padding: 5px 0;
    }
    .qr-code-list a {
        text-decoration: none;
        color: #007bff;
    }
    .qr-code-list a:hover {
        text-decoration: underline;
    }
    footer {
        margin-top: 30px;
        text-align: center;
        color: #6c757d;
    }
    .theme-switcher {
        margin-bottom: 20px;
    }
    .form-group {
        border-radius: 5px; /* Optional: adds rounded corners */
        padding: 10px; /* Optional: adds padding for better appearance */
    }
    .dark-theme .form-group {
        background-color: #495057; /* Dark background for dark theme */
        color: #fff; /* Change text color for readability */
    }
</style>

</head>
<body class="light-theme" id="body">

<div class="container">
    <h3 class="text-center">CIDA Bulk QR Code Generator</h3>
    <p class="text-center">Generate multiple QR codes from a list of URLs.</p>

    <div class="text-center theme-switcher">
        <label class="switch">
            <input type="checkbox" id="theme-toggle">
            <span class="slider round"></span>
        </label>
        <span id="theme-text">Switch to Dark Mode</span>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success message">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
        <form action="src/generate_qr.php" method="post">
    <div class="form-group">
        <label for="event_name" >Name or Batch Number:</label>
        <input type="text" id="event_name" name="event_name" placeholder="A name or a batch number for this set" class="form-control" required>        
    </div>
    <div class="form-group">
        <label for="links">Enter URLs (one per line):</label>
        <textarea id="links" name="links" class="form-control" rows="10" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Generate QR Codes</button>

        </div>
    </div>

    <p>If you need to bulk rename files, please download our file rename tool for Windows <a href="files/CIDA_bulk_file_renamer_v1.00.zip">here</a> </p>

    <h5 class="mt-4">Download Generated QR Codes</h5>
<?php
$qrCodesDir = 'qr_codes/';
if (is_dir($qrCodesDir)) {
    $events = array_filter(glob($qrCodesDir . '*'), 'is_dir');
    if (!empty($events)) {
        foreach ($events as $event) {
            $eventName = basename($event);
            echo '<div class="expandable" onclick="toggleCollapse(\'' . $eventName . '\')">';
            echo '<span id="icon-' . $eventName . '">▼</span> ' . htmlspecialchars($eventName);
            echo '</div>';
            echo '<div class="collapse" id="' . $eventName . '">';
            
            $qrCodes = glob($event . '/*.png');
            if (!empty($qrCodes)) {
                echo '<ul class="qr-code-list">';
                foreach ($qrCodes as $qrCode) {
                    $fileName = basename($qrCode);
                    $filePath = $qrCodesDir . $eventName . '/' . $fileName;
                    echo '<li><a href="' . htmlspecialchars($filePath) . '" download>' . htmlspecialchars($fileName) . '</a></li>';
                }
                echo '</ul>';
                // Download entire folder as zip
                echo '<a href="zip_folder.php?folder=' . urlencode($eventName) . '" class="btn btn-success">Download All as Zip</a>';
            } else {
                echo '<p>No QR codes found for this event.</p>';
            }
            echo '</div>';
        }
    } else {
        echo '<p>No QR codes have been generated yet.</p>';
    }
} else {
    echo '<p>QR code directory does not exist.</p>';
}
?>  

    <footer>
    <p>Powered by <a href="mailto:maheshcida@gmail.com">CIDA-IT</a> @ 2024</p>
    </footer>
</div>

<script>
    // On page load, check for saved theme in localStorage
    document.addEventListener("DOMContentLoaded", function() {
        const savedTheme = localStorage.getItem('theme') || 'light'; // Default to 'light' theme if nothing is saved
        const body = document.getElementById('body');
        const themeText = document.getElementById('theme-text');
        const themeToggle = document.getElementById('theme-toggle');

        // Apply the saved theme
        if (savedTheme === 'dark') {
            body.classList.remove('light-theme');
            body.classList.add('dark-theme');
            themeText.textContent = "Switch to Light Mode";
            themeToggle.checked = true; // Set the toggle to dark mode
        } else {
            body.classList.remove('dark-theme');
            body.classList.add('light-theme');
            themeText.textContent = "Switch to Dark Mode";
            themeToggle.checked = false; // Set the toggle to light mode
        }
    });

    // Theme toggler
    document.getElementById('theme-toggle').addEventListener('change', function() {
        const body = document.getElementById('body');
        const themeText = document.getElementById('theme-text');
        
        if (this.checked) {
            body.classList.remove('light-theme');
            body.classList.add('dark-theme');
            themeText.textContent = "Switch to Light Mode";
            localStorage.setItem('theme', 'dark'); // Save dark theme in localStorage
        } else {
            body.classList.remove('dark-theme');
            body.classList.add('light-theme');
            themeText.textContent = "Switch to Dark Mode";
            localStorage.setItem('theme', 'light'); // Save light theme in localStorage
        }
    });

    // Function to toggle collapse of date-based QR code sections
    function toggleCollapse(date) {
        const content = document.getElementById(date);
        const icon = document.getElementById('icon-' + date);
        if (content.style.display === "block") {
            content.style.display = "none";
            icon.textContent = "▼"; // Show down arrow when collapsed
        } else {
            content.style.display = "block";
            icon.textContent = "▲"; // Show up arrow when expanded
        }
    }
</script>


<style>
    /* Add styles for the switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #007bff;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>

</body>
</html>
