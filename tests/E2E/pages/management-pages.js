class UserManagementPage {
    constructor(page) {
        this.page = page;
    }

    async expectLoaded(expect) {
        await expect(this.page.getByTestId('user-management-page')).toBeVisible();
        await expect(this.page.getByTestId('users-table')).toBeVisible();
        await expect(this.page.getByTestId('users-create')).toBeVisible();
    }

    async openCreateForm() {
        await this.page.getByTestId('users-create').click();
    }

    async expectCreateForm(expect) {
        await expect(this.page.getByTestId('users-form')).toBeVisible();
        await expect(this.page.getByTestId('users-role')).toBeVisible();
        await expect(this.page.getByTestId('users-teacher-link')).toBeVisible();
    }
}

class StudentManagementPage {
    constructor(page) {
        this.page = page;
    }

    async expectLoaded(expect) {
        await expect(this.page.getByTestId('student-management-page')).toBeVisible();
        await expect(this.page.getByTestId('students-table')).toBeVisible();
        await expect(this.page.getByTestId('students-create')).toBeVisible();
    }

    async openCreateForm() {
        await this.page.getByTestId('students-create').click();
    }

    async expectCreateForm(expect) {
        await expect(this.page.getByTestId('students-form')).toBeVisible();
    }
}

class TeacherManagementPage {
    constructor(page) {
        this.page = page;
    }

    async expectLoaded(expect) {
        await expect(this.page.getByTestId('teacher-management-page')).toBeVisible();
        await expect(this.page.getByTestId('teachers-table')).toBeVisible();
        await expect(this.page.getByTestId('teachers-create')).toBeVisible();
    }

    async openCreateForm() {
        await this.page.getByTestId('teachers-create').click();
    }

    async expectCreateForm(expect) {
        await expect(this.page.getByTestId('teachers-form')).toBeVisible();
    }
}

module.exports = {
    UserManagementPage,
    StudentManagementPage,
    TeacherManagementPage
};
