// @ts-check
import { test, expect } from '@playwright/test';

/**
 * E-15: Test de flujo completo de onboarding del emprendedor.
 * 
 * Precondiciones del entorno:
 * - El servidor Laravel local debe estar corriendo.
 * - Debe existir el usuario con las credenciales indicadas (perfil incompleto inicialmente).
 */
test.describe('Flujo de Onboarding de Emprendedor', () => {

    test('Debe redirigir al perfil, mostrar el checklist, y permitir desbloquear el dashboard tras completarlo', async ({ page }) => {
        
        // 1. Iniciar sesión como emprendedor con perfil incompleto
        await page.goto('/login');
        await page.fill('input[type="email"]', 'emprendedor.onboarding@wayna.test');
        await page.fill('input[type="password"]', 'password');
        await page.click('button[type="submit"]');

        // 2. Intentar ingresar al dashboard y comprobar la redirección al formulario de edición de perfil
        await page.goto('/emprendedor/dashboard');
        await page.waitForURL('**/emprendedor/perfil/editar');
        
        // 3. Comprobar que el contenedor del checklist de onboarding esté visible
        const checklist = page.locator('[data-testid="emprendedor-onboarding-checklist"]');
        await expect(checklist).toBeVisible();

        // 4. Comprobar que se visualizan los tres ítems obligatorios
        await expect(page.locator('[data-testid="onboarding-item-datos-principales"]')).toBeVisible();
        await expect(page.locator('[data-testid="onboarding-item-descripcion"]')).toBeVisible();
        await expect(page.locator('[data-testid="onboarding-item-fotografia"]')).toBeVisible();

        // 5. Rellenar los campos requeridos en el formulario de edición de perfil
        await page.fill('input#nombre', 'Juan Carlos');
        await page.fill('input#apellidos', 'Pérez Quispe');
        await page.selectOption('select#tipo_emprendimiento', 'artesania');
        await page.selectOption('select#departamento', 'la_paz');
        
        // Introducir una descripción que cumpla el mínimo de 10 caracteres obligatorios
        await page.fill('textarea#descripcion', 'Artesanías y tejidos andinos elaborados 100% a mano con lana de alpaca.');

        // 6. Subir fotografía de perfil simulando la carga del archivo
        // El input de tipo archivo oculto dentro del dropzone posee id="fotografia"
        const fileChooserPromise = page.waitForEvent('filechooser');
        await page.click('.admin-media-dropzone'); // Hace clic en la zona de carga
        const fileChooser = await fileChooserPromise;
        
        await fileChooser.setFiles({
            name: 'perfil.jpg',
            mimeType: 'image/jpeg',
            buffer: Buffer.from('fake-gorgeous-portrait-image-data')
        });

        // 7. Enviar formulario para guardar los cambios del perfil público
        await page.click('button[type="submit"]');

        // 8. El perfil ahora está completo; el middleware EnsureProfileComplete debe permitir
        // acceder y redirigir exitosamente al dashboard del emprendedor
        await page.waitForURL('**/emprendedor/dashboard');
        
        // El checklist en el dashboard debe mostrarse como completo o dejar de exigir el bloqueo
        const exitoAlerta = page.locator('text=¡Excelente trabajo! Tu perfil está listo');
        await expect(exitoAlerta).toBeVisible();
    });
});
