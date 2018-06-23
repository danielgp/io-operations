
Set WshShell   = WScript.CreateObject("WScript.Shell")
CheckStandards = Array("PSR1", "PSR2")

MsgBox "I will assess the PHP projects for compatibility with predefined standards!"

strCurDir      = WshShell.CurrentDirectory
strBaseDir     = Replace(strCurDir, "tests", "")

For Each crtStandard In CheckStandards
    WshShell.Run "C:\www\App\PHP\7.2.x64\php.exe -c C:\www\Config\PHP\7.2.x64\php.ini  D:\www\Data\GitRepositories\GitHub\danielgp\network-components\vendor\squizlabs\php_codesniffer\bin\phpcs -p -v --extensions=php -d date.timezone=""Europe/Bucharest"" --encoding=utf-8 --report=xml --standard=" & crtStandard & " " & strBaseDir & " --report-file=" & strCurDir & "\php-code-sniffer\" & crtStandard & ".xml --ignore=*/data/*,*/tests/*,*/tmp/*,*/vendor/*", 0, True
Next

MsgBox "I finished generating XML files with PHP-Code-Sniffer results!"
