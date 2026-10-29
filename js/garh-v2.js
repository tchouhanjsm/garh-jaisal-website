document.addEventListener('DOMContentLoaded', () => {
  // The shared header appears before each page's main landmark.
  // Give the skip link a stable, focusable destination on every rendered route.
  const main = document.querySelector('main');
  if (main) {
    if (!main.id) main.id = 'main-content';
    if (!main.hasAttribute('tabindex')) main.setAttribute('tabindex', '-1');
  }

  // Progressive enhancement for photo storytelling. No autoplay: guests control the pace.
  document.querySelectorAll('[data-photo-carousel]').forEach((carousel) => {
    if (carousel.dataset.photoCarouselInitialized === 'true') return;

    const slides = Array.from(carousel.querySelectorAll('[data-photo-carousel-slide]'));
    const controls = carousel.querySelector('[data-photo-carousel-controls]');
    const status = carousel.querySelector('[data-photo-carousel-status]');
    const previous = carousel.querySelector('[data-photo-carousel-prev]');
    const next = carousel.querySelector('[data-photo-carousel-next]');
    if (slides.length < 2 || !controls || !status || !previous || !next) return;

    let activeIndex = 0;
    const showSlide = (nextIndex) => {
      activeIndex = (nextIndex + slides.length) % slides.length;
      slides.forEach((slide, index) => {
        const isActive = index === activeIndex;
        slide.hidden = !isActive;
        slide.setAttribute('aria-hidden', String(!isActive));
      });
      status.textContent = 'Photo ' + (activeIndex + 1) + ' of ' + slides.length;
    };

    previous.addEventListener('click', () => showSlide(activeIndex - 1));
    next.addEventListener('click', () => showSlide(activeIndex + 1));
    carousel.addEventListener('keydown', (event) => {
      if (event.altKey || event.ctrlKey || event.metaKey) return;
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        showSlide(activeIndex - 1);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        showSlide(activeIndex + 1);
      }
    });

    carousel.dataset.photoCarouselInitialized = 'true';
    carousel.classList.add('is-ready');
    showSlide(0);
    controls.hidden = false;
  });

  // The fixed mobile action dock can cover the Directions arrival essentials card.
  // Suppress it only while its viewport strip would obscure that card; the hero's
  // Call, WhatsApp and Maps actions remain available, and the dock returns afterwards.
  const arrivalQuickCard = document.querySelector('.arrival-quick-card');
  const mobileCtaDock = document.querySelector('.mobile-cta');
  if (arrivalQuickCard && mobileCtaDock) {
    const compactViewport = window.matchMedia('(max-width: 1080px)');
    let dockHeight = Math.max(58, mobileCtaDock.getBoundingClientRect().height);
    let dockUpdateFrame = 0;

    const clearArrivalDockSuppression = () => {
      mobileCtaDock.classList.remove('is-suppressed-by-arrival-card');
      mobileCtaDock.removeAttribute('aria-hidden');
      mobileCtaDock.removeAttribute('inert');
    };

    const updateArrivalDockSuppression = () => {
      dockUpdateFrame = 0;
      if (!compactViewport.matches || mobileCtaDock.contains(document.activeElement)) {
        clearArrivalDockSuppression();
        return;
      }

      const dockBounds = mobileCtaDock.getBoundingClientRect();
      if (dockBounds.height > 0) dockHeight = dockBounds.height;

      const cardBounds = arrivalQuickCard.getBoundingClientRect();
      const dockTop = Math.max(0, window.innerHeight - dockHeight);
      const overlapsDock = cardBounds.left < window.innerWidth &&
        cardBounds.right > 0 &&
        cardBounds.top < window.innerHeight &&
        cardBounds.bottom > dockTop;

      if (overlapsDock) {
        mobileCtaDock.classList.add('is-suppressed-by-arrival-card');
        mobileCtaDock.setAttribute('aria-hidden', 'true');
        mobileCtaDock.setAttribute('inert', '');
      } else {
        clearArrivalDockSuppression();
      }
    };

    const scheduleArrivalDockUpdate = () => {
      if (dockUpdateFrame !== 0) return;
      dockUpdateFrame = window.requestAnimationFrame(updateArrivalDockSuppression);
    };

    window.addEventListener('scroll', scheduleArrivalDockUpdate, { passive: true });
    window.addEventListener('resize', scheduleArrivalDockUpdate, { passive: true });
    document.addEventListener('focusin', scheduleArrivalDockUpdate);
    document.addEventListener('focusout', scheduleArrivalDockUpdate);
    if (typeof compactViewport.addEventListener === 'function') {
      compactViewport.addEventListener('change', scheduleArrivalDockUpdate);
    } else if (typeof compactViewport.addListener === 'function') {
      compactViewport.addListener(scheduleArrivalDockUpdate);
    }
    updateArrivalDockSuppression();
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

  const ctaMetadataForLink = (anchor) => {
    const metadata = {};
    const id = anchor.getAttribute('data-cta-id') || '';
    const location = anchor.getAttribute('data-cta-location') || '';
    if (/^[a-z0-9]+(?:-[a-z0-9]+){0,7}$/.test(id)) metadata.cta_id = id;
    if (/^[a-z0-9]+(?:-[a-z0-9]+){0,7}$/.test(location)) metadata.cta_location = location;
    return metadata;
  };

  // Keep only non-sensitive campaign fields. Never forward arbitrary query parameters or form data.
  const attributionKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
  const safeCampaignValue = (value, fallback) => (
    typeof value === 'string' && /^[A-Za-z0-9._-]{1,80}$/.test(value.trim())
      ? value.trim()
      : fallback
  );
  const attributionStorageKey = 'gj_conversion_attribution_v1';
  const attributionForCurrentPage = () => {
    const incoming = new URLSearchParams(window.location.search);
    const route = (window.location.pathname.split('/').filter(Boolean).pop() || 'index.php')
      .replace(/\.php$/i, '')
      .toLowerCase()
      .replace(/[^a-z0-9._-]/g, '-');

    // Keep campaign attribution for this tab's journey, not only the landing URL.
    // Read only the four allowlisted campaign values; never copy arbitrary query data.
    try {
      const stored = JSON.parse(window.sessionStorage.getItem(attributionStorageKey) || 'null');
      if (stored && typeof stored === 'object' && attributionKeys.every(key =>
        typeof stored[key] === 'string' && /^[A-Za-z0-9._-]{1,80}$/.test(stored[key])
      )) {
        return {
          utm_source: stored.utm_source,
          utm_medium: stored.utm_medium,
          utm_campaign: stored.utm_campaign,
          utm_content: stored.utm_content
        };
      }
    } catch {
      // Storage can be unavailable in restricted browser contexts; use page-local values.
    }

    const attribution = {
      utm_source: safeCampaignValue(incoming.get('utm_source'), 'hotel_website'),
      utm_medium: safeCampaignValue(incoming.get('utm_medium'), 'website'),
      utm_campaign: safeCampaignValue(incoming.get('utm_campaign'), 'direct_booking'),
      utm_content: safeCampaignValue(incoming.get('utm_content'), route || 'home')
    };
    const hasTaggedCampaign = attributionKeys.some(key => {
      const value = incoming.get(key);
      return typeof value === 'string' && /^[A-Za-z0-9._-]{1,80}$/.test(value.trim());
    });
    if (hasTaggedCampaign) {
      try {
        window.sessionStorage.setItem(attributionStorageKey, JSON.stringify(attribution));
      } catch {
        // Analytics must not depend on browser storage permission.
      }
    }
    return attribution;
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
      trackIntentEvent('booking_outbound_click', Object.assign({}, eventContext, ctaMetadataForLink(anchor), {
        cta_label: labelForTrackedLink(anchor, 'booking')
      }));
      return;
    }

    if (destination.protocol === 'tel:') {
      trackIntentEvent('click_to_call', Object.assign({}, eventContext, ctaMetadataForLink(anchor), {
        cta_label: labelForTrackedLink(anchor, 'call')
      }));
      return;
    }

    if (['wa.me', 'whatsapp.com', 'api.whatsapp.com'].includes(destination.hostname.toLowerCase())) {
      trackIntentEvent('click_whatsapp', Object.assign({}, eventContext, ctaMetadataForLink(anchor), {
        cta_label: labelForTrackedLink(anchor, 'whatsapp')
      }));
      return;
    }

    if (destination.origin === window.location.origin && /\/contact\.php$/i.test(destination.pathname)) {
      trackIntentEvent('contact_enquiry_click', Object.assign({}, eventContext, ctaMetadataForLink(anchor), {
        cta_label: labelForTrackedLink(anchor, 'enquiry')
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
      const contextParameters = {};
      const experienceContext = contactForm.querySelector('.contact-experience-context');
      const roomContext = contactForm.querySelector('.contact-room-context');

      if (experienceContext) {
        enquiryContext = 'experience';
        const experienceSlug = experienceContext.dataset.experienceSlug || '';
        if (/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(experienceSlug)) {
          contextParameters.experience_slug = experienceSlug;
        }
      } else if (roomContext) {
        enquiryContext = 'room';
        const roomSlug = roomContext.dataset.roomSlug || '';
        if (/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(roomSlug)) {
          contextParameters.room_slug = roomSlug;
        }
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
      }, contextParameters, attributionForCurrentPage()));
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
      const recoverRestaurantFieldFocus = () => {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        firstInvalidRestaurantField.scrollIntoView({
          block: 'center',
          behavior: reduceMotion ? 'auto' : 'smooth'
        });
        firstInvalidRestaurantField.focus({ preventScroll: true });
      };

      // The form action returns to #reserve. Wait until the browser has restored
      // the fragment and completed document loading before applying focus recovery.
      if (document.readyState === 'complete') {
        window.requestAnimationFrame(recoverRestaurantFieldFocus);
      } else {
        window.addEventListener('load', () => {
          window.requestAnimationFrame(recoverRestaurantFieldFocus);
        }, { once: true });
      }
    }
  }

  // Curated Gallery: progressively reveal photos after the first six per section.
  // With JavaScript disabled all curated photos remain visible; pagination is enhancement only.
  const gallery = document.querySelector('.gallery-page');
  if (gallery) {
    const filters = Array.from(gallery.querySelectorAll('[data-gallery-filter]'));
    const filterGroup = gallery.querySelector('.gallery-filters');
    const sections = Array.from(gallery.querySelectorAll('[data-gallery-category]'));
    const sectionLinks = Array.from(gallery.querySelectorAll('.gallery-jump a[href^="#"]'));
    const moreButtons = Array.from(gallery.querySelectorAll('[data-gallery-more]'));
    const dialog = gallery.querySelector('.gallery-lightbox');
    const lightboxImage = gallery.querySelector('[data-gallery-lightbox-image]');
    const caption = gallery.querySelector('[data-gallery-lightbox-caption]');
    const positionStatus = gallery.querySelector('[data-gallery-lightbox-position]');
    const previous = gallery.querySelector('[data-gallery-previous]');
    const next = gallery.querySelector('[data-gallery-next]');
    const pageSize = 6;
    const prewarmedImages = new Map();
    let activeImages = [];
    let activeIndex = 0;
    let activeTrigger = null;

    const sectionItems = (section) => Array.from(section.querySelectorAll('.gallery-item'));
    const setVisibleCount = (section, requestedCount) => {
      const items = sectionItems(section);
      const visibleCount = Math.min(Math.max(0, requestedCount), items.length);
      items.forEach((item, index) => {
        item.hidden = index >= visibleCount;
      });
      section.dataset.galleryVisibleCount = String(visibleCount);

      const status = section.querySelector('[data-gallery-count]');
      if (status) {
        status.textContent = 'Showing ' + visibleCount + ' of ' + items.length + ' curated photos.';
      }
      const more = section.querySelector('[data-gallery-more]');
      if (more) {
        const remaining = items.length - visibleCount;
        more.hidden = remaining <= 0;
        more.textContent = remaining > pageSize
          ? 'Show ' + pageSize + ' more photos'
          : 'Show ' + remaining + ' more ' + (remaining === 1 ? 'photo' : 'photos');
      }
    };

    const visibleImages = () => Array.from(gallery.querySelectorAll('[data-gallery-open]'))
      .filter((link) => {
        const section = link.closest('[data-gallery-category]');
        const item = link.closest('.gallery-item');
        return Boolean(section && item && !section.hidden && !item.hidden);
      });

    // Warm only adjacent full-size sources after the visitor explicitly opens the viewer.
    // This keeps the initial gallery load from downloading every original photograph.
    const prewarmImage = (link) => {
      if (!link || !link.href || prewarmedImages.has(link.href)) return;
      const image = new Image();
      image.decoding = 'async';
      prewarmedImages.set(link.href, image);
      image.src = link.href;
      if (typeof image.decode === 'function') image.decode().catch(() => undefined);
    };

    const prewarmNeighbors = () => {
      if (activeImages.length < 2) return;
      prewarmImage(activeImages[(activeIndex + 1) % activeImages.length]);
      prewarmImage(activeImages[(activeIndex - 1 + activeImages.length) % activeImages.length]);
    };

    const applyFilter = (category) => {
      sections.forEach((section) => {
        section.hidden = category !== 'all' && section.dataset.galleryCategory !== category;
        setVisibleCount(section, pageSize);
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

    moreButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const section = button.closest('[data-gallery-category]');
        if (!section) return;
        const visibleCount = Number(section.dataset.galleryVisibleCount || pageSize);
        setVisibleCount(section, visibleCount + pageSize);
      });
    });

    const renderLightboxImage = (index) => {
      if (!activeImages.length || !lightboxImage || !caption) return;
      activeIndex = (index + activeImages.length) % activeImages.length;
      const link = activeImages[activeIndex];
      const thumbnail = link.querySelector('img');
      if (!thumbnail) return;
      // The lightbox is a separate, full-size view. Clear the initial image's
      // responsive candidates so they cannot override the newly selected photo.
      lightboxImage.removeAttribute('srcset');
      lightboxImage.removeAttribute('sizes');
      const intrinsicWidth = Number(thumbnail.getAttribute('width'));
      const intrinsicHeight = Number(thumbnail.getAttribute('height'));
      if (intrinsicWidth > 0 && intrinsicHeight > 0) {
        lightboxImage.setAttribute('width', String(intrinsicWidth));
        lightboxImage.setAttribute('height', String(intrinsicHeight));
      } else {
        lightboxImage.removeAttribute('width');
        lightboxImage.removeAttribute('height');
      }
      // A lightbox is an explicit user action; lazy loading can otherwise leave
      // the next/previous photo blank while its caption has already advanced.
      lightboxImage.loading = 'eager';
      lightboxImage.src = link.href;
      lightboxImage.alt = thumbnail.alt;
      caption.textContent = thumbnail.alt;
      if (positionStatus) {
        positionStatus.textContent = 'Photo ' + (activeIndex + 1) + ' of ' + activeImages.length;
      }
      if (previous) previous.disabled = activeImages.length < 2;
      if (next) next.disabled = activeImages.length < 2;
      prewarmNeighbors();
    };

    gallery.querySelectorAll('[data-gallery-open]').forEach((link) => {
      link.addEventListener('click', (event) => {
        if (!dialog || typeof dialog.showModal !== 'function') return;
        event.preventDefault();
        activeImages = visibleImages();
        activeIndex = activeImages.indexOf(link);
        activeTrigger = link;
        renderLightboxImage(activeIndex);
        dialog.showModal();
      });
    });

    previous?.addEventListener('click', () => renderLightboxImage(activeIndex - 1));
    next?.addEventListener('click', () => renderLightboxImage(activeIndex + 1));
    dialog?.addEventListener('keydown', (event) => {
      if (event.altKey || event.ctrlKey || event.metaKey || activeImages.length < 2) return;
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        renderLightboxImage(activeIndex - 1);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        renderLightboxImage(activeIndex + 1);
      }
    });
    gallery.querySelector('[data-gallery-close]')?.addEventListener('click', () => dialog?.close());
    dialog?.addEventListener('click', (event) => {
      if (event.target === dialog) dialog.close();
    });
    dialog?.addEventListener('close', () => {
      const triggerSection = activeTrigger?.closest('[data-gallery-category]');
      const triggerItem = activeTrigger?.closest('.gallery-item');
      if (activeTrigger?.isConnected && !triggerSection?.hidden && !triggerItem?.hidden) {
        activeTrigger.focus({ preventScroll: true });
        return;
      }
      const selectedFilter = filters.find((filter) => filter.getAttribute('aria-pressed') === 'true');
      selectedFilter?.focus({ preventScroll: true });
    });

    applyFilter('all');
    if (filterGroup) filterGroup.hidden = false;
  }



  // Rooms: progressively enhance search, filters and side-by-side shortlisting.
  // No room selection is persisted; every booking link still points to the configured booking engine.
  const roomFinder = document.querySelector('[data-room-finder]');
  if (roomFinder) {
    const roomCards = Array.from(document.querySelectorAll('.rooms-page .room-card'));
    const floorGroups = Array.from(document.querySelectorAll('.rooms-page [data-room-floor-group]'));
    const floorLinks = Array.from(document.querySelectorAll('.rooms-page [data-room-floor-link]'));
    const searchInput = roomFinder.querySelector('[data-room-search]');
    const floorSelect = roomFinder.querySelector('[data-room-floor-filter]');
    const featureButtons = Array.from(roomFinder.querySelectorAll('[data-room-filter-feature]'));
    const resetButtons = Array.from(roomFinder.querySelectorAll('[data-room-reset]'));
    const resultStatus = roomFinder.querySelector('[data-room-result-status]');
    const emptyState = roomFinder.querySelector('[data-room-empty]');
    const comparisonShortlist = roomFinder.querySelector('[data-room-comparison-shortlist]');
    const comparisonShortlistStatus = roomFinder.querySelector('[data-room-comparison-shortlist-status]');
    const comparisonShortlistItems = roomFinder.querySelector('[data-room-comparison-shortlist-items]');
    const comparisonShortlistReview = roomFinder.querySelector('[data-room-shortlist-review]');
    const comparisonShortlistReveal = roomFinder.querySelector('[data-room-shortlist-reveal]');
    const comparisonShortlistClear = roomFinder.querySelector('[data-room-shortlist-clear]');
    const comparisonDialog = document.querySelector('[data-room-comparison-dialog]');
    const comparisonStatus = comparisonDialog?.querySelector('[data-room-comparison-status]');
    const comparisonContent = comparisonDialog?.querySelector('[data-room-comparison-content]');
    const comparisonClear = comparisonDialog?.querySelector('[data-room-comparison-clear]');
    const comparisonEnquiryLink = comparisonDialog?.querySelector('[data-room-comparison-enquiry]');
    const comparisonTripButton = comparisonDialog?.querySelector('[data-room-comparison-trip]');
    const comparisonDifferenceControl = comparisonDialog?.querySelector('[data-room-comparison-difference-control]');
    const comparisonDifferences = comparisonDialog?.querySelector('[data-room-comparison-differences]');
    const comparisonFilterStatus = comparisonDialog?.querySelector('[data-room-comparison-filter-status]');
    const comparisonCloseButtons = Array.from(comparisonDialog?.querySelectorAll('[data-room-comparison-close]') || []);
    const selectedRoomIds = new Set();
    let lastSelectedRoomId = null;
    let selectedFeature = 'all';

    const normalizeRoomText = (value) => String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
    const roomName = (card) => card.querySelector('h3')?.textContent?.trim() || 'Selected room';
    const selectedRoomCards = () => Array.from(selectedRoomIds)
      .map((id) => roomCards.find((card) => card.id === id))
      .filter(Boolean);

    const renderComparisonShortlist = (selectedCards) => {
      if (!comparisonShortlist || !comparisonShortlistItems) return;

      comparisonShortlist.hidden = selectedCards.length === 0;
      comparisonShortlistItems.replaceChildren();
      selectedCards.forEach((card) => {
        const item = document.createElement('li');
        const name = document.createElement('span');
        name.textContent = roomName(card);
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'room-comparison-shortlist-remove';
        remove.dataset.roomShortlistRemove = card.id;
        remove.textContent = 'Remove';
        remove.setAttribute('aria-label', 'Remove ' + roomName(card) + ' from your room shortlist');
        item.append(name, remove);
        comparisonShortlistItems.appendChild(item);
      });

      const hiddenSelection = selectedCards.some((card) => card.hidden);
      if (comparisonShortlistReveal) comparisonShortlistReveal.hidden = !hiddenSelection;

      if (comparisonShortlistStatus) {
        comparisonShortlistStatus.textContent = selectedCards.length === 1
          ? '1 room selected. Add one more room to compare.'
          : selectedCards.length + ' rooms selected. You can review this shortlist even when filters hide the room cards.';
      }
      if (comparisonShortlistReview) {
        comparisonShortlistReview.disabled = selectedCards.length < 2;
        comparisonShortlistReview.textContent = selectedCards.length >= 2
          ? 'Compare selected rooms (' + selectedCards.length + ')'
          : 'Compare selected rooms (' + selectedCards.length + '/2)';
        comparisonShortlistReview.setAttribute('aria-label', selectedCards.length >= 2
          ? 'Compare ' + selectedCards.length + ' selected rooms'
          : 'Select at least two rooms to compare');
      }
    };

    const applyComparisonFeatureFilter = () => {
      const rows = Array.from(comparisonContent?.querySelectorAll('[data-room-feature-row]') || []);
      const showDifferences = Boolean(comparisonDifferences?.checked);
      const differingCount = rows.filter((row) => row.dataset.roomFeatureDiff === 'true').length;

      rows.forEach((row) => {
        row.hidden = showDifferences && row.dataset.roomFeatureDiff !== 'true';
      });

      if (comparisonFilterStatus) {
        comparisonFilterStatus.hidden = !showDifferences || rows.length === 0;
        if (showDifferences) {
          comparisonFilterStatus.textContent = differingCount
            ? 'Showing ' + differingCount + ' of ' + rows.length + ' listed features that differ across the selected rooms.'
            : 'No listed feature differences found. Turn off “Show differences only” to review every listed feature.';
        }
      }
    };

    const updateRoomResultStatus = () => {
      if (!resultStatus) return;
      const visibleCount = roomCards.filter((card) => !card.hidden).length;
      const selectionNote = selectedRoomIds.size
        ? ' ' + selectedRoomIds.size + ' room' + (selectedRoomIds.size === 1 ? '' : 's') + ' selected for comparison.'
        : '';
      resultStatus.textContent = 'Showing ' + visibleCount + ' of ' + roomCards.length + ' rooms.' + selectionNote;
    };

    const renderComparison = () => {
      const selectedCards = selectedRoomCards();

      if (comparisonEnquiryLink) {
        if (selectedCards.length >= 2) {
          const query = new URLSearchParams();
          selectedCards.forEach((card) => query.append('rooms[]', card.id.replace(/^room-/, '')));
          comparisonEnquiryLink.href = 'contact.php?' + query.toString();
          comparisonEnquiryLink.hidden = false;
        } else {
          comparisonEnquiryLink.href = 'contact.php';
          comparisonEnquiryLink.hidden = true;
        }
      }

      if (comparisonTripButton) {
        if (selectedCards.length >= 2) {
          const roomSlugs = selectedCards.map((card) => card.id.replace(/^room-/, ''));
          const roomNames = selectedCards.map(roomName);
          comparisonTripButton.dataset.tripId = 'room-options-' + roomSlugs.join('-');
          comparisonTripButton.dataset.tripTitle = 'Room options: ' + roomNames.join(', ');
          comparisonTripButton.dataset.tripUrl = 'room.php#room-directory';
          comparisonTripButton.dataset.tripGroup = 'stay';
          comparisonTripButton.setAttribute('aria-label', 'Add these room options to your Trip: ' + roomNames.join(', '));
          comparisonTripButton.hidden = false;
        } else {
          comparisonTripButton.hidden = true;
          comparisonTripButton.removeAttribute('data-trip-id');
          comparisonTripButton.removeAttribute('data-trip-title');
          comparisonTripButton.removeAttribute('data-trip-url');
          comparisonTripButton.removeAttribute('data-trip-group');
          comparisonTripButton.removeAttribute('aria-label');
        }
      }

      roomCards.forEach((card) => {
        const toggle = card.querySelector('[data-room-compare-toggle]');
        const openButton = card.querySelector('[data-room-comparison-open]');
        const selected = selectedRoomIds.has(card.id);
        if (toggle) {
          toggle.hidden = false;
          toggle.setAttribute('aria-pressed', String(selected));
          toggle.textContent = selected ? 'Remove from compare' : 'Add to compare';
          toggle.disabled = !selected && selectedRoomIds.size >= 3;
          toggle.setAttribute('aria-label', (selected ? 'Remove ' : 'Add ') + roomName(card) + (selected ? ' from comparison' : ' to comparison'));
        }
        if (openButton) {
          const canReview = selectedCards.length >= 2 && card.id === lastSelectedRoomId;
          openButton.hidden = !canReview;
          openButton.textContent = 'Review comparison (' + selectedCards.length + ')';
          openButton.setAttribute('aria-label', 'Review comparison of ' + selectedCards.length + ' selected rooms');
        }
      });

      updateRoomResultStatus();
      renderComparisonShortlist(selectedCards);
      if (!comparisonContent || !comparisonStatus) return;

      comparisonContent.replaceChildren();
      if (selectedCards.length < 2) {
        if (comparisonDifferenceControl) comparisonDifferenceControl.hidden = true;
        if (comparisonDifferences) comparisonDifferences.checked = false;
        if (comparisonFilterStatus) comparisonFilterStatus.hidden = true;
        comparisonStatus.textContent = selectedCards.length === 1
          ? '1 room selected. Close this panel and add at least one more room to compare.'
          : 'Choose two or three rooms from the directory to compare.';
        return;
      }

      if (comparisonDifferenceControl) comparisonDifferenceControl.hidden = false;
      comparisonStatus.textContent = 'Comparing ' + selectedCards.length + ' rooms. Listed details are shown below; check current rates and availability through the booking link.';
      const table = document.createElement('table');
      table.className = 'room-comparison-table';
      table.setAttribute('aria-label', 'Selected room comparison');

      const caption = document.createElement('caption');
      caption.textContent = 'Room details at a glance — Listed features by room';
      table.appendChild(caption);

      const thead = document.createElement('thead');
      const headerRow = document.createElement('tr');
      const firstHeader = document.createElement('th');
      firstHeader.scope = 'col';
      firstHeader.textContent = 'Compare';
      headerRow.appendChild(firstHeader);
      selectedCards.forEach((card) => {
        const header = document.createElement('th');
        header.scope = 'col';
        header.textContent = roomName(card);
        headerRow.appendChild(header);
      });
      thead.appendChild(headerRow);
      table.appendChild(thead);

      const tbody = document.createElement('tbody');
      const photoRow = document.createElement('tr');
      const photoHeading = document.createElement('th');
      photoHeading.scope = 'row';
      photoHeading.textContent = 'Room photo';
      photoRow.appendChild(photoHeading);
      selectedCards.forEach((card) => {
        const cell = document.createElement('td');
        const sourceImage = card.querySelector('.photo-carousel-slide img');
        if (sourceImage) {
          // Reuse the first curated room photo and its validated responsive candidates.
          // The comparison cell is narrower than the directory card, so size for its real slot.
          const image = sourceImage.cloneNode(false);
          image.classList.add('room-comparison-photo');
          image.alt = sourceImage.getAttribute('alt') || (roomName(card) + ' interior');
          image.setAttribute('sizes', '150px');
          image.loading = 'lazy';
          image.decoding = 'async';
          cell.appendChild(image);
        } else {
          cell.textContent = 'Photo unavailable';
        }
        photoRow.appendChild(cell);
      });
      tbody.appendChild(photoRow);

      const appendTextRow = (label, selector) => {
        const row = document.createElement('tr');
        const heading = document.createElement('th');
        heading.scope = 'row';
        heading.textContent = label;
        row.appendChild(heading);
        selectedCards.forEach((card) => {
          const cell = document.createElement('td');
          cell.textContent = card.querySelector(selector)?.textContent?.trim() || 'Not listed';
          row.appendChild(cell);
        });
        tbody.appendChild(row);
      };

      appendTextRow('Floor', '.room-kicker');
      appendTextRow('Description', '.room-description');

      // Compare every registered/listed feature on the same row so differences are scannable.
      const featuresByRoom = selectedCards.map((card) => new Map(
        Array.from(card.querySelectorAll('.room-features li'))
          .map((item) => item.textContent.trim())
          .filter(Boolean)
          .map((feature) => [normalizeRoomText(feature), feature])
      ));
      const featureLabels = new Map();
      featuresByRoom.forEach((features) => {
        features.forEach((label, key) => {
          if (!featureLabels.has(key)) featureLabels.set(key, label);
        });
      });

      Array.from(featureLabels.entries())
        .sort((left, right) => left[1].localeCompare(right[1]))
        .forEach(([featureKey, featureLabel]) => {
          const row = document.createElement('tr');
          row.dataset.roomFeatureRow = '';
          const heading = document.createElement('th');
          heading.scope = 'row';
          heading.textContent = featureLabel;
          row.appendChild(heading);

          let listedCount = 0;
          featuresByRoom.forEach((features, roomIndex) => {
            const listed = features.has(featureKey);
            if (listed) listedCount += 1;
            const cell = document.createElement('td');
            const state = document.createElement('span');
            state.className = listed
              ? 'room-comparison-feature-state room-comparison-feature-state--listed'
              : 'room-comparison-feature-state room-comparison-feature-state--unlisted';
            state.textContent = listed ? 'Listed' : 'Not listed';
            state.setAttribute('aria-label', featureLabel + (listed ? ' is listed for ' : ' is not listed for ') + roomName(selectedCards[roomIndex]));
            cell.appendChild(state);
            row.appendChild(cell);
          });

          row.dataset.roomFeatureDiff = String(listedCount > 0 && listedCount < selectedCards.length);
          tbody.appendChild(row);
        });

      const actionRow = document.createElement('tr');
      const actionHeading = document.createElement('th');
      actionHeading.scope = 'row';
      actionHeading.textContent = 'Next step';
      actionRow.appendChild(actionHeading);
      selectedCards.forEach((card) => {
        const cell = document.createElement('td');
        const sourceLink = card.querySelector('.room-book');
        if (sourceLink) {
          const link = document.createElement('a');
          link.className = 'button button-primary room-comparison-availability';
          link.href = sourceLink.href;
          link.target = sourceLink.target || '_blank';
          link.rel = sourceLink.rel || 'noopener';
          link.textContent = 'Check availability';
          link.setAttribute('aria-label', 'Check availability for ' + roomName(card));
          cell.appendChild(link);
        }
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'room-comparison-remove';
        removeButton.dataset.roomCompareRemove = card.id;
        removeButton.textContent = 'Remove from compare';
        removeButton.setAttribute('aria-label', 'Remove ' + roomName(card) + ' from comparison');
        cell.appendChild(removeButton);
        actionRow.appendChild(cell);
      });
      tbody.appendChild(actionRow);
      table.appendChild(tbody);
      comparisonContent.appendChild(table);
      applyComparisonFeatureFilter();
    };

    const applyRoomFilters = () => {
      const query = normalizeRoomText(searchInput ? searchInput.value : '');
      const selectedFloor = floorSelect ? floorSelect.value : 'all';
      let visibleCount = 0;

      roomCards.forEach((card) => {
        const roomSearchableText = [
          card.querySelector('h3')?.textContent,
          card.querySelector('.room-description')?.textContent,
          ...Array.from(card.querySelectorAll('.room-features li')).map((feature) => feature.textContent),
        ].filter(Boolean).join(' ');
        const matchesSearch = !query || normalizeRoomText(roomSearchableText).includes(query);
        const matchesFloor = selectedFloor === 'all' || card.dataset.roomFloor === selectedFloor;
        const cardFeatures = (card.dataset.roomFeatures || '').split('|').filter(Boolean);
        const matchesFeature = selectedFeature === 'all' || cardFeatures.includes(selectedFeature);
        const visible = matchesSearch && matchesFloor && matchesFeature;
        card.hidden = !visible;
        if (visible) visibleCount++;
      });

      floorGroups.forEach((section) => {
        section.hidden = section.querySelectorAll('.room-card:not([hidden])').length === 0;
      });
      floorLinks.forEach((link) => {
        const targetId = (link.getAttribute('href') || '').slice(1);
        const target = targetId ? document.getElementById(targetId) : null;
        link.hidden = !target || target.hidden;
      });

      if (emptyState) emptyState.hidden = visibleCount > 0;
      renderComparisonShortlist(selectedRoomCards());
      updateRoomResultStatus();
    };

    const resetRoomFilters = () => {
      if (searchInput) searchInput.value = '';
      if (floorSelect) floorSelect.value = 'all';
      selectedFeature = 'all';
      featureButtons.forEach((button) => {
        button.setAttribute('aria-pressed', String((button.dataset.roomFilterFeature || 'all') === 'all'));
      });
      applyRoomFilters();
    };

    if (roomCards.length && searchInput && floorSelect && featureButtons.length) {
      searchInput.addEventListener('input', applyRoomFilters);
      floorSelect.addEventListener('change', applyRoomFilters);
      featureButtons.forEach((button) => {
        button.addEventListener('click', () => {
          selectedFeature = button.dataset.roomFilterFeature || 'all';
          featureButtons.forEach((candidate) => {
            candidate.setAttribute('aria-pressed', String(candidate === button));
          });
          applyRoomFilters();
        });
      });
      resetButtons.forEach((button) => button.addEventListener('click', resetRoomFilters));

      roomCards.forEach((card) => {
        const toggle = card.querySelector('[data-room-compare-toggle]');
        const openButton = card.querySelector('[data-room-comparison-open]');
        toggle?.addEventListener('click', () => {
          if (selectedRoomIds.has(card.id)) {
            selectedRoomIds.delete(card.id);
            if (lastSelectedRoomId === card.id) {
              lastSelectedRoomId = Array.from(selectedRoomIds).at(-1) || null;
            }
          } else if (selectedRoomIds.size < 3) {
            selectedRoomIds.add(card.id);
            lastSelectedRoomId = card.id;
          }
          renderComparison();
        });
        openButton?.addEventListener('click', () => {
          if (comparisonDialog && selectedRoomIds.size >= 2 && !comparisonDialog.open) {
            renderComparison();
            comparisonDialog.showModal();
          }
        });
      });

      comparisonContent?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-room-compare-remove]');
        if (!removeButton) return;
        const removeIndex = Array.from(comparisonContent.querySelectorAll('[data-room-compare-remove]')).indexOf(removeButton);
        const roomId = removeButton.dataset.roomCompareRemove;
        selectedRoomIds.delete(roomId);
        if (lastSelectedRoomId === roomId) {
          lastSelectedRoomId = Array.from(selectedRoomIds).at(-1) || null;
        }
        renderComparison();
        const remainingRemoveButtons = Array.from(comparisonContent.querySelectorAll('[data-room-compare-remove]'));
        const focusTarget = remainingRemoveButtons[Math.min(removeIndex, remainingRemoveButtons.length - 1)] || comparisonContent;
        focusTarget.focus();
      });
      comparisonShortlistItems?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-room-shortlist-remove]');
        if (!removeButton) return;
        const removeIndex = Array.from(comparisonShortlistItems.querySelectorAll('[data-room-shortlist-remove]')).indexOf(removeButton);
        const roomId = removeButton.dataset.roomShortlistRemove;
        selectedRoomIds.delete(roomId);
        if (lastSelectedRoomId === roomId) {
          lastSelectedRoomId = Array.from(selectedRoomIds).at(-1) || null;
        }
        renderComparison();
        const remainingRemoveButtons = Array.from(comparisonShortlistItems.querySelectorAll('[data-room-shortlist-remove]'));
        const focusTarget = remainingRemoveButtons[Math.min(removeIndex, remainingRemoveButtons.length - 1)]
          || (selectedRoomIds.size >= 2 ? comparisonShortlistReview : searchInput);
        focusTarget?.focus();
      });
      comparisonShortlistReview?.addEventListener('click', () => {
        if (selectedRoomIds.size < 2 || !comparisonDialog || comparisonDialog.open) return;
        renderComparison();
        comparisonDialog.showModal();
      });
      comparisonShortlistReveal?.addEventListener('click', () => {
        if (!selectedRoomIds.size) return;
        resetRoomFilters();
        const firstSelectedCard = selectedRoomCards()[0];
        firstSelectedCard?.querySelector('[data-room-compare-toggle]')?.focus();
      });
      comparisonShortlistClear?.addEventListener('click', () => {
        selectedRoomIds.clear();
        lastSelectedRoomId = null;
        renderComparison();
        searchInput?.focus();
      });
      comparisonClear?.addEventListener('click', () => {
        selectedRoomIds.clear();
        lastSelectedRoomId = null;
        renderComparison();
      });
      comparisonDifferences?.addEventListener('change', applyComparisonFeatureFilter);
      comparisonTripButton?.addEventListener('click', () => {
        const selectedCards = selectedRoomCards();
        if (selectedCards.length < 2) return;

        const roomSlugs = selectedCards.map((card) => card.id.replace(/^room-/, ''));
        const roomNames = selectedCards.map(roomName);
        const item = {
          id: 'room-options-' + roomSlugs.join('-'),
          title: 'Room options: ' + roomNames.join(', '),
          url: 'room.php#room-directory',
          group: 'stay'
        };
        const request = new CustomEvent('gj:trip-add-item', {
          detail: { item },
          cancelable: true
        });
        document.dispatchEvent(request);
        if (request.defaultPrevented) return;

        comparisonDialog?.close();
        document.querySelector('[data-trip-close]')?.focus();
      });
      comparisonCloseButtons.forEach((button) => button.addEventListener('click', () => comparisonDialog?.close()));
      comparisonDialog?.addEventListener('click', (event) => {
        if (event.target === comparisonDialog) comparisonDialog.close();
      });

      roomFinder.hidden = false;
      applyRoomFilters();
      renderComparison();
    }
  }

  // Packages: progressively enhance the published experience cards with search and filters.
  // Draft/hidden registry entries are never rendered into the public list or filter choices.
  const experienceFinder = document.querySelector('[data-experience-finder]');
  if (experienceFinder) {
    const experienceCards = Array.from(document.querySelectorAll('[data-experience-filter-card]'));
    const categorySections = [
      document.querySelector('#desert-options'),
      document.querySelector('#city-options')
    ].filter(Boolean);
    const searchInput = experienceFinder.querySelector('[data-experience-search]');
    const categorySelect = experienceFinder.querySelector('[data-experience-category]');
    const durationSelect = experienceFinder.querySelector('[data-experience-duration]');
    const resetButtons = Array.from(experienceFinder.querySelectorAll('[data-experience-reset]'));
    const resultStatus = experienceFinder.querySelector('[data-experience-result-status]');
    const emptyState = experienceFinder.querySelector('[data-experience-empty]');

    const normalizeExperienceText = (value) => String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();

    const applyExperienceFilters = () => {
      const terms = normalizeExperienceText(searchInput ? searchInput.value : '').split(' ').filter(Boolean);
      const category = categorySelect ? categorySelect.value : 'all';
      const duration = durationSelect ? durationSelect.value : 'all';
      let visibleCount = 0;

      experienceCards.forEach((card) => {
        const cardText = normalizeExperienceText(card.textContent);
        const matchesSearch = terms.every((term) => cardText.includes(term));
        const matchesCategory = category === 'all' || card.dataset.experienceCategory === category;
        const matchesDuration = duration === 'all' || card.dataset.experienceDuration === duration;
        const visible = matchesSearch && matchesCategory && matchesDuration;
        card.hidden = !visible;
        if (visible) visibleCount++;
      });

      categorySections.forEach((section) => {
        const cards = Array.from(section.querySelectorAll('[data-experience-filter-card]'));
        section.hidden = cards.length === 0 || cards.every((card) => card.hidden);
      });

      if (resultStatus) {
        resultStatus.textContent = 'Showing ' + visibleCount + ' of ' + experienceCards.length + ' experiences.';
      }
      if (emptyState) emptyState.hidden = visibleCount > 0;
    };

    const resetExperienceFilters = () => {
      if (searchInput) searchInput.value = '';
      if (categorySelect) categorySelect.value = 'all';
      if (durationSelect) durationSelect.value = 'all';
      applyExperienceFilters();
    };

    if (experienceCards.length && searchInput && categorySelect && durationSelect) {
      searchInput.addEventListener('input', applyExperienceFilters);
      categorySelect.addEventListener('change', applyExperienceFilters);
      durationSelect.addEventListener('change', applyExperienceFilters);
      resetButtons.forEach((button) => button.addEventListener('click', resetExperienceFilters));
      experienceFinder.hidden = false;
      applyExperienceFilters();
    }
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
    const contactLink = tripTray.querySelector('[data-trip-contact]');
    const tripStartDate = tripTray.querySelector('[data-trip-start-date]');
    const tripEndDate = tripTray.querySelector('[data-trip-end-date]');
    const tripTravellers = tripTray.querySelector('[data-trip-travellers]');
    const tripDetailsError = tripTray.querySelector('[data-trip-details-error]');

    const validateTripDetails = () => {
      const startDate = tripStartDate ? tripStartDate.value.trim() : '';
      const endDate = tripEndDate ? tripEndDate.value.trim() : '';
      const travellerValue = tripTravellers ? tripTravellers.value.trim() : '';
      const dateRangeInvalid = Boolean(startDate && endDate && endDate < startDate);
      const travellerCount = Number(travellerValue);
      const travellersInvalid = travellerValue !== '' && (
        !Number.isSafeInteger(travellerCount) || travellerCount < 1
      );
      const errors = [];

      if (dateRangeInvalid) {
        errors.push('Travel end date must be on or after the start date.');
      }
      if (travellersInvalid) {
        errors.push('Enter a whole number of travellers greater than zero, or leave this field blank.');
      }

      if (tripEndDate) {
        if (dateRangeInvalid) tripEndDate.setAttribute('aria-invalid', 'true');
        else tripEndDate.removeAttribute('aria-invalid');
      }
      if (tripTravellers) {
        if (travellersInvalid) tripTravellers.setAttribute('aria-invalid', 'true');
        else tripTravellers.removeAttribute('aria-invalid');
      }
      if (tripDetailsError) {
        tripDetailsError.textContent = errors.join(' ');
        tripDetailsError.hidden = errors.length === 0;
      }
      return errors.length === 0;
    };


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

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape' || panel.hidden) return;
      event.preventDefault();
      setPanelOpen(false, true);
    });

    const updateContactHandoff = () => {
      if (!contactLink) return;
      const query = new URLSearchParams();
      tripItems.forEach((item) => query.append('trip[]', item.id));
      contactLink.hidden = tripItems.length === 0;
      contactLink.href = tripItems.length
        ? 'contact.php?' + query.toString()
        : 'contact.php';
    };

    const updateOutboundWhatsApp = () => {
      if (!sendLink) return;
      const base = sendLink.dataset.whatsappBase || '';
      try {
        const destination = new URL(base, window.location.href);
        const selections = tripItems.map(item => '- ' + item.title).join('\n');
        const startDate = tripStartDate ? tripStartDate.value.trim() : '';
        const endDate = tripEndDate ? tripEndDate.value.trim() : '';
        const travellerValue = tripTravellers ? tripTravellers.value.trim() : '';
        const dateRange = startDate && endDate
          ? startDate + ' to ' + endDate
          : startDate
            ? startDate + ' (end date to be confirmed)'
            : endDate
              ? 'start date to be confirmed, ending ' + endDate
              : 'to be confirmed';
        const travellerCount = travellerValue !== '' && Number.isSafeInteger(Number(travellerValue)) && Number(travellerValue) >= 1
          ? String(Number(travellerValue))
          : 'to be confirmed';
        const message = "Hello, I'd like to plan a Jaisalmer trip with Garh Jaisal Haveli.\n" +
          "Please share current rates, availability and confirmed arrangements for these options:\n" +
          selections + "\nTravel dates: " + dateRange + ".\nNumber of travellers: " + travellerCount +
          ".\nPlease confirm what can be arranged for my dates.";
        destination.searchParams.set('text', message);
        sendLink.href = destination.toString();
      } catch {
        sendLink.href = base || '#';
      }
      validateTripDetails();
    };

    [tripStartDate, tripEndDate, tripTravellers].forEach((field) => {
      if (field) field.addEventListener('input', updateOutboundWhatsApp);
    });

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

      if (!hasItems) {
        setPanelOpen(false, false);
        if (tripStartDate) tripStartDate.value = '';
        if (tripEndDate) tripEndDate.value = '';
        if (tripTravellers) tripTravellers.value = '';
        validateTripDetails();
      }
      updateOutboundWhatsApp();
      updateContactHandoff();
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

    document.addEventListener('gj:trip-add-item', (event) => {
      const requestedItem = event.detail && event.detail.item;
      if (!validTripItem(requestedItem)) {
        event.preventDefault();
        status.textContent = 'These room options could not be added to your Trip. Return to the comparison and try again.';
        return;
      }

      const item = {
        id: requestedItem.id,
        title: requestedItem.title.trim(),
        url: requestedItem.url,
        group: requestedItem.group || ''
      };
      const remainingItems = item.group
        ? tripItems.filter((candidate) => candidate.group !== item.group)
        : tripItems.filter((candidate) => candidate.id !== item.id);
      if (remainingItems.length >= 12) {
        event.preventDefault();
        status.textContent = 'Your Trip has reached 12 options. Remove one before adding another.';
        return;
      }

      const replacedStayChoice = item.group === 'stay' && tripItems.some((candidate) => candidate.group === 'stay');
      tripItems = remainingItems.concat(item);
      persistTripItems();
      renderTripTray();
      setPanelOpen(true, false);
      status.textContent = replacedStayChoice
        ? 'Room options added to your Trip. Your previous stay choice was replaced; all other Trip selections were kept.'
        : 'Room options added to your Trip; all other Trip selections were kept.';
      trackIntentEvent('add_to_trip', {
        page_path: window.location.pathname || '/',
        page_context: currentPageContext(),
        item_id: item.id,
        item_name: item.title
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
    sendLink.addEventListener('click', (event) => {
      if (!tripItems.length) {
        event.preventDefault();
        return;
      }
      if (!validateTripDetails()) {
        event.preventDefault();
        if (tripEndDate && tripEndDate.getAttribute('aria-invalid') === 'true') {
          tripEndDate.focus();
        } else if (tripTravellers && tripTravellers.getAttribute('aria-invalid') === 'true') {
          tripTravellers.focus();
        }
        return;
      }
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

  // Journal discovery is progressive enhancement: public links stay visible
  // when JavaScript is unavailable.
  const journalSearchForm = document.querySelector('[data-journal-search-form]');
  const journalSearchInput = document.querySelector('[data-journal-search-input]');
  const journalTopicFilter = document.querySelector('[data-journal-topic-filter]');
  const journalClearButton = document.querySelector('[data-journal-clear]');
  const journalResults = document.querySelector('[data-journal-results]');
  const journalEmptyState = document.querySelector('[data-journal-empty]');
  const journalGrid = document.querySelector('[data-journal-grid]');
  const journalCards = journalGrid ? Array.from(journalGrid.querySelectorAll('[data-journal-card]')) : [];

  if (journalSearchForm && journalSearchInput && journalTopicFilter &&
      journalClearButton && journalResults && journalEmptyState && journalGrid && journalCards.length) {
    const normalizeJournalText = (value) => String(value || '')
      .normalize('NFKC')
      .toLowerCase()
      .replace(/\s+/g, ' ')
      .trim();

    const updateJournalResults = () => {
      const query = normalizeJournalText(journalSearchInput.value);
      const topic = journalTopicFilter.value;
      const hasActiveFilter = Boolean(query) || topic !== 'all';
      let visibleCount = 0;

      journalCards.forEach((card) => {
        const matchesText = !query || normalizeJournalText(card.textContent).includes(query);
        const matchesTopic = topic === 'all' || card.dataset.journalTopic === topic;
        const visible = matchesText && matchesTopic;
        card.hidden = !visible;
        if (visible) visibleCount += 1;
      });

      journalResults.textContent = hasActiveFilter
        ? 'Showing ' + visibleCount + ' of ' + journalCards.length + ' results.'
        : 'Showing all ' + journalCards.length + ' results.';
      journalResults.hidden = false;
      journalClearButton.hidden = !hasActiveFilter;
      journalEmptyState.hidden = visibleCount !== 0;
    };

    const resetJournalFilters = () => {
      journalSearchInput.value = '';
      journalTopicFilter.value = 'all';
      updateJournalResults();
    };

    journalSearchInput.addEventListener('input', updateJournalResults);
    journalTopicFilter.addEventListener('change', updateJournalResults);
    journalSearchForm.addEventListener('submit', (event) => {
      event.preventDefault();
      updateJournalResults();
    });
    journalClearButton.addEventListener('click', () => {
      resetJournalFilters();
      journalSearchInput.focus();
    });

    // Topic links remain stable URL fragments rather than a second filter UI.
    // Clear active filters before following a fragment to keep the target visible.
    const journalTopicIndex = document.querySelector('.journal-topic-index');
    if (journalTopicIndex) {
      journalTopicIndex.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', resetJournalFilters);
      });
    }

    journalSearchForm.hidden = false;
    updateJournalResults();
  }

});
