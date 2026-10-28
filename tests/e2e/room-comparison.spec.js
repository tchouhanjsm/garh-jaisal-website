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
    await expect(dialog.locator('table')).toContainText('Listed features by room');

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

    const allFeatureRows = dialog.locator('tr[data-room-feature-row]');
    const differingFeatureRows = dialog.locator('tr[data-room-feature-row][data-room-feature-diff="true"]');
    const equalFeatureRows = dialog.locator('tr[data-room-feature-row][data-room-feature-diff="false"]');
    const allFeatureCount = await allFeatureRows.count();
    const differingFeatureCount = await differingFeatureRows.count();
    expect(allFeatureCount, 'Feature-by-feature matrix should render the listed room details').toBeGreaterThan(0);
    expect(differingFeatureCount, 'These two rooms should expose actual listed-feature differences').toBeGreaterThan(0);
    expect(allFeatureCount).toBeGreaterThan(differingFeatureCount);
    await expect(equalFeatureRows.first()).toBeVisible();

    const differenceControl = dialog.locator('[data-room-comparison-difference-control]');
    const differenceToggle = dialog.locator('[data-room-comparison-differences]');
    const differenceStatus = dialog.locator('[data-room-comparison-filter-status]');
    await expect(differenceControl).toBeVisible();
    await expect(differenceToggle).not.toBeChecked();
    await differenceToggle.check();
    await expect(differenceStatus).toBeVisible();
    await expect(differenceStatus).toContainText('of ' + allFeatureCount + ' listed features');
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(differingFeatureCount);
    await expect(dialog.locator('tr[data-room-feature-row][data-room-feature-diff="false"]:visible')).toHaveCount(0);
    await differenceToggle.uncheck();
    await expect(differenceStatus).toBeHidden();
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(allFeatureCount);

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

    // Sprint 77: test the filter with three rooms, then prove it recalculates
    // when a room is removed while the filter remains enabled.
    const threeRoomRows = dialog.locator('tr[data-room-feature-row]');
    const threeRoomTotal = await threeRoomRows.count();
    const threeRoomDiffCount = await dialog.locator('tr[data-room-feature-row][data-room-feature-diff="true"]').count();
    expect(threeRoomTotal).toBeGreaterThan(0);
    expect(threeRoomDiffCount).toBeGreaterThan(0);
    await differenceToggle.check();
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(threeRoomDiffCount);

    const secondId = await secondCard.getAttribute('id');
    const removeSecondRoom = dialog.locator('[data-room-compare-remove="' + secondId + '"]');
    await removeSecondRoom.focus();
    await removeSecondRoom.click();
    await expect(dialog.locator('thead th')).toHaveCount(3);
    await expect(dialog.locator('[data-room-compare-remove]')).toHaveCount(2);
    await expect(dialog.locator('[data-room-compare-remove]').nth(1)).toBeFocused();

    const remainingFeatureRows = dialog.locator('tr[data-room-feature-row]');
    const remainingTotal = await remainingFeatureRows.count();
    const remainingDiffCount = await dialog.locator('tr[data-room-feature-row][data-room-feature-diff="true"]').count();
    expect(remainingTotal).toBeGreaterThan(0);
    expect(remainingDiffCount).toBeGreaterThan(0);
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(remainingDiffCount);
    await expect(differenceStatus).toContainText('of ' + remainingTotal + ' listed features');
    await differenceToggle.uncheck();
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(remainingTotal);

    await dialog.locator('[data-room-comparison-clear]').click();
    await expect(dialog.locator('table')).toHaveCount(0);
    await expect(dialog.locator('[data-room-comparison-status]')).toContainText('Choose two or three rooms');
    await dialog.locator('[data-room-comparison-close]').first().click();
    await expect(page.locator('[data-room-result-status]')).toContainText('Showing 7 of 7 rooms.');
    await expect(page.locator('[data-room-result-status]')).not.toContainText('selected for comparison');
  });

  test('explains when selected rooms have no listed-feature differences', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Feature comparison edge case runs once on desktop.');

    await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    const cards = page.locator('.rooms-page .room-card');
    const first = cards.nth(0);
    const second = cards.nth(1);

    // Create a controlled same-feature fixture using the rendered first room's list.
    const firstFeatureMarkup = await first.locator('.room-features').innerHTML();
    await second.locator('.room-features').evaluate((element, markup) => {
      element.innerHTML = markup;
    }, firstFeatureMarkup);

    await first.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-comparison-open]').click();

    const dialog = page.locator('[data-room-comparison-dialog]');
    const rows = dialog.locator('tr[data-room-feature-row]');
    const toggle = dialog.locator('[data-room-comparison-differences]');
    const status = dialog.locator('[data-room-comparison-filter-status]');
    await expect(rows).not.toHaveCount(0);
    await expect(dialog.locator('tr[data-room-feature-row][data-room-feature-diff="true"]')).toHaveCount(0);

    await toggle.check();
    await expect(status).toBeVisible();
    await expect(status).toContainText('No listed feature differences found');
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(0);

    await toggle.uncheck();
    await expect(status).toBeHidden();
    await expect(dialog.locator('tr[data-room-feature-row]:visible')).toHaveCount(await rows.count());
  });

  test('keeps the selected-room shortlist available when filters hide room cards', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'desktop-1280', 'Shortlist/filter continuity runs once on desktop.');

    await page.goto('/room.php', { waitUntil: 'domcontentloaded' });
    const cards = page.locator('.rooms-page .room-card');
    const first = cards.nth(0);
    const second = cards.nth(1);

    await first.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-compare-toggle]').click();

    const shortlist = page.locator('[data-room-comparison-shortlist]');
    const shortlistList = shortlist.locator('[data-room-comparison-shortlist-items]');
    const shortlistItems = shortlistList.locator('li');
    const review = shortlist.locator('[data-room-shortlist-review]');
    await expect(shortlist).toBeVisible();
    await expect(shortlistItems).toHaveCount(2);
    await expect(review).toBeEnabled();

    await page.locator('[data-room-search]').fill('no-matching-room-query-9284');
    await expect(page.locator('[data-room-result-status]')).toContainText('Showing 0 of 7 rooms.');
    await expect(first).toBeHidden();
    await expect(second).toBeHidden();
    await expect(shortlist).toBeVisible();
    await expect(shortlistList).toContainText(await first.locator('h3').textContent());
    await expect(shortlistList).toContainText(await second.locator('h3').textContent());

    const revealSelectedRooms = shortlist.locator('[data-room-shortlist-reveal]');
    await expect(revealSelectedRooms).toBeVisible();
    await revealSelectedRooms.click();
    await expect(page.locator('[data-room-result-status]')).toContainText('Showing 7 of 7 rooms.');
    await expect(first).toBeVisible();
    await expect(second).toBeVisible();
    await expect(first.locator('[data-room-compare-toggle]')).toBeFocused();

    await page.locator('[data-room-search]').fill('no-matching-room-query-9284');
    await expect(first).toBeHidden();
    await expect(second).toBeHidden();
    await expect(revealSelectedRooms).toBeVisible();
    await review.click();
    const dialog = page.locator('[data-room-comparison-dialog]');
    await expect(dialog).toBeVisible();
    await expect(dialog.locator('thead th')).toHaveCount(3);
    await expect(dialog.locator('table')).toContainText('Listed features by room');

    await dialog.locator('[data-room-comparison-close]').first().click();
    const roomSearch = page.locator('[data-room-search]');
    await shortlist.locator('[data-room-shortlist-clear]').click();
    await expect(shortlist).toBeHidden();
    await expect(roomSearch).toBeFocused();

    // The per-room removal path preserves focus as each shortlist item disappears.
    await roomSearch.fill('');
    await first.locator('[data-room-compare-toggle]').click();
    await second.locator('[data-room-compare-toggle]').click();
    const firstId = await first.getAttribute('id');
    await shortlist.locator('[data-room-shortlist-remove="' + firstId + '"]').click();
    await expect(shortlistItems).toHaveCount(1);
    await expect(shortlistItems.locator('[data-room-shortlist-remove]')).toBeFocused();
    await shortlistItems.locator('[data-room-shortlist-remove]').click();
    await expect(shortlist).toBeHidden();
    await expect(roomSearch).toBeFocused();
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
