' RyaanCMS Auto-Start — runs servers silently on Windows login
' No console window appears. Servers run in background.

Dim PHP, PROJECT, WSH

PHP     = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
PROJECT = "C:\Users\hp\Desktop\AI claude\RyaanCMS"

Set WSH = CreateObject("WScript.Shell")

' Start port 8000 (hidden, no window)
WSH.Run Chr(34) & PHP & Chr(34) & " -f " & Chr(34) & PROJECT & "\artisan" & Chr(34) & " serve --host=127.0.0.1 --port=8000", 0, False

' Wait 2 seconds before starting second instance
WScript.Sleep 2000

' Start port 8001 (hidden, no window)
WSH.Run Chr(34) & PHP & Chr(34) & " -f " & Chr(34) & PROJECT & "\artisan" & Chr(34) & " serve --host=127.0.0.1 --port=8001", 0, False

Set WSH = Nothing
