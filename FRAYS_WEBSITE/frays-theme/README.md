# Frays Cottage Theme Template

Copy this folder to any new project for consistent branding across all Frays Cottage websites and applications.

## Quick Start

### 1. Copy Theme Files

Copy `frays-theme/` to your new project:
```
/your-new-project/
├── frays-theme/
│   ├── css/
│   │   └── theme.css
│   └── js/
│       └── theme.js
└── index.html
```

### 2. Add to HTML

```html
<head>
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;500;600;700&display.css" rel="stylesheet">
  
  <!-- Frays Theme -->
  <link href="frays-theme/css/theme.css" rel="stylesheet">
</head>

<body>
  <!-- Your content -->
  
  <!-- Theme JS -->
  <script src="frays-theme/js/theme.js"></script>
</body>
```

## Colors

| Variable | Value | Usage |
|----------|-------|-------|
| `--frays-red` | #990000 | Primary accent, buttons, headers |
| `--frays-yellow` | #CCCC66 | Secondary accent, highlights |
| `--frays-parchment` | #F1F1D4 | Background sections |

## CSS Classes

### Buttons
```html
<button class="btn-primary">Primary</button>
<button class="btn-secondary">Secondary</button>
```

### Cards
```html
<div class="card">Content</div>
<div class="card card-hover">Hover Effect</div>
```

### Sections
```html
<section class="section-white">White background</section>
<section class="section-parchment">Parchment background</section>
<section class="section-red">Red background</section>
```

## JavaScript Utilities

```javascript
// Toast notifications
FraysTheme.showToast('Message', 'success')

// Format currency
FraysTheme.formatCurrency(1500) // "P1,500.00"

// Format date
FraysTheme.formatDate('2026-02-12') // "12 Feb 2026"

// API calls
const data = await FraysTheme.api('/api/endpoint')

// Copy to clipboard
await FraysTheme.copyToClipboard('text')

// Loading spinner
const spinner = FraysTheme.showLoading(container)
FraysTheme.hideLoading(spinner)
```

## File Structure

```
frays-theme/
├── css/
│   └── theme.css     # All styles (4000+ lines of production CSS)
└── js/
    └── theme.js      # Utilities & helpers
```

## Development Workflow

### 1. Develop locally
```bash
cd FRAYS_WEBSITE
# Make changes
```

### 2. Commit & Push
```bash
git add .
git commit -m "feat: description"
git push origin main
```

### 3. Pull on server
```bash
cd /path/to/live/site
git pull origin main
```

## Available Classes

| Class | Description |
|-------|-------------|
| `.text-frays-red` | Red text color |
| `.bg-frays-yellow` | Yellow background |
| `.badge-red` | Red pill badge |
| `.badge-yellow` | Yellow pill badge |
| `.form-input` | Styled form input |
| `.contact-bar` | Contact info bar |

---

**Maintained by:** Frays Cottage Bookkeeping Services  
**Repository:** https://github.com/junyso/Frays-Cottage-Bookkeeping-Services-Website
