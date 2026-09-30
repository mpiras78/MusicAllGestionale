// specs/fr-02-forgot-password.spec.js
const { test: anonymousTest, expect: anonymousExpect } = require('@playwright/test');
const { LoginPage } = require('../pages/login-page');
const { ForgotPasswordPage } = require('../pages/forgot-password-page');

anonymousTest.describe('FR-02: Recupero Password', () => {

    // Prima di ogni test: partiamo dal Login e clicchiamo su "Hai dimenticato la password?"
    anonymousTest.beforeEach(async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();
        await loginPage.openForgotPassword();
        
        // Verifichiamo di essere atterrati sulla pagina di reset password
        await anonymousExpect(page).toHaveURL(/forgot-password\.php$/);
    });

    // Eseguito AUTOMATICAMENTE alla fine di OGNI singolo test
    anonymousTest.afterEach(async ({ page }) => {
        // Chiude la pagina corrente subito dopo il test
        await page.close();
    });

   // AC-2.1: formato email errata
anonymousTest('AC-2.1: formato email errata mostra messaggio di errore nativo', async ({ page }) => {
    const forgotPasswordPage = new ForgotPasswordPage(page);

    // Inseriamo un'email non valida e proviamo a inviare
    await forgotPasswordPage.submitEmail('email-non-valida');

    // 1. Verifichiamo che il campo email sia marcato come non valido dall'API HTML5
    const isInvalid = await forgotPasswordPage.isEmailInvalid();
    anonymousExpect(isInvalid).toBe(true);

    // 2. (Opzionale) Verifichiamo che sia presente un messaggio di errore nativo
    const validationMessage = await forgotPasswordPage.getEmailValidationMessage();
    anonymousExpect(validationMessage).not.toBe('');
});

    // AC-2.2: Email non presente
    anonymousTest('AC-2.2: email non presente mostra messaggio di conferma generico', async ({ page }) => {
        const forgotPasswordPage = new ForgotPasswordPage(page);

        await forgotPasswordPage.submitEmail('non.registrato@example.com');

        await anonymousExpect(forgotPasswordPage.confirmationMessage).toBeVisible();
    });

    // AC-2.3: Link reset inviato (Email presente)
    anonymousTest('AC-2.3: link reset inviato mostra messaggio di conferma', async ({ page }) => {
        const forgotPasswordPage = new ForgotPasswordPage(page);

        await forgotPasswordPage.submitEmail('utente.esistente@example.com');

        await anonymousExpect(forgotPasswordPage.confirmationMessage).toBeVisible();
    });
});