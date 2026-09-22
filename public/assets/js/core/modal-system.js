/**
 * HIMS Enterprise Financial Management System
 * Universal Pop-up Modal UX, Accessibility & Loading State Engine
 */

(function () {
  'use strict';

  // 1. Auto-Focus First Interactive Element on Modal Open
  document.addEventListener('shown.bs.modal', function (event) {
    const modal = event.target;
    if (!modal) return;

    // Find first interactive, non-hidden element
    const focusableSelector = 'input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled]), button[type="submit"]:not([disabled])';
    const firstFocusable = modal.querySelector(focusableSelector);

    if (firstFocusable) {
      firstFocusable.focus();
    }
  });

  // 2. Keyboard Focus Trapping for Screen Reader & Accessibility Compliance (WCAG 2.1 AA)
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Tab') return;

    const openModal = document.querySelector('.modal.show');
    if (!openModal) return;

    const focusableElements = openModal.querySelectorAll(
      'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    if (focusableElements.length === 0) return;

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey) {
      // Shift + Tab: if focused on first element, cycle to last
      if (document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
      }
    } else {
      // Tab: if focused on last element, cycle to first
      if (document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
      }
    }
  });

  // 3. Automated Form Submission Spinner & Anti-Double-Click Guard
  document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!form || !form.closest('.modal')) return;

    // Check HTML5 validity first so validation bubbles can trigger normally
    if (form.checkValidity && !form.checkValidity()) {
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn || submitBtn.classList.contains('is-submitting')) return;

    // Store original markup for safe restoration
    submitBtn.dataset.originalHtml = submitBtn.innerHTML;

    // Append standard spinner
    const spinner = document.createElement('span');
    spinner.className = 'spinner-border spinner-border-sm me-1';
    spinner.setAttribute('role', 'status');
    spinner.setAttribute('aria-hidden', 'true');

    // Text transition
    const labelSpan = submitBtn.querySelector('span');
    if (labelSpan) {
      const currentText = labelSpan.textContent.trim();
      if (!currentText.toLowerCase().includes('ing')) {
        labelSpan.textContent = 'Processing...';
      }
    }

    submitBtn.prepend(spinner);
    submitBtn.classList.add('is-submitting');

    // Prevent secondary clicks on the form
    setTimeout(function () {
      submitBtn.disabled = true;
    }, 20);
  }, true);

  // 4. Modal Cleanup on Close: Reset Submit Buttons & Form Warnings
  document.addEventListener('hidden.bs.modal', function (event) {
    const modal = event.target;
    if (!modal) return;

    const submitButtons = modal.querySelectorAll('button[type="submit"].is-submitting');
    submitButtons.forEach(function (btn) {
      if (btn.dataset.originalHtml) {
        btn.innerHTML = btn.dataset.originalHtml;
        delete btn.dataset.originalHtml;
      }
      btn.classList.remove('is-submitting');
      btn.disabled = false;
    });
  });

})();
