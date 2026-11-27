param(
    [string]$PhpExe = 'C:\xampp\php\php.exe',
    [string]$TaskName = 'Fah Sai Place - Late Fee Accrual'
)

$scriptPath = Join-Path $PSScriptRoot 'accrue_late_fines.php'
$projectPath = Split-Path $PSScriptRoot -Parent

if (-not (Test-Path -LiteralPath $PhpExe -PathType Leaf)) {
    throw "PHP executable was not found: $PhpExe"
}
if (-not (Test-Path -LiteralPath $scriptPath -PathType Leaf)) {
    throw "Late-fee task script was not found: $scriptPath"
}

$action = New-ScheduledTaskAction `
    -Execute $PhpExe `
    -Argument ('"{0}"' -f $scriptPath) `
    -WorkingDirectory $projectPath
$trigger = New-ScheduledTaskTrigger -Daily -At '12:05AM'
$principal = New-ScheduledTaskPrincipal `
    -UserId ([Security.Principal.WindowsIdentity]::GetCurrent().Name) `
    -LogonType Interactive `
    -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 10) `
    -MultipleInstances IgnoreNew

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Principal $principal `
    -Settings $settings `
    -Description 'Accrue contractual late fees for unpaid invoices once per day.' `
    -Force | Out-Null

Write-Output "Registered daily task '$TaskName' at 12:05 AM."
