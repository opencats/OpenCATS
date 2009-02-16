This set of functions is brought to you by Seven, known as s3v3n_0f_n1n3 on many forums and sites (including sourceforge). I'd like to thank PKWare and Vilma Software for providing information on their websites helping me to realize this project. Also i'd like to thank Max for getting me to write this(I bet he doesn't even know himself :p).

Usage of these libraries:

First, include ziplib.php into any document which might need it. This makes the class available for that document(duh!).
Then, to start working on a zipfile, first declarate the class, like '$ziplib = new Ziplib;' .
Then call $ziplib->zl_add_file(content of the file, filename, compression level); where compression level is either an n (none), b(bzip) or g(gzip), followed by a number between 0 to 9, with 0 being no compression and 9 maximum compression.
Use this function for every file you wish to add.
If you're done, use a function similar to '$zipfile = $ziplib->zl_create(comment);' where comment is the comment added to the zipfile.
Example:
<?php
$zipfile = new Ziplib;
$zipfile->zl_add_file("this is file 1","file1.txt","g9");
$zipfile->zl_add_file("then this must be file 2","file2.txt","b9");
$zipfile->zl_add_file("and, how did you guess, this is file 3","file3.txt","n");

header("content-type: application/zip");
header('Content-Disposition: attachment; filename="file.zip"');
echo $zipfile->zl_pack("Thanks for downloading this testing zip-file");
?>

Since Ziplib-0.2 you can work on several zipfiles at the same time, simply by declarating another class. I don't care if it's called that way, you know what I mean ;).

In 0.3 I've added limited support for file reading. Usage like below:
<?php
$zipfile = new Ziplib;
$index = $zipfile->zl_index_file("./zipfile.zip");
print_r($index);
?>
The code above will give a nice indication of how it works. Please note that the zipfile MUST exist, else a die() is called. So check before you give the library a nice file to digest. Also, this function is not very memory effective, it loads the entire file into the memory. Adviced is not to load large zipfiles using Ziplib. I have some ideas to minimize memory usage, but this will take some time to implement.

Known issues:
The last time the files have been edited is equal to the date and time the zipfile is created
Lots of lengths are not checked yet, making this code relatively unsafe for direct use. Usage in a controled envoirement is safe ofcourse :)

The time-retreival MIGHT not work properly on big-endian machines.
Features:
Creating zip-archives of one or more files
Compressing files
Adding comments to non-exsisting zipfiles

Todo:
Reading zipfiles implementation
Fix known issues
