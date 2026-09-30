class BasePage {
    constructor(page) {
        this.page = page;
    }

    async visit(path) {
        await this.page.goto(path);
    }
}

module.exports = { BasePage };
