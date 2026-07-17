@echo off
REM Adds all Magzani tenant subdomains to the Windows hosts file.
REM Must be run as Administrator (right-click -> Run as administrator).

setlocal EnableDelayedExpansion

set "HOSTS=%SystemRoot%\System32\drivers\etc\hosts"

>nul findstr /b "# Magzani tenants (local dev)" "%HOSTS%" && (
    echo Tenants already present in hosts file. Nothing to do.
    pause
    exit /b 0
)

>> "%HOSTS%" (
    echo.
    echo # Magzani tenants (local dev)
    echo 127.0.0.1 alpha.localhost
    echo 127.0.0.1 horas.localhost
    echo 127.0.0.1 joo.localhost
    echo 127.0.0.1 mahgoup.localhost
    echo 127.0.0.1 mahmo.localhost
    echo 127.0.0.1 store.localhost
    echo 127.0.0.1 youssef.localhost
)

ipconfig /flushdns | findstr /v "^$"

echo Done. Tenants added to hosts file.
pause
