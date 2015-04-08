<?php

class Email {

  /*
   * Validate file type and generate error
   */
  function validate_file($file) {

    $error = null;

    $file_type = $file['upload_file']['type'];
    $file_error = $file['upload_file']['error'];
    $file_size = $file['upload_file']['size'];
    $file_tmp = $file['upload_file']['tmp_name'];

    // Validate File
    $file_mime = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

    if (!in_array($file_type, $file_mime)) {
      $error = "File type incorrect! Please upload CSV file only.";
    }

    if ($file_error > 0) {
      $error = 'Please upload again!';
    } 

    if ($file_size == 0) {
      $error = 'File is empty!';
    }   
    
    return $error;  

  }

  /*
   * Validate email format and seperate valid and invalid format into array 
   */
  function validate_email($file_tmp) {

    $valid_email = array();
    $invalid_email = array();

    $file = fopen($file_tmp, 'r');

    while (($line = fgetcsv($file)) !== FALSE) {

      foreach ($line as $email) {
        
        if (!empty($email)) {
          if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $valid_email[] = $email;
          } else {
            $invalid_email[] = $email;
          }
        }
      }
    }

    fclose($file);

    return array('valid_email' => $valid_email, 'invalid_email' => $invalid_email);    
  }

  function generate_csv_file($array, $filename, $delimiter=";") {

    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="'.$filename.'";');

    $f = fopen('php://output', 'w');

    foreach ($array as $line) {
        fputcsv($f, array($line), $delimiter);
    }
  }

  function process($file) {

    $email_list = null;  

    // Validate File
    $error = $this->validate_file($file);

    // Validate Email Format
    if (empty($error)) {
      $file_tmp = $file['upload_file']['tmp_name'];
      $email_list = $this->validate_email($file_tmp);

      // Generate valid email file
      $file_name_for_valid = 'valid_email_address-' . date('Y-m-d--H-i-s') . '.csv';
      $this->generate_csv_file($email_list['valid_email'], $file_name_for_valid);
    } else {
      header('Location: index.php?error=' . $error);
      exit;
    }

   }

   
} // end of class Email

$email = new Email();

$email->process($_FILES);
?>