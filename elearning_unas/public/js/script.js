/**
 * Global JavaScript - Sistem E-Learning Akademik
 * Semua interaksi dan utility functions terpusat di satu file ini
 */

// ============================================
// Namespace untuk menghindari global pollution
// ============================================

const elearning = {
  // Configuration
  config: {
    apiTimeout: 5000,
    notificationDuration: 3000,
  },

  // ============================================
  // Notification System
  // ============================================

  showNotification(message, type = 'info') {
    const notificationId = 'notification-' + Date.now();
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <span>${this.escapeHtml(message)}</span>
        <button class="notification-close" onclick="elearning.closeNotification('${notificationId}')">&times;</button>
      </div>
    `;

    // Add to page
    if (!document.querySelector('.notification-container')) {
      const container = document.createElement('div');
      container.className = 'notification-container';
      document.body.appendChild(container);
    }

    document.querySelector('.notification-container').appendChild(notification);

    // Auto close
    setTimeout(() => {
      this.closeNotification(notificationId);
    }, this.config.notificationDuration);
  },

  closeNotification(id) {
    const notification = document.getElementById(id);
    if (notification) {
      notification.classList.add('notification-exit');
      setTimeout(() => {
        notification.remove();
      }, 300);
    }
  },

  // ============================================
  // Form Utilities
  // ============================================

  /**
   * Validate email format
   */
  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  },

  /**
   * Validate username format (8 digits)
   */
  isValidUsername(username) {
    return /^\d{8}$/.test(username);
  },

  /**
   * Get form data as object
   */
  getFormData(formElement) {
    const formData = new FormData(formElement);
    const data = {};
    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }
    return data;
  },

  /**
   * Validate required fields
   */
  validateRequired(fields) {
    const errors = [];
    fields.forEach(field => {
      const element = document.querySelector(`[name="${field}"]`);
      if (!element || !element.value.trim()) {
        errors.push(`${field} harus diisi`);
      }
    });
    return errors;
  },

  /**
   * Show form errors
   */
  showFormErrors(errors) {
    errors.forEach(error => {
      this.showNotification(error, 'danger');
    });
  },

  // ============================================
  // Table Utilities
  // ============================================

  /**
   * Sort table by column
   */
  sortTable(tableId, columnIndex, ascending = true) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
      const aValue = a.cells[columnIndex].textContent.trim();
      const bValue = b.cells[columnIndex].textContent.trim();

      // Try to parse as number
      const aNum = parseFloat(aValue);
      const bNum = parseFloat(bValue);

      if (!isNaN(aNum) && !isNaN(bNum)) {
        return ascending ? aNum - bNum : bNum - aNum;
      }

      // String comparison
      return ascending
        ? aValue.localeCompare(bValue)
        : bValue.localeCompare(aValue);
    });

    rows.forEach(row => tbody.appendChild(row));
  },

  /**
   * Filter table by search term
   */
  filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(term) ? '' : 'none';
    });
  },

  /**
   * Export table to CSV
   */
  exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
      const cells = row.querySelectorAll('th, td');
      const rowData = Array.from(cells).map(cell => {
        let text = cell.textContent.trim();
        // Escape quotes and wrap in quotes if contains comma
        if (text.includes(',') || text.includes('"')) {
          text = `"${text.replace(/"/g, '""')}"`;
        }
        return text;
      });
      csv.push(rowData.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  },

  // ============================================
  // File Utilities
  // ============================================

  /**
   * Format file size in human readable format
   */
  getFileSizeReadable(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  },

  /**
   * Validate file size
   */
  isValidFileSize(bytes, maxMB = 10) {
    return bytes <= (maxMB * 1024 * 1024);
  },

  /**
   * Validate file type
   */
  isValidFileType(filename, allowedExtensions) {
    const ext = filename.split('.').pop().toLowerCase();
    return allowedExtensions.includes(ext);
  },

  // ============================================
  // Date & Time Utilities
  // ============================================

  /**
   * Format date to Indonesian format
   */
  formatDate(date, format = 'DD MMM YYYY') {
    if (typeof date === 'string') {
      date = new Date(date);
    }

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const d = date.getDate();
    const m = months[date.getMonth()];
    const y = date.getFullYear();

    return format
      .replace('DD', String(d).padStart(2, '0'))
      .replace('MMM', m)
      .replace('YYYY', y);
  },

  /**
   * Format time
   */
  formatTime(date, format = 'HH:mm') {
    if (typeof date === 'string') {
      date = new Date(date);
    }

    const h = String(date.getHours()).padStart(2, '0');
    const m = String(date.getMinutes()).padStart(2, '0');
    const s = String(date.getSeconds()).padStart(2, '0');

    return format
      .replace('HH', h)
      .replace('mm', m)
      .replace('ss', s);
  },

  /**
   * Get day name
   */
  getDayName(date) {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    if (typeof date === 'string') {
      date = new Date(date);
    }
    return days[date.getDay()];
  },

  // ============================================
  // Modal & Dialog Utilities
  // ============================================

  /**
   * Show confirmation dialog
   */
  confirm(message, onConfirm, onCancel) {
    if (window.confirm(message)) {
      if (onConfirm) onConfirm();
    } else {
      if (onCancel) onCancel();
    }
  },

  /**
   * Show modal
   */
  showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('modal-open');
      modal.classList.add('show');
    }
  },

  /**
   * Hide modal
   */
  hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('modal-open');
      modal.classList.remove('show');
    }
  },

  // ============================================
  // API & AJAX Utilities
  // ============================================

  /**
   * Make API request
   */
  async apiRequest(url, options = {}) {
    const defaultOptions = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      timeout: this.config.apiTimeout,
    };

    const finalOptions = { ...defaultOptions, ...options };

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), finalOptions.timeout);

      const response = await fetch(url, {
        ...finalOptions,
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  },

  /**
   * POST request
   */
  async post(url, data) {
    return this.apiRequest(url, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  /**
   * GET request
   */
  async get(url) {
    return this.apiRequest(url, {
      method: 'GET',
    });
  },

  // ============================================
  // DOM Utilities
  // ============================================

  /**
   * Escape HTML to prevent XSS
   */
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  },

  /**
   * Add class to element
   */
  addClass(element, className) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.classList.add(className);
    }
  },

  /**
   * Remove class from element
   */
  removeClass(element, className) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.classList.remove(className);
    }
  },

  /**
   * Toggle class
   */
  toggleClass(element, className) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.classList.toggle(className);
    }
  },

  /**
   * Show element
   */
  show(element) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.style.display = '';
    }
  },

  /**
   * Hide element
   */
  hide(element) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.style.display = 'none';
    }
  },

  /**
   * Set element content
   */
  setContent(element, content) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.textContent = content;
    }
  },

  /**
   * Set element HTML
   */
  setHtml(element, html) {
    if (typeof element === 'string') {
      element = document.querySelector(element);
    }
    if (element) {
      element.innerHTML = html;
    }
  },

  // ============================================
  // Sidebar Toggle
  // ============================================

  /**
   * Toggle sidebar on mobile
   */
  toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
      this.toggleClass(sidebar, 'sidebar-open');
      document.body.classList.toggle('sidebar-is-open', sidebar.classList.contains('sidebar-open'));
      document.querySelectorAll('[data-toggle="sidebar"]').forEach(toggle => {
        toggle.setAttribute('aria-expanded', sidebar.classList.contains('sidebar-open') ? 'true' : 'false');
      });
    }
  },

  // ============================================
  // Initialize
  // ============================================

  /**
   * Initialize all event listeners
   */
  init() {
    // Sidebar toggle on mobile
    const sidebarToggles = document.querySelectorAll('[data-toggle="sidebar"]');
    sidebarToggles.forEach(toggle => {
      toggle.setAttribute('aria-expanded', 'false');
      toggle.addEventListener('click', () => this.toggleSidebar());
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', (e) => {
      const sidebar = document.querySelector('.sidebar');
      const isToggle = e.target.closest('[data-toggle="sidebar"]');
      if (sidebar && !sidebar.contains(e.target) && !isToggle) {
        this.removeClass(sidebar, 'sidebar-open');
        document.body.classList.remove('sidebar-is-open');
        sidebarToggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
      }
    });

    // Active menu item based on current page
    this.setActiveMenuItem();
  },

  /**
   * Set active menu item based on current page
   */
  setActiveMenuItem() {
    const currentPage = window.location.pathname;
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    const sidebarItems = document.querySelectorAll('.sidebar-nav a');

    [...menuItems, ...sidebarItems].forEach(item => {
      const href = item.getAttribute('href');
      if (href && (currentPage === href || currentPage.endsWith(href) || href.endsWith(currentPage.split('/').pop()))) {
        this.addClass(item, 'active');
      } else {
        this.removeClass(item, 'active');
      }
    });
  },
};

// ============================================
// Initialize on DOM Ready
// ============================================

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    elearning.init();
  });
} else {
  elearning.init();
}

// ============================================
// Export for use in other scripts
// ============================================

if (typeof module !== 'undefined' && module.exports) {
  module.exports = elearning;
}
