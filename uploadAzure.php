<?php
/**----------------------------------------------------------------------------------
* Microsoft Developer & Platform Evangelism
*
* Copyright (c) Microsoft Corporation. All rights reserved.
*
* THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, 
* EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES 
* OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
*----------------------------------------------------------------------------------
* The example companies, organizations, products, domain names,
* e-mail addresses, logos, people, places, and events depicted
* herein are fictitious.  No association with any real company,
* organization, product, domain name, email address, logo, person,
* places, or events is intended or should be inferred.
*----------------------------------------------------------------------------------
**/

/** -------------------------------------------------------------
# Azure Storage Blob Sample - Demonstrate how to use the Blob Storage service. 
# Blob storage stores unstructured data such as text, binary data, documents or media files. 
# Blobs can be accessed from anywhere in the world via HTTP or HTTPS. 
#
# Documentation References: 
#  - Associated Article - https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php 
#  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/ 
#  - Getting Started with Blobs - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
#  - Blob Service Concepts - http://msdn.microsoft.com/en-us/library/dd179376.aspx 
#  - Blob Service REST API - http://msdn.microsoft.com/en-us/library/dd135733.aspx 
#  - Blob Service PHP API - https://github.com/Azure/azure-storage-php
#  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/ 
#
**/

require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

// $connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');

$connectionString = "DefaultEndpointsProtocol=https;AccountName=cripsstorage;AccountKey=buOWlctMRWL0IOiU/Klo2Fa1rIYZve2Z4KB3DVOloJZMt7bBErWb+xkcLU5OHfFMOAYdIihiKARuESnHqhnGtg==";
// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

// $fileToUpload = "HelloWorld.txt";

// if (!isset($_GET["Cleanup"])) {
if (isset($_FILES['azureImage'])) {
	$errors= array();
      $file_name = $_FILES['azureImage']['name'];
      $file_size = $_FILES['azureImage']['size'];
      $file_tmp = $_FILES['azureImage']['tmp_name'];
      $file_type = $_FILES['azureImage']['type'];
      $file_explode = explode(".", $file_name);
      $file_ext = $file_explode[count($file_explode)-1];
      
      $expensions= array("png");
      
      if(in_array($file_ext,$expensions)=== false){
         $errors[]="extension not allowed, please choose a PNG file.";
      }
      
      if($file_size > 1024152){
         $errors[]='File size must be excately 1 MB';
      }
      
      if(empty($errors)==true){
         //move_uploaded_file($file_tmp,"images/".$file_name);
        $fileToUpload = $file_tmp;

          // Create container options object.
	    $createContainerOptions = new CreateContainerOptions();

	    // Set public access policy. Possible values are
	    // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
	    // CONTAINER_AND_BLOBS:
	    // Specifies full public read access for container and blob data.
	    // proxys can enumerate blobs within the container via anonymous
	    // request, but cannot enumerate containers within the storage account.
	    //
	    // BLOBS_ONLY:
	    // Specifies public read access for blobs. Blob data within this
	    // container can be read via anonymous request, but container data is not
	    // available. proxys cannot enumerate blobs within the container via
	    // anonymous request.
	    // If this value is not specified in the request, container data is
	    // private to the account owner.
	    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

	    // Set container metadata.
	    $createContainerOptions->addMetaData("key1", "value1");
	    $createContainerOptions->addMetaData("key2", "value2");

	    $containerName = "blockblobs".generateRandomString();

	    try {
	        // Create container.
	        $blobClient->createContainer($containerName, $createContainerOptions);

	        // Getting local file so that we can upload it to Azure
	        // $myfile = fopen($fileToUpload, "w") or die("Unable to open file!");
	        // fclose($myfile);
	        
	        # Upload file as a block blob
	        // echo "Uploading BlockBlob: ".PHP_EOL;
	        // echo $fileToUpload;
	        // echo "<br />";
	        
	        $content = fopen($fileToUpload, "r");

	        //Upload blob
	        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

	        // List blobs.
	        $listBlobsOptions = new ListBlobsOptions();
	        $listBlobsOptions->setPrefix($file_name);

	        // echo "These are the blobs present in the container: ";

	        do{
	            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
	            foreach ($result->getBlobs() as $blob)
	            {
	                echo $blob->getName().": ".$blob->getUrl()."<br />";
	            }
	        
	            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
	        } while($result->getContinuationToken());
	        // echo "<br />";

	        // Get blob.
	        // echo "This is the content of the blob uploaded: ";
	        // $blob = $blobClient->getBlob($containerName, $fileToUpload);
	        // fpassthru($blob->getContentStream());
	        // echo "<br />";
	    }
	    catch(ServiceException $e){
	        // Handle exception based on error codes and messages.
	        // Error codes and messages are here:
	        // http://msdn.microsoft.com/library/azure/dd179439.aspx
	        $code = $e->getCode();
	        $error_message = $e->getMessage();
	        echo $code.": ".$error_message."<br />";
	    }
	    catch(InvalidArgumentTypeException $e){
	        // Handle exception based on error codes and messages.
	        // Error codes and messages are here:
	        // http://msdn.microsoft.com/library/azure/dd179439.aspx
	        $code = $e->getCode();
	        $error_message = $e->getMessage();
	        echo $code.": ".$error_message."<br />";
	    }
	    echo "Success Submit";
	} 
	else {
		printf($errors);
	}
}
?>
<!DOCTYPE html>
<html>
<body>
	<form action="" method="POST" enctype="multipart/form-data">
	 <label>FOTO <small>(PNG, max 1MB)</small></label>
      <input type="file" class="form-control input-sm input-cst" name="azureImage" id="azureImage">
      <div class="error-wrap"></div>
    <button type="submit">Tekan untuk membersihkan semua sumber daya yang dibuat oleh sampel ini</button>
</form>
</body>
</html>
