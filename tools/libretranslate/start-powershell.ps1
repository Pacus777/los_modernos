Set-Location $PSScriptRoot

if (!(Test-Path "venv")) {
    Write-Host "Creando entorno virtual de LibreTranslate..."
    python -m venv venv
}

.\venv\Scripts\Activate.ps1

Write-Host "Instalando dependencias..."
python -m pip install --upgrade pip setuptools wheel
pip install -r requirements.txt

Write-Host "Iniciando LibreTranslate en http://127.0.0.1:5000"
libretranslate --host 127.0.0.1 --port 5000