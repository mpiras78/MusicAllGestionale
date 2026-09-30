const base = require('@playwright/test');
const { e2eConfig, hasAdminCredentials } = require('../support/e2e-config');
const { LoginPage } = require('../pages/login-page');

const test = base.test.extend({
    adminPage: async ({ page }, use, testInfo) => {
        testInfo.skip(
            !hasAdminCredentials(),
            'Definire E2E_ADMIN_USERNAME e E2E_ADMIN_PASSWORD per eseguire test autenticati.'
        );

        const loginPage = new LoginPage(page);
        await loginPage.visit();
        await loginPage.login(e2eConfig.adminUsername, e2eConfig.adminPassword);

        await use(page);
    }
});

module.exports = {
    test,
    expect: base.expect
};
