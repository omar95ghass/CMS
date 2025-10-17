## ##
## ##
## CMS queue management system v2.0 ##
## To install this update, please follow these intructions: ##

- *Replace* the file: *"index.php"* 
        -> with the old one in the Root folder.
        
- *Replace* the file: *"add_queue.php"* 
        -> with the old one in this direction: "RootFolder/php/add_queue.php".

- *Add* the file *"print_image"* to this direction "RootFolder/php/".

- *Add* the Folder *"Vendor"* to this direction "RootFolder/" along with "index.php".

- *Replace* this printer with your actual shared printer name: 
        *file: RootFolder/php/print_image.php  ->  Line 53 : $conn = new WindowsPrintConnector('MP-80');*

- *Clear* cash memory in the browser. 