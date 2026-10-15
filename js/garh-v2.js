document.addEventListener('DOMContentLoaded', () => {
  // The shared header appears before each page's main landmark.
  // Give the skip link a stable, focusable destination on every rendered route.
  const main = document.querySelector('main');
  if (main) {
    if (!main.id) main.id = 'main-content';
    if (!main.hasAttribute('tabindex')) main.setAttribute('tabindex', '-1');
  }

  // Measure booking and contact intent without treating a click or form interaction
  // as a completed booking or submitted enquiry. Destination IDs come from site-config.php.
  const analyticsConfig = window.gjAnalyticsConfig && typeof window.gjAnalyticsConfig === 'object'
    ? window.gjAnalyticsConfig
    : {};
  const analyticsDestinations = Array.isArray(analyticsConfig.destinations)
    ? Array.from(new Set(analyticsConfig.destinations.filter(id => typeof id === 'string' && /^G-[A-Z0-9]+$/i.test(id))))
    : [];
  let bookingDestination = null;
  try {
    if (typeof analyticsConfig.bookingUrl === 'string' && analyticsConfig.bookingUrl) {
      bookingDestination = new URL(analyticsConfig.bookingUrl, window.location.href);
    }
  } catch {
    bookingDestination = null;
  }

  const currentPageContext = () => {
    const route = (window.location.pathname.split('/').filter(Boolean).pop() || 'index.php').toLowerCase();
    if (route === 'index.php') return 'home';
    if (route === 'room.php') return 'rooms';
    if (route === 'roof.php' || route === 'restaurant.php') return 'dining';
    if (route === 'direction.php') return 'arrival';
    if (route === 'contact.php') return 'contact';
    if (route === 'pack.php' || route === 'car-tours.php' || route === 'desert-safari.php' ||
        route === 'camping.php' || route.includes('safari') || route.includes('tour')) return 'experiences';
    if (route.includes('guide') || route.includes('itinerary') || route === 'blog.php') return 'guides';
    return 'site_content';
  };

  const trackIntentEvent = (eventName, parameters) => {
    if (typeof window.gtag !== 'function' || analyticsDestinations.length === 0) return;
    window.gtag('event', eventName, Object.assign({}, parameters, {
      send_to: analyticsDestinations,
      transport_type: 'beacon'
    }));
  };

  const labelForTrackedLink = (anchor, kind) => {
    if (kind === 'call') return 'call';
    if (kind === 'whatsapp') return 'whatsapp';
    const label = (anchor.innerText || anchor.getAttribute('aria-label') || anchor.getAttribute('title') || '')
      .replace(/\s+/g, ' ')
      .trim();
    return (label || kind).slice(0, 80);
  };

  // Keep only non-sensitive campaign fields. Never forward arbitrary query parameters or form data.
  const attributionKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
  const safeCampaignValue = (value, fallback) => (
    typeof value === 'string' && /^[A-Za-z0-9._-]{1,80}$/.test(value.trim())
      ? value.trim()
      : fallback
  );
  const attributionForCurrentPage = () => {
    const incoming = new URLSearchParams(window.location.search);
    const route = (window.location.pathname.split('/').filter(Boolean).pop() || 'index.php')
      .replace(/\.php$/i, '')
      .toLowerCase()
      .replace(/[^a-z0-9._-]/g, '-');
    return {
      utm_source: safeCampaignValue(incoming.get('utm_source'), 'hotel_website'),
      utm_medium: safeCampaignValue(incoming.get('utm_medium'), 'website'),
      utm_campaign: safeCampaignValue(incoming.get('utm_campaign'), 'direct_booking'),
      utm_content: safeCampaignValue(incoming.get('utm_content'), route || 'home')
    };
  };
  const isWhatsAppHost = (hostname) => ['wa.me', 'whatsapp.com', 'api.whatsapp.com']
    .includes(String(hostname || '').toLowerCase());
  const whatsappTopicForCurrentPage = () => {
    const route = (window.location.pathname.split('/').filter(Boolean).pop() || 'index.php').toLowerCase();
    const labels = {
      'index.php': 'a stay at Garh Jaisal Haveli',
      'room.php': 'the heritage rooms',
      'roof.php': 'rooftop dining',
      'restaurant.php': "Garh Jaisal's Kitchen",
      'pack.php': 'packages and experiences',
      'desert-safari.php': 'desert experiences',
      'camping.php': 'a desert camp stay',
      'car-rental.php': 'car arrangements',
      'car-tours.php': 'Jaisalmer sightseeing',
      'direction.php': 'arrival directions',
      'contact.php': 'the details on the contact page',
      'blog.php': 'Jaisalmer travel planning',
      'book-rooms-in-jaisalmer.php': 'a stay inside Jaisalmer Fort',
      'best-time-to-visit-jaisalmer.php': 'planning when to visit Jaisalmer',
      'how-to-reach-jaisalmer.php': 'how to reach Jaisalmer and enter the Fort',
      'jaisalmer-desert-safari-packing-list.php': 'packing for a Jaisalmer desert safari',
      'jaisalmer-havelis-guide.php': 'a Jaisalmer havelis and architecture visit',
      'jaisalmer-food-guide.php': 'Rajasthani food and rooftop dining in Jaisalmer',
      'jaisalmer-beyond-the-fort-guide.php': 'heritage places beyond Jaisalmer Fort',
      'jaisalmer-markets-and-crafts-guide.php': 'Jaisalmer markets and local crafts',
      'jaisalmer-fort-guide.php': 'planning a visit to Jaisalmer Fort',
      'desert-safari-guide.php': 'choosing a Jaisalmer desert safari',
      'jaisalmer-2-day-itinerary.php': 'a two-day Jaisalmer itinerary',
      'desert-safari.php': 'Jaisalmer desert safari options',
      'sunset-camel-safari.php': 'the sunset camel safari',
      'cultural-safari.php': 'the cultural desert safari',
      'sleep-under-the-stars.php': 'the Sleep Under the Stars desert stay',
      'swiss-tent-safari.php': 'the Swiss Tent Safari',
      'guided-jaisalmer-tour.php': 'a guided Jaisalmer tour',
      'city-tour.php': 'the guided Fort walk',
      'half-day-jaisalmer-tour.php': 'a half-day Jaisalmer tour',
      'full-day-jaisalmer-tour.php': 'a full-day Jaisalmer tour',
      'jaisalmer-3-day-itinerary.php': 'a three-day Jaisalmer itinerary',
      'jaisalmer-honeymoon-guide.php': 'a couples trip to Jaisalmer'
    };
    return labels[route] || 'Garh Jaisal Haveli';
  };
  const prepareOutboundLinks = () => {
    const attribution = attributionForCurrentPage();
    document.querySelectorAll('a[href]').forEach((anchor) => {
      let destination;
      try {
        destination = new URL(anchor.href, window.location.href);
      } catch {
        return;
      }

      if (
        bookingDestination &&
        destination.origin === bookingDestination.origin &&
        destination.pathname === bookingDestination.pathname
      ) {
        const missingAttribution = attributionKeys
          .filter((key) => !destination.searchParams.has(key))
          .map((key) => encodeURIComponent(key) + '=' + encodeURIComponent(attribution[key]));
        if (missingAttribution.length) {
          const originalHref = anchor.getAttribute('href') || anchor.href;
          const hashIndex = originalHref.indexOf('#');
          const hash = hashIndex >= 0 ? originalHref.slice(hashIndex) : '';
          const withoutHash = hashIndex >= 0 ? originalHref.slice(0, hashIndex) : originalHref;
          const separator = !withoutHash.includes('?')
            ? '?'
            : (withoutHash.endsWith('?') || withoutHash.endsWith('&') ? '' : '&');
          anchor.setAttribute('href', withoutHash + separator + missingAttribution.join('&') + hash);
        }
        return;
      }

      if (isWhatsAppHost(destination.hostname) && !destination.searchParams.get('text')) {
        destination.searchParams.set(
          'text',
          "Hello, I'm enquiring about " + whatsappTopicForCurrentPage() +
            '. Please share the current details and availability.'
        );
        anchor.href = destination.toString();
      }
    });
  };
  prepareOutboundLinks();

  document.addEventListener('click', (event) => {
    const anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!anchor) return;

    let destination;
    try {
      destination = new URL(anchor.href, window.location.href);
    } catch {
      return;
    }

    const eventContext = Object.assign({
      page_path: window.location.pathname || '/',
      page_context: currentPageContext()
    }, attributionForCurrentPage());

    if (
      bookingDestination &&
      destination.origin === bookingDestination.origin &&
      destination.pathname === bookingDestination.pathname
    ) {
      trackIntentEvent('booking_outbound_click', Object.assign({}, eventContext, {
        cta_label: labelForTrackedLink(anchor, 'booking')
      }));
      return;
    }

    if (destination.protocol === 'tel:') {
      trackIntentEvent('click_to_call', Object.assign({}, eventContext, {
        cta_label: labelForTrackedLink(anchor, 'call')
      }));
      return;
    }

    if (['wa.me', 'whatsapp.com', 'api.whatsapp.com'].includes(destination.hostname.toLowerCase())) {
      trackIntentEvent('click_whatsapp', Object.assign({}, eventContext, {
        cta_label: labelForTrackedLink(anchor, 'whatsapp')
      }));
    }
  }, true);

  const contactForm = document.querySelector('form.contact-form');
  let enquiryStartSent = false;
  if (contactForm) {
    document.addEventListener('focusin', (event) => {
      if (enquiryStartSent || !(event.target instanceof Element)) return;
      if (!event.target.matches('input:not([type="hidden"]), textarea, select')) return;
      if (event.target.closest('form.contact-form') !== contactForm) return;

      const query = new URLSearchParams(window.location.search);
      const requestedTopic = query.get('topic') || '';
      const travelPlanningTopics = [
        'visit-planning', 'desert-packing', 'fort-guide', 'desert-safari',
        'two-day-itinerary', 'three-day-itinerary', 'honeymoon'
      ];
      let enquiryContext = 'general';
      if (contactForm.querySelector('.contact-room-context')) {
        enquiryContext = 'room';
      } else if (requestedTopic === 'arrival' || requestedTopic === 'dining') {
        enquiryContext = requestedTopic;
      } else if (travelPlanningTopics.includes(requestedTopic)) {
        enquiryContext = 'travel_planning';
      }

      enquiryStartSent = true;
      trackIntentEvent('enquiry_start', Object.assign({
        page_path: window.location.pathname || '/',
        page_context: 'contact',
        enquiry_context: enquiryContext
      }, attributionForCurrentPage()));
    });
  }

  const restaurantForm = document.querySelector('form.restaurant-form');
  let restaurantEnquiryStartSent = false;
  if (restaurantForm) {
    document.addEventListener('focusin', (event) => {
      if (restaurantEnquiryStartSent || !(event.target instanceof Element)) return;
      if (!event.target.matches('input:not([type="hidden"]), textarea, select')) return;
      if (event.target.closest('form.restaurant-form') !== restaurantForm) return;

      restaurantEnquiryStartSent = true;
      trackIntentEvent('restaurant_enquiry_start', Object.assign({
        page_path: window.location.pathname || '/',
        page_context: 'dining'
      }, attributionForCurrentPage()));
    });

    restaurantForm.addEventListener('submit', () => {
      trackIntentEvent('restaurant_request_attempt', Object.assign({
        page_path: window.location.pathname || '/',
        page_context: 'dining'
      }, attributionForCurrentPage()));
    });

    const firstInvalidRestaurantField = restaurantForm.querySelector('.restaurant-field.has-error input[aria-invalid="true"]');
    if (firstInvalidRestaurantField) {
      firstInvalidRestaurantField.focus({ preventScroll: true });
      const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      firstInvalidRestaurantField.scrollIntoView({
        block: 'center',
        behavior: reduceMotion ? 'auto' : 'smooth'
      });
    }
  }

  const gallery = document.querySelector('.gallery-page');
  if (gallery) {
    const filters = Array.from(gallery.querySelectorAll('[data-gallery-filter]'));
    const filterGroup = gallery.querySelector('.gallery-filters');
    if (filterGroup) filterGroup.hidden = false;
    const sections = Array.from(gallery.querySelectorAll('[data-gallery-category]'));
    const sectionLinks = Array.from(gallery.querySelectorAll('.gallery-jump a[href^="#"]'));
    const dialog = gallery.querySelector('.gallery-lightbox');
    const lightboxImage = gallery.querySelector('[data-gallery-lightbox-image]');
    const caption = gallery.querySelector('[data-gallery-lightbox-caption]');
    const previous = gallery.querySelector('[data-gallery-previous]');
    const next = gallery.querySelector('[data-gallery-next]');
    let activeImages = [];
    let activeIndex = 0;

    const visibleImages = () => Array.from(gallery.querySelectorAll('[data-gallery-open]'))
      .filter((link) => !link.closest('[data-gallery-category]')?.hidden);

    const applyFilter = (category) => {
      sections.forEach((section) => {
        section.hidden = category !== 'all' && section.dataset.galleryCategory !== category;
      });
      sectionLinks.forEach((link) => {
        const target = link.getAttribute('href').slice(1);
        link.hidden = category !== 'all' && target !== category;
      });
      filters.forEach((filter) => {
        filter.setAttribute('aria-pressed', String(filter.dataset.galleryFilter === category));
      });
    };

    filters.forEach((filter) => {
      filter.addEventListener('click', () => {
        applyFilter(filter.dataset.galleryFilter || 'all');
        if (dialog?.open) dialog.close();
      });
    });

    const renderLightboxImage = (index) => {
      if (!activeImages.length || !lightboxImage || !caption) return;
      activeIndex = (index + activeImages.length) % activeImages.length;
      const link = activeImages[activeIndex];
      const thumbnail = link.querySelector('img');
      if (!thumbnail) return;
      lightboxImage.src = thumbnail.currentSrc || thumbnail.src;
      lightboxImage.alt = thumbnail.alt;
      caption.textContent = thumbnail.alt;
      if (previous) previous.disabled = activeImages.length < 2;
      if (next) next.disabled = activeImages.length < 2;
    };

    gallery.querySelectorAll('[data-gallery-open]').forEach((link) => {
      link.addEventListener('click', (event) => {
        if (!dialog || typeof dialog.showModal !== 'function') return;
        event.preventDefault();
        activeImages = visibleImages();
        activeIndex = activeImages.indexOf(link);
        renderLightboxImage(activeIndex);
        dialog.showModal();
      });
    });

    previous?.addEventListener('click', () => renderLightboxImage(activeIndex - 1));
    next?.addEventListener('click', () => renderLightboxImage(activeIndex + 1));
    gallery.querySelector('[data-gallery-close]')?.addEventListener('click', () => dialog?.close());
    dialog?.addEventListener('click', (event) => {
      if (event.target === dialog) dialog.close();
    });
    applyFilter('all');
  }


  // Session-only Trip tray: store product choices, never personal data or form fields.
  const tripTray = document.querySelector('[data-trip-tray]');
  if (tripTray) {
    const storageKey = 'gj-trip-items-v1';
    const trigger = tripTray.querySelector('[data-trip-toggle]');
    const panel = tripTray.querySelector('[data-trip-panel]');
    const closeButton = tripTray.querySelector('[data-trip-close]');
    const clearButton = tripTray.querySelector('[data-trip-clear]');
    const itemsList = tripTray.querySelector('[data-trip-items]');
    const emptyMessage = tripTray.querySelector('[data-trip-empty]');
    const countBadge = tripTray.querySelector('[data-trip-count]');
    const status = tripTray.querySelector('[data-trip-status]');
    const sendLink = tripTray.querySelector('[data-trip-send]');

    const validTripItem = (item) => Boolean(
      item &&
      typeof item.id === 'string' && /^[a-z0-9][a-z0-9-]{0,79}$/.test(item.id) &&
      typeof item.title === 'string' && item.title.trim() !== '' && item.title.length <= 140 &&
      typeof item.url === 'string' && /^[a-z0-9-]+\.php(?:#[a-z0-9-]+)?$/i.test(item.url) &&
      (typeof item.group === 'string' || typeof item.group === 'undefined')
    );
    const readTripItems = () => {
      try {
        const stored = JSON.parse(window.sessionStorage.getItem(storageKey) || '[]');
        return Array.isArray(stored) ? stored.filter(validTripItem).slice(0, 12) : [];
      } catch {
        return [];
      }
    };
    let tripItems = readTripItems();

    const persistTripItems = () => {
      try {
        window.sessionStorage.setItem(storageKey, JSON.stringify(tripItems));
      } catch {
        // Keep the current in-memory list usable when session storage is unavailable.
      }
    };

    const setPanelOpen = (open, moveFocus) => {
      panel.hidden = !open;
      trigger.setAttribute('aria-expanded', String(open));
      if (open && moveFocus) closeButton.focus();
      if (!open && moveFocus) trigger.focus();
    };

    const updateOutboundWhatsApp = () => {
      if (!sendLink) return;
      const base = sendLink.dataset.whatsappBase || '';
      try {
        const destination = new URL(base, window.location.href);
        const selections = tripItems.map(item => '- ' + item.title).join('\n');
        const message = "Hello, I'd like to plan a Jaisalmer trip with Garh Jaisal Haveli.\n" +
          "Please share current rates, availability and confirmed arrangements for these options:\n" +
          selections + "\nTravel dates: [add dates].\nPlease confirm what can be arranged for my dates.";
        destination.searchParams.set('text', message);
        sendLink.href = destination.toString();
      } catch {
        sendLink.href = base || '#';
      }
    };

    const refreshAddButtons = () => {
      tripTray.querySelectorAll('[data-trip-add]').forEach((buttonElement) => {
        const id = buttonElement.dataset.tripId || '';
        const selected = tripItems.some(item => item.id === id);
        const title = buttonElement.dataset.tripTitle || 'this option';
        if (!buttonElement.dataset.defaultLabel) {
          buttonElement.dataset.defaultLabel = (buttonElement.textContent || 'Add to Trip').trim();
        }
        buttonElement.textContent = selected ? 'Added to Trip' : buttonElement.dataset.defaultLabel;
        buttonElement.setAttribute('aria-pressed', String(selected));
        buttonElement.setAttribute('aria-label', selected
          ? 'Remove ' + title + ' from your Trip'
          : 'Add ' + title + ' to your Trip');
        buttonElement.classList.toggle('is-added', selected);
      });
      document.querySelectorAll('[data-trip-add]').forEach((buttonElement) => {
        const id = buttonElement.dataset.tripId || '';
        const selected = tripItems.some(item => item.id === id);
        const title = buttonElement.dataset.tripTitle || 'this option';
        if (!buttonElement.dataset.defaultLabel) {
          buttonElement.dataset.defaultLabel = (buttonElement.textContent || 'Add to Trip').trim();
        }
        buttonElement.textContent = selected ? 'Added to Trip' : buttonElement.dataset.defaultLabel;
        buttonElement.setAttribute('aria-pressed', String(selected));
        buttonElement.setAttribute('aria-label', selected
          ? 'Remove ' + title + ' from your Trip'
          : 'Add ' + title + ' to your Trip');
        buttonElement.classList.toggle('is-added', selected);
      });
    };

    const renderTripTray = () => {
      const hasItems = tripItems.length > 0;
      trigger.hidden = !hasItems;
      countBadge.textContent = String(tripItems.length);
      clearButton.hidden = !hasItems;
      sendLink.hidden = !hasItems;
      emptyMessage.hidden = hasItems;
      itemsList.replaceChildren();

      tripItems.forEach((item) => {
        const listItem = document.createElement('li');
        listItem.className = 'trip-tray-item';
        const details = document.createElement('a');
        details.href = item.url;
        details.textContent = item.title;
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'trip-tray-remove';
        remove.textContent = 'Remove';
        remove.setAttribute('aria-label', 'Remove ' + item.title + ' from your Trip');
        remove.addEventListener('click', () => {
          tripItems = tripItems.filter(candidate => candidate.id !== item.id);
          persistTripItems();
          status.textContent = item.title + ' removed from your Trip.';
          if (!tripItems.length) setPanelOpen(false, false);
          renderTripTray();
        });
        listItem.append(details, remove);
        itemsList.append(listItem);
      });

      if (!hasItems) setPanelOpen(false, false);
      updateOutboundWhatsApp();
      refreshAddButtons();
    };

    document.querySelectorAll('[data-trip-add]').forEach((buttonElement) => {
      buttonElement.addEventListener('click', () => {
        const item = {
          id: buttonElement.dataset.tripId || '',
          title: (buttonElement.dataset.tripTitle || '').trim(),
          url: buttonElement.dataset.tripUrl || '',
          group: buttonElement.dataset.tripGroup || ''
        };
        if (!validTripItem(item)) {
          status.textContent = 'This option could not be added. Open the item page and try again.';
          return;
        }

        const existingIndex = tripItems.findIndex(candidate => candidate.id === item.id);
        if (existingIndex >= 0) {
          tripItems.splice(existingIndex, 1);
          status.textContent = item.title + ' removed from your Trip.';
        } else {
          if (item.group) {
            tripItems = tripItems.filter(candidate => candidate.group !== item.group);
          }
          if (tripItems.length >= 12) {
            status.textContent = 'Your Trip has reached 12 options. Remove one before adding another.';
            return;
          }
          tripItems.push(item);
          status.textContent = item.title + ' added to your Trip.';
          trackIntentEvent('add_to_trip', {
            page_path: window.location.pathname || '/',
            page_context: currentPageContext(),
            item_id: item.id,
            item_name: item.title
          });
        }
        persistTripItems();
        renderTripTray();
      });
    });

    trigger.addEventListener('click', () => {
      const open = panel.hidden;
      setPanelOpen(open, open);
      if (open) {
        trackIntentEvent('trip_open', {
          page_path: window.location.pathname || '/',
          page_context: currentPageContext(),
          item_count: tripItems.length
        });
      }
    });
    closeButton.addEventListener('click', () => setPanelOpen(false, true));
    clearButton.addEventListener('click', () => {
      tripItems = [];
      persistTripItems();
      status.textContent = 'Your Trip shortlist has been cleared.';
      setPanelOpen(false, false);
      renderTripTray();
    });
    sendLink.addEventListener('click', () => {
      if (!tripItems.length) return;
      trackIntentEvent('trip_send_intent', {
        page_path: window.location.pathname || '/',
        page_context: currentPageContext(),
        item_count: tripItems.length
      });
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !panel.hidden) {
        setPanelOpen(false, true);
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    });

    renderTripTray();
  }

  // Restrained editorial reveals are progressive enhancement: the page stays visible.
  const storyTargets = Array.from(
    document.querySelectorAll('main.thar-sunset-story [data-story-reveal]')
  );
  const prefersReducedMotion = typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (storyTargets.length && !prefersReducedMotion && 'IntersectionObserver' in window) {
    const storyObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('story-is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -28px 0px' });
    storyTargets.forEach((target) => storyObserver.observe(target));
  }

  const button = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.main-nav');
  if (!button || !nav) return;

  const items = Array.from(nav.querySelectorAll('[data-nav-item]'));

  const syncItem = (item) => {
    const toggle = item.querySelector('.nav-sub-toggle');
    if (!toggle) return;
    const forced = item.classList.contains('is-open');
    const dismissed = item.classList.contains('is-dismissed');
    const live = !forced && !dismissed && (item.matches(':hover') || item.matches(':focus-within'));
    const compactNavigation = window.matchMedia('(max-width:1366px)').matches;
    const open = forced || (!compactNavigation && live);
    toggle.setAttribute('aria-expanded', String(open));
    const label = toggle.dataset.label || '';
    toggle.setAttribute('aria-label', (open ? 'Hide ' : 'Show ') + label + ' submenu');
  };

  const closeItems = (except) => {
    items.forEach((item) => {
      if (item === except) return;
      item.classList.remove('is-open');
      syncItem(item);
    });
  };

  const setMenu = (open) => {
    nav.classList.toggle('is-open', open);
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    if (!open) closeItems(null);
  };

  button.addEventListener('click', () => {
    setMenu(!nav.classList.contains('is-open'));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenu(false));
  });

  items.forEach((item) => {
    const toggle = item.querySelector('.nav-sub-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      const willOpen = !item.classList.contains('is-open');
      closeItems(item);
      item.classList.toggle('is-open', willOpen);
      syncItem(item);
    });

    ['mouseenter', 'mouseleave', 'focusin', 'focusout'].forEach((type) => {
      item.addEventListener(type, () => {
        // Defer so :hover and :focus-within reflect the new state.
        window.setTimeout(() => {
          // Escape hides the panel until the pointer and focus have both left.
          if (!item.matches(':hover') && !item.matches(':focus-within')) {
            item.classList.remove('is-dismissed');
          }
          syncItem(item);
        }, 0);
      });
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    const compactNavigation = window.matchMedia('(max-width:1366px)').matches;
    const hasOpenSubmenu = items.some((item) => item.classList.contains('is-open'));
    if (compactNavigation && nav.classList.contains('is-open') && !hasOpenSubmenu) {
      setMenu(false);
      button.focus();
      event.preventDefault();
      return;
    }

    const openItem = items.find((item) =>
      (item.classList.contains('is-open') || item.contains(document.activeElement)) &&
      item.querySelector('.nav-sub-toggle')
    );
    if (openItem) {
      openItem.classList.remove('is-open');
      openItem.classList.add('is-dismissed');
      const toggle = openItem.querySelector('.nav-sub-toggle');
      syncItem(openItem);
      if (toggle) toggle.focus();
      event.preventDefault();
      return;
    }

    if (nav.classList.contains('is-open')) {
      setMenu(false);
      button.focus();
      event.preventDefault();
    }
  });

  document.addEventListener('click', (event) => {
    if (!nav.contains(event.target)) closeItems(null);
    if (!nav.classList.contains('is-open')) return;
    if (nav.contains(event.target) || button.contains(event.target)) return;
    setMenu(false);
  });
});
