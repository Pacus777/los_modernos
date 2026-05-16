function cargarImagen(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

function roundRect(ctx, x, y, w, h, r) {
    const radio = Math.min(r, w / 2, h / 2);
    ctx.beginPath();
    ctx.moveTo(x + radio, y);
    ctx.arcTo(x + w, y, x + w, y + h, radio);
    ctx.arcTo(x + w, y + h, x, y + h, radio);
    ctx.arcTo(x, y + h, x, y, radio);
    ctx.arcTo(x, y, x + w, y, radio);
    ctx.closePath();
}

function textoCentrado(ctx, texto, y, font, color, centerX) {
    ctx.fillStyle = color;
    ctx.font = font;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'alphabetic';
    ctx.fillText(texto, centerX, y);
}

function dibujarLineasCentradas(ctx, lineas, yInicio, lineHeight, cx, color) {
    ctx.fillStyle = color;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'alphabetic';
    let y = yInicio;
    lineas.forEach((linea) => {
        ctx.fillText(linea, cx, y);
        y += lineHeight;
    });
    return y;
}

function partirLinea(ctx, texto, maxAncho) {
    const palabras = texto.split(' ');
    const lineas = [];
    let actual = '';

    palabras.forEach((palabra) => {
        const prueba = actual ? `${actual} ${palabra}` : palabra;
        if (ctx.measureText(prueba).width > maxAncho && actual) {
            lineas.push(actual);
            actual = palabra;
        } else {
            actual = prueba;
        }
    });
    if (actual) {
        lineas.push(actual);
    }
    return lineas;
}

/**
 * Genera PNG de la tarjeta QR para descargar (sin URL ni meta; nombre centrado).
 */
export async function exportarTarjetaQrPng({
    qrSrc,
    nombreCompleto,
    titulo,
    subtitulo,
    marca = 'Wayna Conecta',
    logoSrc = '/images/logo-naranja.png',
}) {
    const W = 360;
    const H = 500;
    const escala = 2;
    const cx = W / 2;
    const canvas = document.createElement('canvas');
    canvas.width = W * escala;
    canvas.height = H * escala;
    const ctx = canvas.getContext('2d');
    ctx.scale(escala, escala);

    ctx.fillStyle = '#fafaf9';
    ctx.fillRect(0, 0, W, H);

    roundRect(ctx, 8, 8, W - 16, H - 16, 22);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.strokeStyle = '#f07e26';
    ctx.lineWidth = 3;
    ctx.stroke();

    textoCentrado(ctx, titulo, 44, '600 16px system-ui, sans-serif', '#78716c', cx);

    const qrImg = await cargarImagen(qrSrc);
    const qrSize = 196;
    const qrY = 62;
    ctx.drawImage(qrImg, (W - qrSize) / 2, qrY, qrSize, qrSize);

    let y = qrY + qrSize + 22;
    ctx.strokeStyle = '#e7e5e4';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(48, y);
    ctx.lineTo(cx - 22, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(cx + 22, y);
    ctx.lineTo(W - 48, y);
    ctx.stroke();

    ctx.fillStyle = '#f07e26';
    ctx.beginPath();
    ctx.arc(cx, y, 14, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#fff';
    ctx.font = '14px system-ui';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('▣', cx, y + 1);

    const bloqueInicio = y + 36;
    const bloqueFin = H - 72;
    const nombreY = bloqueInicio + (bloqueFin - bloqueInicio) * 0.38;
    textoCentrado(
        ctx,
        nombreCompleto,
        nombreY,
        '700 22px system-ui, sans-serif',
        '#1c1917',
        cx,
    );

    ctx.font = '500 13px system-ui, sans-serif';
    const lineasSub = partirLinea(ctx, subtitulo, W - 64);
    const subY = nombreY + 30;
    dibujarLineasCentradas(ctx, lineasSub, subY, 18, cx, '#78716c');

    try {
        const logo = await cargarImagen(logoSrc);
        const logoH = 28;
        const logoW = (logo.width / logo.height) * logoH;
        ctx.drawImage(logo, cx - logoW / 2, H - 54, logoW, logoH);
    } catch {
        /* logo opcional */
    }

    textoCentrado(ctx, marca, H - 20, '700 11px system-ui, sans-serif', '#f07e26', cx);

    return canvas.toDataURL('image/png');
}

export function descargarDataUrl(dataUrl, nombreArchivo) {
    const enlace = document.createElement('a');
    enlace.href = dataUrl;
    enlace.download = nombreArchivo;
    document.body.appendChild(enlace);
    enlace.click();
    enlace.remove();
}
