$HostName = '127.0.0.1'
$Port = 8000
$Php = 'C:\Users\alexa\.config\herd-lite\bin\php.exe'
$LogFile = Join-Path $PSScriptRoot '..\storage\logs\laravel-server-watchdog.log'
$Root = Split-Path $PSScriptRoot -Parent

Set-Location $Root

while ($true) {
    $listener = Get-NetTCPConnection -LocalAddress $HostName -LocalPort $Port -State Listen -ErrorAction SilentlyContinue

    if (-not $listener) {
        $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        Add-Content -Path $LogFile -Value "[$timestamp] Preview server stopped or was unavailable; restarting."

        $arguments = '-d', 'variables_order=EGPCS', 'artisan', 'serve', "--host=$HostName", "--port=$Port"
        $process = Start-Process -FilePath $Php -ArgumentList $arguments -WorkingDirectory $Root -PassThru -WindowStyle Hidden

        for ($attempt = 0; $attempt -lt 20; $attempt++) {
            Start-Sleep -Seconds 1

            $listener = Get-NetTCPConnection -LocalAddress $HostName -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
            if ($listener) {
                $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
                Add-Content -Path $LogFile -Value "[$timestamp] Preview server restarted; PID $($process.Id)."
                break
            }
        }
    }

    Start-Sleep -Seconds 5
}
