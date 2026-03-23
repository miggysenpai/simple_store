<?php
ob_start();
require_once '../users/init.php';

$download_id = Input::get('id'); 
$download_availabilty = $db->query("SELECT * FROM simple_store_expenses WHERE id = ?", [$download_id])->first(); 
    
$no_access =  '
        <html>
            <head>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">    
            </head>
            <body>
                <div class="px-4 py-5 my-5 text-center">
                    
                    <h1 class="display-5 fw-bold text-body-emphasis">File download not set up or you do not have access. Please reach out to admin</h1>
                    <div class="col-lg-6 mx-auto">
                      <br /><br /><br />
                      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                       
                        <a href="../usersc/login.php" class="btn btn-outline-secondary btn-lg ">
                          Click here to go back to home page.
                        </a>
                      </div>
                    </div>
                  </div>
            </body>
        </html>
        
    ';    
    
//Checks if file exists
if($download_availabilty->file == ""){
   echo $no_access;
  die();
}
        

if(isUserLoggedIn()) {
    
        // if is admin, go ahead and download
        if(hasPerm([2], $user->data()->id)){
            $download = $db->query("SELECT * FROM simple_store_expenses WHERE id = ?",[$download_id])->first(); // get product info
            
            
            // Check if a filename is provided in the URL (e.g., download.php?file=example.pdf)
            if($download->file !== "") {
                
                // Basic security: use basename() to prevent directory traversal attacks
                $fileName = basename($download->file);
                $filePath = $abs_us_root . $us_url_root . "usersc/plugins/simple_store/downloads/" . $fileName; // Specify the path to your files on the server
                $mime_type = mime_content_type($filePath);
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            
                // Check if the file exists on the server
                if (file_exists($filePath)) {
                    // Clear the output buffer
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
            
                    // Set headers to force download
                    header('Content-Description: File Transfer');
                    header('Content-Type: ' . $mime_type); // Generic MIME type for force download
                    header('Content-Disposition: attachment; filename="'.$download->name.'_id_'.$download_id.'.'.$fileExtension.'"');
                    header("Cache-Control: public");
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($filePath));
            
            
                    
                    // Read the file and output its content to the browser
                    readfile($filePath);
                    exit; // Terminate the script to prevent any extra output
                } else {
                    // File not found
                    http_response_code(404);
                    die("File not found.");
                }
            } else {
                die("No file specified.");
            }

        }

} else { 
    echo $no_access;
}

?>