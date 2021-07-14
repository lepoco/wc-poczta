# Just copy files, because I do not want to
# Leszek Pomianowski CC0

# DATA
$directories = @('code', 'languages')
$files = @('wc-poczta.php', 'readme.txt', 'LICENSE')


# INFO
Write-Host "============================" -ForegroundColor White
Write-Host "RDEV" -ForegroundColor Red -NoNewline
Write-Host " | " -ForegroundColor White -NoNewline
Write-Host "Poweshell Quick Build"
Write-Host "============================" -ForegroundColor White

# Do what you need to do
$ROOT_PATH = '.\'
$DIST_PATH = '.\dist\'

if (Test-Path -Path $DIST_PATH) {
  Remove-Item -path $DIST_PATH -Recurse -Force
  Write-Host "[OK] " -ForegroundColor Green -NoNewline
  Write-Host "Directory $DIST_PATH removed!" -ForegroundColor White
}

foreach ($directory in $directories) {
  if (Test-Path -Path $ROOT_PATH$directory) {
    Copy-Item "$ROOT_PATH$directory" -Destination "$DIST_PATH$directory" -Recurse -Force
    Write-Host "[OK] " -ForegroundColor Green -NoNewline
  } else {
    Write-Host "[ER] " -ForegroundColor Red -NoNewline
  }

  Write-Host "Copying a directory: " -ForegroundColor White -NoNewline
  Write-Host $ROOT_PATH$directory -NoNewline
  Write-Host ", to: " -ForegroundColor White -NoNewline
  Write-Host $DIST_PATH$directory
}

foreach ($file in $files) {
  if (-not(Test-Path -Path $file -PathType Leaf)) {
    Write-Host "[ER] " -ForegroundColor Red -NoNewline -Force
  } else {
    Copy-Item "$ROOT_PATH$file" -Destination "$DIST_PATH$file" -Force
    Write-Host "[OK] " -ForegroundColor Green -NoNewline
  }
  
  Write-Host "Copying a file: " -ForegroundColor White -NoNewline
  Write-Host $ROOT_PATH$file -NoNewline
  Write-Host ", to: " -ForegroundColor White -NoNewline
  Write-Host $DIST_PATH$file
}

# Write-Host ""
# git branch -a
# git remote -v

Write-Host ""
Write-Host "DONE." -ForegroundColor Green