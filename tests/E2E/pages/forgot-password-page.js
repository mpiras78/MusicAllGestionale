// pages/forgot-password-page.js
const { BasePage } = require('./base-page');

class ForgotPasswordPage extends BasePage {
    constructor(page) {
        super(page);
        
        // Selettore del campo email (adatta se necessario)
        this.emailInput = this.page.getByLabel(/email/i);
        this.submitButton = this.page.getByRole('button', { name: /invia|reset|recupera/i });
        this.confirmationMessage = this.page.getByText("Se l'email è registrata, riceverai un link per resettare la password");
    }

    async submitEmail(email) {
        if (email !== undefined) {
            await this.emailInput.fill(email);
        }
        await this.submitButton.click();
    }

    // Verifica la validità HTML5 del campo
    async isEmailInvalid() {
        return await this.emailInput.evaluate((el) => !el.checkValidity());
    }

    // (Opzionale) Recupera il messaggio di validazione nativo HTML5
    async getEmailValidationMessage() {
        return await this.emailInput.evaluate((el) => el.validationMessage);
    }
}

module.exports = { ForgotPasswordPage };