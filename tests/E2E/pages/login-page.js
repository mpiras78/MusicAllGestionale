const { BasePage } = require('./base-page');

class LoginPage extends BasePage {
    constructor(page) {
        super(page);
        this.usernameInput = this.page.getByTestId('login-username');
        this.passwordInput = this.page.getByTestId('login-password');
        this.submitButton = this.page.getByTestId('login-submit');
        this.errorMessage = this.page.getByText('Credenziali non valide');
        this.forgotPasswordLink = this.page.getByRole('link', { name: /Hai dimenticato la password\?/i });
    }

    async visit() {
        await super.visit('login.php');
    }

    async login(username, password) {
        if (username !== undefined) await this.usernameInput.fill(username);
        if (password !== undefined) await this.passwordInput.fill(password);
        await this.submitButton.click();
    }

    async isUsernameInvalid() {
        return await this.usernameInput.evaluate((el) => !el.checkValidity());
    }

    async isPasswordInvalid() {
        return await this.passwordInput.evaluate((el) => !el.checkValidity());
    }

    async openForgotPassword() {
        await this.forgotPasswordLink.click();
    }
}

module.exports = { LoginPage };