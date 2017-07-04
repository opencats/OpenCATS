Set WshShell = CreateObject("WScript.Shell") 
WshShell.Run  "D:/tools/xampp/php/php.exe " & "C:/Users/mb/git/OpenATS/QueueCLI.php", 0
Set WshShell = Nothing