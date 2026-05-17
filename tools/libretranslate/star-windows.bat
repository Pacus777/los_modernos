@echo off
cd /d %~dp0

if not exist venv (
    echo Creando entorno virtual de LibreTranslate...
    python -m venv venv
)

call venv\Scripts\activate

echo Instalando dependencias...
python -m pip install --upgrade pip setuptools wheel
pip install -r requirements.txt

echo Iniciando LibreTranslate en http://127.0.0.1:5000
libretranslate --host 127.0.0.1 --port 5000 --load-only es,en

pause