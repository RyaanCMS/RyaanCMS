' RyaanCMS Auto-Start — MySQL + PHP servers, no console window

Dim PHP, MYSQLD, MYSQL_INI, PROJECT, WSH

PHP       = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
MYSQLD    = "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe"
MYSQL_INI = "C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini"
PROJECT   = "C:\Users\hp\Desktop\AI claude\RyaanCMS"

Set WSH = CreateObject("WScript.Shell")

' ── Step 1: Start MySQL (hidden, no window) ───────────────────────────────
WSH.Run Chr(34) & MYSQLD & Chr(34) & " --defaults-file=" & Chr(34) & MYSQL_INI & Chr(34), 0, False

' Wait 5 seconds for MySQL to fully initialize before PHP connects
WScript.Sleep 5000

' ── Step 2: Start PHP artisan serve on port 8000 ─────────────────────────
WSH.Run Chr(34) & PHP & Chr(34) & " -f " & Chr(34) & PROJECT & "\artisan" & Chr(34) & " serve --host=127.0.0.1 --port=8000", 0, False

' Wait 2 seconds
WScript.Sleep 2000

' ── Step 3: Start PHP artisan serve on port 8001 ─────────────────────────
WSH.Run Chr(34) & PHP & Chr(34) & " -f " & Chr(34) & PROJECT & "\artisan" & Chr(34) & " serve --host=127.0.0.1 --port=8001", 0, False

Set WSH = Nothing
