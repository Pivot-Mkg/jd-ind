$ErrorActionPreference = 'Stop'

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $repoRoot.Path

$failures = New-Object 'System.Collections.Generic.List[string]'

$forbiddenPaths = @(
    'filestructure/contact.php',
    'filestructure/forms/contact.php',
    'forms/contact.php'
)

foreach ($path in $forbiddenPaths) {
    if (Test-Path -LiteralPath $path) {
        $failures.Add("Forbidden legacy contact endpoint exists: $path")
    }
}

$scanFiles = Get-ChildItem -Recurse -File -Include *.html,*.php
foreach ($file in $scanFiles) {
    $relativePath = $file.FullName
    if ($relativePath.StartsWith($repoRoot.Path, [System.StringComparison]::OrdinalIgnoreCase)) {
        $relativePath = $relativePath.Substring($repoRoot.Path.Length).TrimStart('\', '/')
    }

    $actionMatches = Select-String -Path $file.FullName -Pattern 'action\s*=\s*["''](?<target>[^"''>]+)["'']' -AllMatches
    foreach ($actionMatch in $actionMatches) {
        foreach ($regexMatch in $actionMatch.Matches) {
            $target = $regexMatch.Groups['target'].Value.Trim()
            if ($target -match 'contact[^"''>]*\.php' -and $target -notmatch 'assets/mail/contact-submit\.php') {
                $failures.Add("Unexpected contact form action in ${relativePath}:$($actionMatch.LineNumber) -> $target")
            }
        }
    }

    $legacyRefs = Select-String -Path $file.FullName -Pattern 'filestructure/forms/contact\.php|forms/contact\.php' -AllMatches
    foreach ($legacyRef in $legacyRefs) {
        $failures.Add("Legacy contact reference found in ${relativePath}:$($legacyRef.LineNumber)")
    }
}

if ($failures.Count -gt 0) {
    foreach ($failure in $failures) {
        Write-Error $failure
    }
    exit 1
}

Write-Output 'Contact endpoint guard passed: only assets/mail/contact-submit.php is referenced for contact submissions.'
