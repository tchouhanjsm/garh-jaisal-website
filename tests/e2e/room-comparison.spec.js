const { test, expect } = require('@playwright/test');

test.describe('Room comparison journey', () => {
  test('compares up to three rooms and preserves the existing booking destination', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Run the comparison interaction contract once.');

    await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    const cards = page.locator('.rooms-page .room-card');
    await expect(cards).toHaveCount(7);

    const firstCard = cards.nth(0);
    const secondCard = cards.nth(1);
    const thirdCard = cards.nth(2);
    const firstToggle = firstCard.locator('[data-room-compare-toggle]');
    const secondToggle = secondCard.locator('[data-room-compare-toggle]');

    await expect(firstToggle).toBeVisible();
    await firstToggle.click();
    await expect(firstToggle).toHaveAttribute('aria-pressed', 'true');
    await expect(firstCard.locator('[data-room-comparison-open]')).toBeHidden();

    await secondToggle.click();
    const openTwo = secondCard.locator('[data-room-comparison-open]');
    await expect(openTwo).toBeVisible();
    await expect(openTwo).toContainText('2');
    await openTwo.click();

    const dialog = page.locator('[data-room-comparison-dialog]');
    await expect(dialog).toBeVisible();
    await expect(dialog.locator('table')).toBeVisible();
    await expect(dialog.locator('thead th')).toHaveCount(3);
    await expect(dialog.locator('table')).toContainText(await firstCard.locator('h3').textContent());
    await expect(dialog.locator('table')).toContainText('Listed features');

    const comparisonContent = dialog.locator('[data-room-comparison-content]');
    await expect(comparisonContent).toHaveAttribute('role', 'region');
    await expect(comparisonContent).toHaveAttribute('tabindex', '0');
    await expect(comparisonContent).toHaveAccessibleName('Selected room comparison table');
    await expect(dialog.locator('#room-comparison-scroll-hint')).toContainText('scroll the table horizontally');

    const photoRow = dialog.locator('tbody tr').filter({ hasText: 'Room photo' });
    await expect(photoRow).toHaveCount(1);
    const comparisonPhotos = photoRow.locator('img.room-comparison-photo');
    await expect(comparisonPhotos).toHaveCount(2);
    const firstPhoto = firstCard.locator('.photo-carousel-slide img').first();
    await expect(comparisonPhotos.nth(0)).toHaveAttribute('src', await firstPhoto.getAttribute('src'));
    await expect(comparisonPhotos.nth(0)).toHaveAttribute('alt', await firstPhoto.getAttribute('alt'));
    await expect(comparisonPhotos.nth(0)).toHaveAttribute('sizes', '150px');
    await expect(comparisonPhotos.nth(1)).toHaveAttribute('sizes', '150px');
    const originalBookingHref = await firstCard.locator('.room-book').getAttribute('href');
    await expect(dialog.locator('a.room-comparison-availability').first()).toHaveAttribute('href', originalBookingHref);
    await expect(dialog.locator('[data-room-compare-remove]')).toHaveCount(2);

    await dialog.locator('[data-room-comparison-close]').first().click();
    const thirdToggle = thirdCard.locator('[data-room-compare-toggle]');
    await thirdToggle.click();
    const openThree = thirdCard.locator('[data-room-comparison-open]');
    await expect(openThree).toBeVisible();
    await expect(openThree).toContainText('3');
    await expect(cards.nth(3).locator('[data-room-compare-toggle]')).toBeDisabled();
    await openThree.click();
    await expect(dialog.locator('thead th')).toHaveCount(4);

    const secondId = await secondCard.getAttribute('id');
    await dialog.locator('[data-room-compare-remove="' + secondId + '"]').click();
    await expect(dialog.locator('thead th')).toHaveCount(3);
    await expect(dialog.locator('[data-room-compare-remove]')).toHaveCount(2);

    await dialog.locator('[data-room-comparison-clear]').click();
    await expect(dialog.locator('table')).toHaveCount(0);
    await expect(dialog.locator('[data-room-comparison-status]')).toContainText('Choose two or three rooms');
    await dialog.locator('[data-room-comparison-close]').first().click();
    await expect(page.locator('[data-room-result-status]')).toContainText('Showing 7 of 7 rooms.');
    await expect(page.locator('[data-room-result-status]')).not.toContainText('selected for comparison');
  });

  test('keeps the compare dialog inside the viewport from mobile through desktop widths', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Exercise all five design widths in one browser project.');

    for (const width of [320, 390, 768, 1280, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
      const cards = page.locator('.rooms-page .room-card');
      await cards.nth(0).locator('[data-room-compare-toggle]').click();
      await cards.nth(1).locator('[data-room-compare-toggle]').click();
      await cards.nth(1).locator('[data-room-comparison-open]').click();

      const dialog = page.locator('[data-room-comparison-dialog]');
      await expect(dialog).toBeVisible();
      const comparisonContent = dialog.locator('[data-room-comparison-content]');
      await expect(comparisonContent).toHaveAttribute('tabindex', '0');
      await comparisonContent.focus();
      await expect(comparisonContent).toBeFocused();
      if (width <= 390) {
        const horizontalScrollAvailable = await comparisonContent.evaluate((element) => element.scrollWidth > element.clientWidth);
        expect(horizontalScrollAvailable, 'Comparison columns should scroll inside the focused region at ' + width + 'px').toBe(true);
      }
      const pageWidth = await page.evaluate(() => document.documentElement.scrollWidth);
      expect(pageWidth, 'The page must not overflow horizontally at ' + width + 'px').toBeLessThanOrEqual(width + 1);
      const bounds = await dialog.boundingBox();
      expect(bounds).not.toBeNull();
      expect(bounds.x, 'Dialog left edge must remain in the viewport at ' + width + 'px').toBeGreaterThanOrEqual(0);
      expect(bounds.x + bounds.width, 'Dialog right edge must remain in the viewport at ' + width + 'px').toBeLessThanOrEqual(width + 1);
    }
  });

  test('adds compared room options to the shared Trip while preserving experience choices', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Run the cross-page Trip composer integration once.');
    test.setTimeout(60_000);

    await page.goto('/catalogue.php', { waitUntil: 'domcontentloaded' });
    await page.locator('[data-product-id="heritage-room-stay"] [data-trip-add]').click();

    await page.goto('/swiss-tent-safari.php', { waitUntil: 'domcontentloaded' });
    await page.locator('[data-trip-add][data-trip-id="swiss-tent-safari"]').first().click();
    await expect(page.locator('[data-trip-count]')).toHaveText('2');

    await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    const cards = page.locator('.rooms-page .room-card');
    const first = cards.nth(0);
    const second = cards.nth(1);
    const firstName = (await first.locator('h3').innerText()).trim();
    const secondName = (await second.locator('h3').innerText()).trim();

    await first.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-comparison-open]').click();

    const dialog = page.locator('[data-room-comparison-dialog]');
    const addRoomOptions = dialog.locator('[data-room-comparison-trip]');
    await expect(addRoomOptions).toBeVisible();
    await expect(addRoomOptions).toContainText('Add room options to Trip');
    await addRoomOptions.click();

    await expect(dialog).toBeHidden();
    await expect(page.locator('[data-trip-panel]')).toBeVisible();
    await expect(page.locator('[data-trip-items]')).toContainText('Room options: ' + firstName + ', ' + secondName);
    await expect(page.locator('[data-trip-items]')).toContainText('Overnight Safari in a Swiss Tent');
    await expect(page.locator('[data-trip-items]')).not.toContainText('Heritage room stay');
    await expect(page.locator('[data-trip-close]')).toBeFocused();

    const sendHref = await page.locator('[data-trip-send]').getAttribute('href');
    const message = new URL(sendHref).searchParams.get('text');
    expect(message).toContain('Room options: ' + firstName + ', ' + secondName);
    expect(message).toContain('Overnight Safari in a Swiss Tent');

    const stored = await page.evaluate(() => sessionStorage.getItem('gj-trip-items-v1'));
    const expectedRoomOptionsId = 'room-options-' + (await first.getAttribute('id')).replace(/^room-/, '') + '-' + (await second.getAttribute('id')).replace(/^room-/, '');
    expect(stored).toContain(expectedRoomOptionsId);
    expect(stored).toContain('swiss-tent-safari');
    expect(stored).not.toContain('2026-11-');
    expect(stored).not.toContain('"travellers"');
  });

  test('carries the selected room shortlist into a trusted contact enquiry', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Run the end-to-end context handoff once.');
    await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    const cards = page.locator('.rooms-page .room-card');
    const first = cards.nth(0);
    const second = cards.nth(1);
    const firstName = (await first.locator('h3').textContent()).trim();
    const secondName = (await second.locator('h3').textContent()).trim();
    await first.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-comparison-open]').click();

    const enquiryLink = page.locator('[data-room-comparison-dialog] [data-room-comparison-enquiry]');
    await expect(enquiryLink).toBeVisible();
    const targetUrl = new URL(await enquiryLink.getAttribute('href'), 'http://127.0.0.1:8080');
    expect(targetUrl.pathname).toBe('/contact.php');
    expect(targetUrl.searchParams.getAll('rooms[]')).toEqual([
      (await first.getAttribute('id')).replace(/^room-/, ''),
      (await second.getAttribute('id')).replace(/^room-/, ''),
    ]);

    await enquiryLink.click();
    await expect(page).toHaveURL(/\/contact\.php\?rooms/);
    const context = page.locator('.contact-rooms-context');
    await expect(context).toBeVisible();
    await expect(context).toContainText(firstName);
    await expect(context).toContainText(secondName);
    await expect(page.locator('#message')).toHaveValue(new RegExp('these rooms: ' + firstName + ', ' + secondName));
    await expect(page.locator('input[name="enquiry_context_token"]')).toHaveAttribute('value', /[a-f0-9]{32}/);
  });
});
