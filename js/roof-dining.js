'use strict';

const roofDining = document.querySelector('[data-roof-dining]');
if (roofDining) {
  const tabList = roofDining.querySelector('.roof-dining-tabs');
  const tabs = Array.from(tabList.querySelectorAll('a[href^="#"]'));
  const panels = tabs.map(tab => document.getElementById(tab.hash.slice(1)));

  function selectDiningTab(panel) {
    tabs.forEach((tab, index) => {
      const selected = panels[index] === panel;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
      panels[index].hidden = !selected;
    });
  }

  function restoreDiningTab(scrollToPanel) {
    const destination = document.getElementById(window.location.hash.slice(1));
    const matchingPanel = panels.find(panel => panel.contains(destination));
    const panel = matchingPanel || document.getElementById(roofDining.dataset.defaultPanel);
    const wasHidden = panel.hidden;
    selectDiningTab(panel);
    if (scrollToPanel && matchingPanel && wasHidden) panel.scrollIntoView();
  }

  tabList.setAttribute('role', 'tablist');
  tabs.forEach((tab, index) => {
    const panel = panels[index];
    tab.setAttribute('role', 'tab');
    tab.setAttribute('aria-controls', panel.id);
    panel.setAttribute('role', 'tabpanel');
    panel.setAttribute('aria-labelledby', tab.id);
    panel.tabIndex = 0;

    tab.addEventListener('click', event => {
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      selectDiningTab(panel);
      if (window.location.hash !== tab.hash) window.history.pushState(null, '', tab.hash);
    });

    tab.addEventListener('keydown', event => {
      if (event.key === ' ') {
        event.preventDefault();
        tab.click();
        return;
      }
      const nextIndex = {
        ArrowLeft: (index + tabs.length - 1) % tabs.length,
        ArrowRight: (index + 1) % tabs.length,
        Home: 0,
        End: tabs.length - 1
      }[event.key];
      if (nextIndex === undefined) return;
      event.preventDefault();
      tabs[nextIndex].focus();
    });
  });

  restoreDiningTab(false);
  window.addEventListener('hashchange', () => restoreDiningTab(true));
}
