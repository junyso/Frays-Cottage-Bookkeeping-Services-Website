/**
 * Frays Cottage Theme JavaScript
 * 
 * Shared utilities for all Frays Cottage projects
 */

const FraysTheme = {
  // Colors (match CSS variables)
  colors: {
    red: '#990000',
    yellow: '#CCCC66',
    parchment: '#F1F1D4'
  },
  
  /**
   * Initialize contact bar with current year
   */
  initContactBar() {
    const yearEl = document.getElementById('copyright-year');
    if (yearEl) {
      yearEl.textContent = new Date().getFullYear();
    }
  },
  
  /**
   * Show toast notification
   */
  showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style
    Object.assign(toast.style, {
      position: 'fixed',
      bottom: '2rem',
      right: '2rem',
      padding: '1rem 1.5rem',
      borderRadius: '0.5rem',
      color: '#fff',
      fontWeight: '500',
      zIndex: '9999',
      transform: 'translateY(100px)',
      opacity: '0',
      transition: 'all 0.3s ease'
    });
    
    const colors = {
      success: '#22c55e',
      error: '#ef4444',
      warning: '#f59e0b',
      info: this.colors.red
    };
    toast.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(toast);
    
    // Animate in
    requestAnimationFrame(() => {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
    });
    
    // Remove after 3 seconds
    setTimeout(() => {
      toast.style.transform = 'translateY(100px)';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  },
  
  /**
   * Format currency (BWP)
   */
  formatCurrency(amount) {
    return new Intl.NumberFormat('en-BW', {
      style: 'currency',
      currency: 'BWP'
    }).format(amount);
  },
  
  /**
   * Format date
   */
  formatDate(date, format = 'short') {
    const options = {
      short: { day: 'numeric', month: 'short', year: 'numeric' },
      long: { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' },
      time: { hour: '2-digit', minute: '2-digit' }
    };
    
    return new Date(date).toLocaleDateString('en-BW', options[format] || options.short);
  },
  
  /**
   * Debounce function
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },
  
  /**
   * Get URL parameter
   */
  getParam(name) {
    const url = new URL(window.location);
    return url.searchParams.get(name);
  },
  
  /**
   * Copy to clipboard
   */
  async copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      this.showToast('Copied to clipboard!', 'success');
      return true;
    } catch (err) {
      this.showToast('Failed to copy', 'error');
      return false;
    }
  },
  
  /**
   * Loading spinner
   */
  showLoading(container) {
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    spinner.innerHTML = `
      <div style="
        border: 3px solid var(--frays-parchment);
        border-top: 3px solid var(--frays-red);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
      "></div>
    `;
    
    Object.assign(spinner.style, {
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
      padding: '2rem'
    });
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
    document.head.appendChild(style);
    
    container.appendChild(spinner);
    return spinner;
  },
  
  hideLoading(spinner) {
    if (spinner && spinner.parentNode) {
      spinner.parentNode.removeChild(spinner);
    }
  },
  
  /**
   * API helper
   */
  async api(endpoint, options = {}) {
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json'
      }
    };
    
    const response = await fetch(endpoint, { ...defaultOptions, ...options });
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.error || 'API request failed');
    }
    
    return data;
  },
  
  /**
   * Initialize all components
   */
  init() {
    this.initContactBar();
    console.log('✅ Frays Cottage Theme initialized');
  }
};

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => FraysTheme.init());
} else {
  FraysTheme.init();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FraysTheme;
}
