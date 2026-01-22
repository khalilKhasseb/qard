@echo off
REM QCard Queue Worker Setup for Windows Server
REM Run this as Administrator

echo Setting up QCard Queue Workers for Windows...

REM Variables - Update these paths for your server
set APP_PATH=C:\inetpub\wwwroot\qcard
set SERVICE_NAME=QCard-Queue-Worker

REM 1. Create queue worker service using NSSM (Non-Sucking Service Manager)
REM Download NSSM from https://nssm.cc/download

echo Installing Queue Worker Service...
nssm install %SERVICE_NAME% php
nssm set %SERVICE_NAME% Application php
nssm set %SERVICE_NAME% AppParameters "artisan queue:work database --sleep=3 --tries=3"
nssm set %SERVICE_NAME% AppDirectory %APP_PATH%
nssm set %SERVICE_NAME% DisplayName "QCard Queue Worker"
nssm set %SERVICE_NAME% Description "QCard Laravel Queue Worker Service"
nssm set %SERVICE_NAME% Start SERVICE_AUTO_START

REM 2. Start the service
echo Starting Queue Worker Service...
net start %SERVICE_NAME%

REM 3. Create scheduled task for Laravel scheduler
echo Setting up Laravel Scheduler...
schtasks /create /tn "QCard-Scheduler" /tr "php %APP_PATH%\artisan schedule:run" /sc minute /mo 1 /ru "SYSTEM"

REM 4. Create monitoring script
echo Creating monitoring script...
echo @echo off > %APP_PATH%\monitor-queues.bat
echo cd /d %APP_PATH% >> %APP_PATH%\monitor-queues.bat
echo echo === Queue Status Check - %%date%% %%time%% === >> %APP_PATH%\monitor-queues.bat
echo sc query %SERVICE_NAME% >> %APP_PATH%\monitor-queues.bat
echo php artisan queue:failed >> %APP_PATH%\monitor-queues.bat
echo php artisan queue:monitor default >> %APP_PATH%\monitor-queues.bat

echo Setup complete!
echo.
echo Management commands:
echo - Check service: sc query %SERVICE_NAME%
echo - Restart service: net stop %SERVICE_NAME% ^&^& net start %SERVICE_NAME%
echo - View failed jobs: cd %APP_PATH% ^&^& php artisan queue:failed
echo - Monitor queues: %APP_PATH%\monitor-queues.bat