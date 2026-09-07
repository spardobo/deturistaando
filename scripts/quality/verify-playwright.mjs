import assert from "node:assert/strict";
import { chromium } from "@playwright/test";

const browser = await chromium.launch();

try {
    const page = await browser.newPage();
    await page.setContent("<main><h1>Playwright is ready</h1></main>");

    assert.equal(await page.getByRole("heading").textContent(), "Playwright is ready");
    console.log("Playwright launched Chromium successfully.");
} finally {
    await browser.close();
}
