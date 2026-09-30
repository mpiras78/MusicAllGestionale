const e2eConfig = {
    adminUsername: process.env.E2E_ADMIN_USERNAME || '',
    adminPassword: process.env.E2E_ADMIN_PASSWORD || ''
};

function hasAdminCredentials() {
    return Boolean(e2eConfig.adminUsername && e2eConfig.adminPassword);
}

module.exports = {
    e2eConfig,
    hasAdminCredentials
};
