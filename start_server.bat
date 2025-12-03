@echo off
title DataNexus Server
color 0b

echo ==================================================
echo               DATANEXUS SERVER
echo ==================================================
echo.
echo [INFO] Iniciando servidor de desenvolvimento...
echo [INFO] Acesso: http://localhost:8080
echo.
echo Pressione Ctrl+C para encerrar.
echo.

:: Abre o navegador padrão automaticamente
start http://localhost:8080

:: Inicia o servidor embutido do PHP apontando para a pasta 'src'
:: O comando -t define a raiz do documento
php -S localhost:8080 -t src

pause