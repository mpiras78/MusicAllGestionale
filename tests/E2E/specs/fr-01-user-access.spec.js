const { test: anonymousTest, expect: anonymousExpect } = require('@playwright/test');
const { LoginPage } = require('../pages/login-page');

anonymousTest.describe('FR-01: Login e Accesso al sistema', () => {

    // Test di sicurezza: tentativo di accesso a pagina protetta senza autenticazione
    anonymousTest('FR-01: La gestione utenti/index richiede un accesso autenticato', async ({ page }) => {
        await page.goto('index.php'); // Prova ad accedere ad una pagina protetta
        await anonymousExpect(page).toHaveURL(/\/login\.php$/); // Deve reindirizzare a login
    });

    // AC-1.1 - Login OK
    anonymousTest('AC-1.1: Login OK con credenziali corrette', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();

        await loginPage.login('admin', 'pe3upuroCe');

        await anonymousExpect(page).toHaveURL(/\/(index|calendario)\.php$/);
    });

    // AC-1.2 - Username mancante
    anonymousTest('AC-1.2: Username mancante attiva la validazione', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();

        await loginPage.login('', 'passwordSegreta');

        const isInvalid = await loginPage.isUsernameInvalid();
        anonymousExpect(isInvalid).toBe(true);
    });

    // AC-1.3 - Password mancante
    anonymousTest('AC-1.3: Password mancante attiva la validazione', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();

        await loginPage.login('admin', '');

        const isInvalid = await loginPage.isPasswordInvalid();
        anonymousExpect(isInvalid).toBe(true);
    });

    // AC-1.4 - Credenziali errate
    anonymousTest('AC-1.4: Credenziali errate mostrano messaggio di errore', async ({ page }) => {
        const loginPage = new LoginPage(page);
        await loginPage.visit();

        await loginPage.login('utenteErrato', 'passwordSbagliata');

        await anonymousExpect(loginPage.errorMessage).toBeVisible();
    });
});