const { test, expect } = require('../fixtures/authenticated-test');
const {
    StudentManagementPage,
    TeacherManagementPage
} = require('../pages/management-pages');
const { AppNavigation } = require('../pages/app-navigation');

test('FR-0X: la gestione soci e docenti resta separata nella navigazione', async ({ adminPage }) => {
    const navigation = new AppNavigation(adminPage);
    const studentManagementPage = new StudentManagementPage(adminPage);
    const teacherManagementPage = new TeacherManagementPage(adminPage);

    await navigation.openStudents();
    await studentManagementPage.expectLoaded(expect);
    await studentManagementPage.openCreateForm();
    await studentManagementPage.expectCreateForm(expect);

    await navigation.openTeachers();
    await teacherManagementPage.expectLoaded(expect);
    await teacherManagementPage.openCreateForm();
    await teacherManagementPage.expectCreateForm(expect);
});
