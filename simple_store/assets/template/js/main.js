/**
* Template Name: ZenBlog
* Template URL: https://bootstrapmade.com/zenblog-bootstrap-blog-template/
* Updated: Aug 08 2024 with Bootstrap v5.3.3
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);
  
 /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

/**
   * Product Image Zoom and Thumbnail Functionality
   */

  function productDetailFeatures() {
    // Initialize Drift for image zoom
    function initDriftZoom() {
      // Check if Drift is available
      if (typeof Drift === 'undefined') {
        console.error('Drift library is not loaded');
        return;
      }

      const driftOptions = {
        paneContainer: document.querySelector('.image-zoom-container'),
        inlinePane: window.innerWidth < 768 ? true : false,
        inlineOffsetY: -85,
        containInline: true,
        hoverBoundingBox: false,
        zoomFactor: 3,
        handleTouch: false
      };

      // Initialize Drift on the main product image
      const mainImage = document.getElementById('main-product-image');
      if (mainImage) {
        new Drift(mainImage, driftOptions);
      }
    }

    // Thumbnail click functionality
    function initThumbnailClick() {
      const thumbnails = document.querySelectorAll('.thumbnail-item');
      const mainImage = document.getElementById('main-product-image');

      if (!thumbnails.length || !mainImage) return;

      thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
          // Get image path from data attribute
          const imageSrc = this.getAttribute('data-image');

          // Update main image src and zoom attribute
          mainImage.src = imageSrc;
          mainImage.setAttribute('data-zoom', imageSrc);

          // Update active state
          thumbnails.forEach(item => item.classList.remove('active'));
          this.classList.add('active');

          // Reinitialize Drift for the new image
          initDriftZoom();
        });
      });
    }

    // Image navigation functionality (prev/next buttons)
    function initImageNavigation() {
      const prevButton = document.querySelector('.image-nav-btn.prev-image');
      const nextButton = document.querySelector('.image-nav-btn.next-image');

      if (!prevButton || !nextButton) return;

      const thumbnails = Array.from(document.querySelectorAll('.thumbnail-item'));
      if (!thumbnails.length) return;

      // Function to navigate to previous or next image
      function navigateImage(direction) {
        // Find the currently active thumbnail
        const activeIndex = thumbnails.findIndex(thumb => thumb.classList.contains('active'));
        if (activeIndex === -1) return;

        let newIndex;
        if (direction === 'prev') {
          // Go to previous image or loop to the last one
          newIndex = activeIndex === 0 ? thumbnails.length - 1 : activeIndex - 1;
        } else {
          // Go to next image or loop to the first one
          newIndex = activeIndex === thumbnails.length - 1 ? 0 : activeIndex + 1;
        }

        // Simulate click on the new thumbnail
        thumbnails[newIndex].click();
      }

      // Add event listeners to navigation buttons
      prevButton.addEventListener('click', () => navigateImage('prev'));
      nextButton.addEventListener('click', () => navigateImage('next'));
    }

    // Initialize all features
    initDriftZoom();
    initThumbnailClick();
    initImageNavigation();
  }

  productDetailFeatures();

  /**
   * Price range slider implementation for price filtering.
   */
  function priceRangeWidget() {
    // Get all price range widgets on the page
    const priceRangeWidgets = document.querySelectorAll('.price-range-container');

    priceRangeWidgets.forEach(widget => {
      const minRange = widget.querySelector('.min-range');
      const maxRange = widget.querySelector('.max-range');
      const sliderProgress = widget.querySelector('.slider-progress');
      const minPriceDisplay = widget.querySelector('.current-range .min-price');
      const maxPriceDisplay = widget.querySelector('.current-range .max-price');
      const minPriceInput = widget.querySelector('.min-price-input');
      const maxPriceInput = widget.querySelector('.max-price-input');
      const applyButton = widget.querySelector('.filter-actions .btn-primary');

      if (!minRange || !maxRange || !sliderProgress || !minPriceDisplay || !maxPriceDisplay || !minPriceInput || !maxPriceInput) return;

      // Slider configuration
      const sliderMin = parseInt(minRange.min);
      const sliderMax = parseInt(minRange.max);
      const step = parseInt(minRange.step) || 1;

      // Initialize with default values
      let minValue = parseInt(minRange.value);
      let maxValue = parseInt(maxRange.value);

      // Set initial values
      updateSliderProgress();
      updateDisplays();

      // Min range input event
      minRange.addEventListener('input', function() {
        minValue = parseInt(this.value);

        // Ensure min doesn't exceed max
        if (minValue > maxValue) {
          minValue = maxValue;
          this.value = minValue;
        }

        // Update min price input and display
        minPriceInput.value = minValue;
        updateDisplays();
        updateSliderProgress();
      });

      // Max range input event
      maxRange.addEventListener('input', function() {
        maxValue = parseInt(this.value);

        // Ensure max isn't less than min
        if (maxValue < minValue) {
          maxValue = minValue;
          this.value = maxValue;
        }

        // Update max price input and display
        maxPriceInput.value = maxValue;
        updateDisplays();
        updateSliderProgress();
      });

      // Min price input change
      minPriceInput.addEventListener('change', function() {
        let value = parseInt(this.value) || sliderMin;

        // Ensure value is within range
        value = Math.max(sliderMin, Math.min(sliderMax, value));

        // Ensure min doesn't exceed max
        if (value > maxValue) {
          value = maxValue;
        }

        // Update min value and range input
        minValue = value;
        this.value = value;
        minRange.value = value;
        updateDisplays();
        updateSliderProgress();
      });

      // Max price input change
      maxPriceInput.addEventListener('change', function() {
        let value = parseInt(this.value) || sliderMax;

        // Ensure value is within range
        value = Math.max(sliderMin, Math.min(sliderMax, value));

        // Ensure max isn't less than min
        if (value < minValue) {
          value = minValue;
        }

        // Update max value and range input
        maxValue = value;
        this.value = value;
        maxRange.value = value;
        updateDisplays();
        updateSliderProgress();
      });

      // Apply button click
      if (applyButton) {
        applyButton.addEventListener('click', function() {
          // This would typically trigger a form submission or AJAX request
          console.log(`Applying price filter: $${minValue} - $${maxValue}`);

          // Here you would typically add code to filter products or redirect to a filtered URL
        });
      }

      // Helper function to update the slider progress bar
      function updateSliderProgress() {
        const range = sliderMax - sliderMin;
        const minPercent = ((minValue - sliderMin) / range) * 100;
        const maxPercent = ((maxValue - sliderMin) / range) * 100;

        sliderProgress.style.left = `${minPercent}%`;
        sliderProgress.style.width = `${maxPercent - minPercent}%`;
      }

      // Helper function to update price displays
      function updateDisplays() {
        minPriceDisplay.textContent = `$${minValue}`;
        maxPriceDisplay.textContent = `$${maxValue}`;
      }
    });
  }
  priceRangeWidget();

 /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Initiate Pure Counter
   */
  new PureCounter();
  
  // Function to initialize Bootstrap tooltips
  function initTooltips() {
    // Check if Bootstrap's tooltip function exists
    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    } else {
      // Fallback for when Bootstrap JS is not loaded
      const cvvHint = document.querySelector('.cvv-hint');
      if (cvvHint) {
        cvvHint.addEventListener('mouseenter', function() {
          this.setAttribute('data-original-title', this.getAttribute('title'));
          this.setAttribute('title', '');
        });

        cvvHint.addEventListener('mouseleave', function() {
          this.setAttribute('title', this.getAttribute('data-original-title'));
        });
      }
    }
  }



  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  mobileNavToggleBtn.addEventListener('click', mobileNavToogle);

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });

  });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

})();