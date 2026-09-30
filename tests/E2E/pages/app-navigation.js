class AppNavigation {
    constructor(page) {
        this.page = page;
    }

    async openManagement() {
        await this.page.getByTestId('nav-management').click();
    }

    async openStudents() {
        await this.openManagement();
        await this.page.getByTestId('nav-students').click();
    }

    async openTeachers() {
        await this.openManagement();
        await this.page.getByTestId('nav-teachers').click();
    }

    async openUserManagement() {
        await this.page.getByTestId('nav-user-menu').click();
        await this.page.getByTestId('nav-users').click();
    }
}

module.exports = { AppNavigation };
