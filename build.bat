@echo on

set distFolder = "dist\"

echo Build started

rem robocopy languages\ dist\languages /E - optional echo

rmdir /s/q .\dist
robocopy code\ dist\code > nul
robocopy languages\ dist\languages > nul
robocopy assets\ dist\assets > nul
robocopy .\ .\dist readme.txt > nul

echo Done